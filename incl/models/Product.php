<?php

const PACK = "pack";
const PIECE = "piece";

class Product implements  JsonSerializable {
    private $name;
    private $description = null;
    private $parentBarcode = null;
    private $barcodes = array();
    private $imageUrls = array();
    private $stockUnit = PIECE;
    private $unitQuantity = 1;
    private $weighed = false;

    /**
     * Product constructor.
     * @param string $name Product name
     * @param string|array $barcodes Product UPCs
     * @throws Exception
     */
    function __construct(string $name, $barcodes) {
        $this->name = $name;
        if (is_string($barcodes)) {
            array_push($this->barcodes, $barcodes);
        } else if (is_array($barcodes)) {
            $this->barcodes = $barcodes;
        } else {
            throw new Exception("$barcodes must be a string or an array of strings.");
        }
    }

    /**
     * Description of the product
     * @param string $description
     * @return Product
     */
    public function setDescription(string $description): Product {
        $this->description = $description;
        return $this;
    }

    /**
     * Parent product's barcode
     * @param string $parentBarcode
     * @return Product
     */
    public function setParentBarcode(string $parentBarcode): Product {
        $this->parentBarcode = $parentBarcode;
        return $this;
    }

    /**
     * Add an image url
     * @param string $imageUrl
     * @return $this
     */
    public function addImageUrl(string $imageUrl): Product {
        array_push($this->imageUrls, $imageUrl);
        return $this;
    }

    /**
     * Add a list of image urls
     * @param array $imageUrls
     * @return $this
     */
    public function addImageUrls(array $imageUrls): Product {
        $this->imageUrls = array_merge($this->$imageUrls, $imageUrls);
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
            'weighed' => $this->weighed
        ];
    }
}