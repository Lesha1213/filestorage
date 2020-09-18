<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components\image\operations\dto;

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

    public function __construct(?int $width, ?int $height)
    {
        $this->width = $width;
        $this->height = $height;
    }

    /**
     * @return int|null
     */
    public function getWidth(): ?int
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     * @return Resolution
     */
    public function setWidth(?int $width): self
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight(): ?int
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     * @return Resolution
     */
    public function setHeight(?int $height): self
    {
        $this->height = $height;
        return $this;
    }
}
