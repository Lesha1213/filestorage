<?php

namespace reactivestudio\filestorage\services\image\operations\dto;

class Quality
{
    private const MIME_ALL = '*';
    private const DEFAULT_VALUE = self::BEST;

    public const T_JPEG = 'jpeg';
    public const T_PNG = 'png';

    public const BEST = 100;
    public const GOOD = 70;
    public const LOW = 30;

    /**
     * @var array
     */
    private $value;

    /**
     * @param int|array|int[] $value
     */
    private function __construct($value)
    {
        if (!is_array($value)) {
            $this->value[self::MIME_ALL] = $value;
        } else {
            $this->value = $value;
        }
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
    public static function defaults(): self
    {
        return new static(static::DEFAULT_VALUE);
    }

    /**
     * @param array $map
     * @return static
     */
    public static function define(array $map): self
    {
        return new static($map);
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
     * @param null|string $mime
     * @return int
     */
    public function getValue(?string $mime = null): int
    {
        if (null === $mime) {
            $mime = static::MIME_ALL;
        }
        return $this->value[$mime] ?? static::DEFAULT_VALUE;
    }
}
