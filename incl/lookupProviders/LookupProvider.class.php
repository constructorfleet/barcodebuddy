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


require_once __DIR__ . "/ProviderOpenFoodFacts.php";
require_once __DIR__ . "/ProviderUpcDb.php";
require_once __DIR__ . "/ProviderJumbo.php";
require_once __DIR__ . "/ProviderUpcDatabase.php";
require_once __DIR__ . "/../models/Product.php";

class LookupProvider {

    protected $useGenericName;
    protected $apiKey;
    protected $providerName;
    protected $ignoredResultCodes = null;
    protected $providerConfigKey = null;

    function __construct($apiKey = null) {
        $this->useGenericName = BBConfig::getInstance()["USE_GENERIC_NAME"];
        $this->apiKey         = $apiKey;
    }

    /**
     * @param string $barcode
     * @return Product|null
     * @throws Exception
     */
    function lookupBarcode(string $barcode): ?Product {
        if (!$this->isProviderEnabled())
            return null;

        try {
            $json = $this->lookup($barcode);
            if ($json == null)
                return null;
            return $this->extractProductModel($json);
        } catch (Exception $ex) {
            API::logError($ex->getMessage());
            return null;
        }
    }

    protected function isProviderEnabled(): bool {
        if ($this->providerConfigKey == null)
            throw new Exception('providerConfigKey needs to be overridden!');
        return BBConfig::getInstance()[$this->providerConfigKey];
    }

    /**
     * Looks up a barcode
     * @param string $barcode The barcode to lookup
     * @return null|array     The resulting json array
     * @throws Exception
     */
    protected function lookup(string $barcode): ?array {
        throw new Exception('lookupBarcode needs to be overridden!');
    }

    /**
     * Extracts a product model to facilitate integration with Grocy
     *
     * @param array $json The lookup request's json response
     * @return null|Product
     * @throws Exception
     */
    protected function extractProductModel(array $json) : ?Product {
        throw new Exception('extractProductModel needs to be overridden!');
    }

    protected function execute($url) {
        $curl = new CurlGenerator($url, METHOD_GET, null, null, true, $this->ignoredResultCodes);
        try {
            $result = $curl->execute(true);
        } catch (Exception $e) {
            $class = get_class($e);
            switch ($class) {
                case 'InvalidServerResponseException':
                    API::logError("Could not connect to " . $this->providerName . ".", false);
                    return null;
                case 'UnauthorizedException':
                    API::logError("Could not connect to " . $this->providerName . " - unauthorized");
                    return null;
                case 'InvalidJsonResponseException':
                    API::logError("Error parsing " . $this->providerName . " response: " . $e->getMessage(), false);
                    return null;
                case 'InvalidSSLException':
                    API::logError("Could not connect to " . $this->providerName . " - invalid SSL certificate");
                    return null;
                case 'InvalidParameterException':
                    API::logError("Internal error: Invalid parameter passed to " . $this->providerName . ".");
                    return null;
                case 'NotFoundException':
                    API::logError("Server " . $this->providerName . " reported path not found.");
                    return null;
                case 'LimitExceededException':
                    API::logError("Connection limits exceeded for " . $this->providerName . ".");
                    return null;
                case 'InternalServerErrorException':
                    API::logError($this->providerName . " reported internal error.");
                    return null;
                default:
                    API::logError("Unknown error with " . $this->providerName . ": " . $e->getMessage());
                    return null;
            }
        }
        return $result;
    }
}