<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components\image\operations\dto;

class Quality
{
    public const BEST = 100;
    public const GOOD = 70;
    public const LOW = 30;

    /**
     * @var int
     */
    private $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    /**
     * @return Quality
     */
    public static function best(): self
    {
        return new static(static::BEST);
    }

    /**
     * @return Quality
     */
    public static function good(): self
    {
        return new static(static::GOOD);
    }

    /**
     * @return Quality
     */
    public static function low(): self
    {
        return new static(static::LOW);
    }

    /**
     * @return int
     */
    public function getValue(): int
    {
        return $this->value;
    }
}
