<?php


class NutrientInformation implements JsonSerializable {
    private $name = null;
    private $unit = null;
    private $value = null;
    private $perServing = null;

    function __construct(
        string $name,
        string $unit = null,
        float $value = 0.0,
        float $perServing = null
    ) {
        $this->name = $name;
        $this->unit = $unit;
        $this->value = $value;
        $this->perServing = $perServing;
    }

    public function jsonSerialize()
    {
        return [
            "name" => $this->name,
            "unit" => $this->unit,
            "value" => $this->value,
            "perServing" => $this->perServing
        ];
    }
}