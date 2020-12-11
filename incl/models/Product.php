<?php

const PACK = "pack";
const PIECE = "piece";

class Product implements  JsonSerializable {
    private $name = null;
    private $description = null;
    private $parentBarcode = null;
    private $barcodes = array();
    private $imageUrls = array();
    private $tags = array();
    private $stockUnit = PIECE;
    private $unitQuantity = 1;
    private $weighed = false;
    private $nutritionInformation = array();

    /**
     * Product constructor.
     * @param null|string $name Product name
     * @param string|array $barcodes Product UPCs
     * @throws Exception
     */
    function __construct(?string $name, $barcodes) {
        $this->name = $name;
        $this->addBarcodes($barcodes);
    }

    public function addBarcodes($barcodes): Product {
        if (is_string($barcodes)) {
            array_push($this->barcodes, $barcodes);
        } else if (is_array($barcodes)) {
            array_push($this->barcodes, ...$barcodes);
        } else {
            throw new Exception("$barcodes must be a string or an array of strings.");
        }

        return $this;
    }

    /**
     * Description of the product
     * @param string|null $description
     * @return Product
     */
    public function setDescription(?string $description): Product {
        $this->description = $description;
        return $this;
    }

    /**
     * Parent product's barcode
     * @param string|null $parentBarcode
     * @return Product
     */
    public function setParentBarcode(?string $parentBarcode): Product {
        $this->parentBarcode = $parentBarcode;
        return $this;
    }

    /**
     * Add an image url
     * @param string|null $imageUrl
     * @return $this
     */
    public function addImageUrl(?string $imageUrl): Product {
        if (empty($imageUrl))
            return $this;
        array_push($this->imageUrls, $imageUrl);
        return $this;
    }

    /**
     * Add a list of image urls
     * @param array|null $imageUrls
     * @return $this
     */
    public function addImageUrls(?array $imageUrls): Product {
        if (empty($imageUrls))
            return $this;
        array_push($this->imageUrls, ...$imageUrls);
        return $this;
    }

    /**
     * Add a nutrient
     * @param NutrientInformation|null $nutrient
     * @return $this
     */
    public function addNutrient(?NutrientInformation $nutrient): Product {
        if (empty($nutrient))
            return $this;
        array_push($this->nutritionInformation, $nutrient);
        return $this;
    }

    /**
     * Add a list of nutrients
     * @param array|null $nutrients
     * @return $this
     */
    public function addNutrients(?array $nutrients): Product {
        if (empty($nutrients))
            return $this;
        array_push($this->nutritionInformation, ...$nutrients);
        return $this;
    }

    /**
     * Add a tag
     * @param string|null $tag
     * @return $this
     */
    public function addTag(?string $tag): Product {
        if (empty($tag))
            return $this;
        array_push($this->tags, $tag);
        return $this;
    }

    /**
     * Add a list of tags
     * @param array|null $tags
     * @return $this
     */
    public function addTags(?array $tags): Product {
        if (empty($tags))
            return $this;
        if (is_string($tags))
            return $this->addTag($tags);
        array_push($this->tags, ...$tags);
        return $this;
    }

    /**
     * Sets the packaging type and count
     * @param int $packageCount
     * @return $this
     * @throws Exception
     */
    public function setPackageCount(int $packageCount = 1): Product {
        if ($packageCount < 1) {
            throw new Exception("$packageCount cannot be less than 1");
        }
        $this->unitQuantity = $packageCount;
        $this->stockUnit = ($packageCount > 1) ? PACK : PIECE;
        return $this;
    }

    /**
     * Set whether this product is weighed.
     * @param bool $weighed
     * @return $this
     */
    public function isWeighed(bool $weighed): Product {
        $this->weighed = $weighed;
        return $this;
    }

    /**
     * @return null|string Product name
     */
    public function getName(): ?string {
        return $this->name;
    }

    /**
     * @return array
     */
    public function jsonSerialize() {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'parentBarcode' => $this->parentBarcode,
            'barcodes' => $this->barcodes,
            'imageUrls' => $this->imageUrls,
            'unit' => $this->stockUnit,
            'quantity' => $this->unitQuantity,
            'weighed' => $this->weighed,
            'tags' => $this->tags,
            'nutritionInformation' => $this->nutritionInformation
        ];
    }

    public function debug() {
        return $this->name . ' ' . $this->barcodes[0];
    }
}