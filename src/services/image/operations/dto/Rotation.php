<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations\dto;

class Rotation
{
    /**
     * @var int
     */
    private $degree = 0;

    /**
     * @var bool
     */
    private $toPortrait = false;

    /**
     * @return int
     */
    public function getDegree(): int
    {
        return $this->degree;
    }

    /**
     * @param int $degree
     * @return Rotation
     */
    public function setDegree(int $degree): self
    {
        $this->degree = $degree;
        return $this;
    }

    /**
     * @return bool
     */
    public function isToPortrait(): bool
    {
        return $this->toPortrait;
    }

    /**
     * @return Rotation
     */
    public function setToPortrait(): self
    {
        $this->toPortrait = true;
        return $this;
    }
}
