<?php
/**
 * Box packing (3D bin packing, knapsack problem)
 * @package BoxPacker
 * @author Doug Wright
 */
namespace DVDoug\BoxPacker;

class TestItem implements Item
{

    public function __construct($description, $width, $length, $depth, $weight, $keepFlat)
    {
        $this->description = $description;
        $this->width = $width;
        $this->length = $length;
        $this->depth = $depth;
        $this->weight = $weight;
        $this->keepFlat = $keepFlat;

        $this->volume = $this->width * $this->length * $this->depth;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getLength()
    {
        return $this->length;
    }

    public function getDepth()
    {
        return $this->depth;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function getVolume()
    {
        return $this->volume;
    }

    public function getKeepFlat()
    {
        return $this->keepFlat;
    }
}