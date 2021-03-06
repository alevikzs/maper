<?php

namespace Mapper\Tests\Dummy\Tree;

use \JsonSerializable;

/**
 * Class Trunk
 * @package Mapper\Tests\Dummy\Tree
 */
class Trunk implements JsonSerializable {

    /**
     * @var float
     */
    private $height;

    /**
     * @var float
     */
    private $width;

    /**
     * @return float
     */
    public function getHeight(): float {
        return $this->height;
    }

    /**
     * @param float $height
     * @return Trunk
     */
    public function setHeight(float $height): Trunk {
        $this->height = $height;
        return $this;
    }

    /**
     * @return float
     */
    public function getWidth(): float {
        return $this->width;
    }

    /**
     * @param float $width
     * @return Trunk
     */
    public function setWidth(float $width): Trunk {
        $this->width = $width;
        return $this;
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array {
        return get_object_vars($this);
    }

}