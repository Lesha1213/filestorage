<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations\dto;

final class Resolution
{
    /**
     * @var int|null
     */
    private $width;

    /**
     * @var int|null
     */
    private $height;

    private function __construct(?int $width, ?int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    public static function create(?int $width, ?int $height): self
    {
        return new static($width, $height);
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }
}
