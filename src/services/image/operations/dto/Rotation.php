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

    private function __construct(int $degree, bool $toPortrait = false)
    {

    }

    public static function create(int $degree, bool $toPortrait = false): self
    {
        return new static($degree, $toPortrait);
    }

    /**
     * @return int
     */
    public function getDegree(): int
    {
        return $this->degree;
    }

    /**
     * @return bool
     */
    public function isToPortrait(): bool
    {
        return $this->toPortrait;
    }
}
