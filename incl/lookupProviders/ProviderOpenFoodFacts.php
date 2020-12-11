<?php

/**
 * Barcode Buddy for Grocy
 *
 * PHP version 7
 *
 * LICENSE: This source file is subject to version 3.0 of the GNU General
 * Public License v3.0 that is attached to this project.
 *
 * @author     Marc Ole Bulling
 * @copyright  2019 Marc Ole Bulling
 * @license    https://www.gnu.org/licenses/gpl-3.0.en.html  GNU GPL v3.0
 * @since      File available since Release 1.5
 */


require_once __DIR__ . "/../api.inc.php";
require_once __DIR__ . "/../helpers.php";

class ProviderOpenFoodFacts extends LookupProvider {

    function __construct($apiKey = null) {
        parent::__construct($apiKey);
        $this->providerName      = "OpenFoodFacts";
        $this->providerConfigKey = "LOOKUP_USE_OFF";
    }

    /**
     * Lookup a product via barcode
     * @param string $barcode
     * @return array|null
     * @throws Exception
     */
    protected function lookup(string $barcode): ?array {
        if (!$this->isProviderEnabled())
            return null;

        $url    = "https://world.openfoodfacts.org/api/v0/product/" . $barcode . ".json";
        $result = $this->execute($url);
        if (!isset($result["status"]) || $result["status"] !== 1 || !isset($result["product"]))
            return null;

        return $result["product"];
    }

    protected function extractProductModel(array $productJson): ?Product {
        $parseTag = function(?string $tag): ?string {
            if ($tag == null)
                return null;
            $tokens = explode(":", $tag);
            return end($tokens);
        };

        $genericName = (
            isset($productJson["generic_name"])
            && get($productJson["generic_name"]) != null
        )
            ? sanitizeString($productJson["generic_name"])
            : null;
        $productName = (
            isset($productJson["product_name"])
            && get($productJson["product_name"]) != null
        )
            ? sanitizeString($productJson["product_name"])
            : null;

        return (new Product(
            $this->useGenericName
                ? ($genericName ?: $productName)
                : ($productName ?: $genericName),
            $productJson["code"])
        )
            ->setDescription($productJson["ingredients_text"])
            ->addTag($parseTag($productJson["compared_to_category"]))
            ->addTags(array_map($parseTag, get($productJson["brands_tags"], array())))
            ->addNutrients($this->processNutrients(get($productJson["nutrients"], array())))
            ->addTags(array_map($parseTag, $productJson["categories_tags"]));
    }

    private function processNutrients(?array $nutrientsJson): ?array {
        if ($nutrientsJson)
            return null;

        $tokenizeKey = function(string $key): array {
            $tokens = explode("_", $key);
            return [
                "name" => $tokens[0],
                "property" => $tokens[1]
            ];
        };
        $groupNutrients = function ($nutrients, $item) use ($tokenizeKey) {
            if (!(stringEndsWith($item, "_serving")
                || stringEndsWith($item, "_unit")
                || stringEndsWith($item, "_value")))
                return $nutrients;

            $itemKey = key($item);
            $nutrientProperty = $tokenizeKey($itemKey);
            $nutrientName = $nutrientProperty["name"];

            if (!key_exists($nutrientName, $nutrients))
                array_push($nutrients, [$nutrientName => array()]);
            array_push(
                $nutrients[$nutrientName],
                [
                    $nutrientProperty["property"] => $item[$itemKey]
                ]
            );
            return $nutrients;
        };
        $buildNutrientInformation = function(?array $nutrient) {
            if (empty($nutrient))
                return null;
            return new NutrientInformation(
                key($nutrient),
                get($nutrient["unit"]),
                get($nutrient["value"], 0.0),
                get($nutrient["serving"], 0.0)
            );
        };
        $nutrients = array();
        $nutrientMap = array_reduce($nutrients, $groupNutrients);
        return array_map($buildNutrientInformation, get($nutrientMap, array()));
    }
}