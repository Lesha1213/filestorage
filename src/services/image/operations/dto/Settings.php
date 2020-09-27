<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations\dto;


class Settings
{
    /**
     * @var Resolution
     */
    protected $resolution;

    /**
     * @var Position
     */
    protected $position;

    /**
     * @var Rotation|null
     */
    protected $rotation;

    /**
     * @var Quality|null
     */
    protected $quality;

    /**
     * @var bool
     */
    protected $isUpSize = false;

    /**
     * @return Resolution
     */
    public function getResolution(): Resolution
    {
        return $this->resolution;
    }

    /**
     * @param Resolution $resolution
     * @return Settings
     */
    public function setResolution(Resolution $resolution): self
    {
        $this->resolution = $resolution;
        return $this;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position
    {
        return $this->position;
    }

    /**
     * @param Position $position
     * @return Settings
     */
    public function setPosition(Position $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return Rotation|null
     */
    public function getRotation(): ?Rotation
    {
        return $this->rotation;
    }

    /**
     * @param Rotation $rotation
     * @return Settings
     */
    public function setRotation(Rotation $rotation): self
    {
        $this->rotation = $rotation;
        return $this;
    }

    /**
     * @return Quality
     */
    public function getQuality(): Quality
    {
        return $this->quality ?? Quality::best();
    }

    /**
     * @param Quality $quality
     * @return Settings
     */
    public function setQuality(Quality $quality): self
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUpSize(): bool
    {
        return $this->isUpSize;
    }

    /**
     * @param bool $isUpSize
     * @return Settings
     */
    public function setUpSize(bool $isUpSize): self
    {
        $this->isUpSize = $isUpSize;
        return $this;
    }
}
