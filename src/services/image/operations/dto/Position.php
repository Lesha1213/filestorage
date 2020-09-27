<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations\dto;

final class Position
{
    public const CENTER = 'center';

    public const TOP = 'top';
    public const BOTTOM = 'bottom';

    public const LEFT = 'left';
    public const RIGHT = 'right';

    /**
     * @var string|null
     */
    private $x;

    /**
     * @var string|null
     */
    private $y;

    private function __construct(?string $x = null, ?string $y = null)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public static function create(?string $x = null, ?string $y = null): self
    {
        return new static($x, $y);
    }

    public function toString(): string
    {
        if (null === $this->x && null === $this->y) {
            return self::CENTER;
        }

        if (null !== $this->x && null !== $this->y) {
            if (self::CENTER === $this->x) {
                return $this->y;
            }

            if (self::CENTER === $this->y) {
                return $this->x;
            }

            return "{$this->y}-{$this->x}";
        }

        return $this->x ?? $this->y;
    }

    public function getX(): ?string
    {
        return $this->x;
    }

    public function getY(): ?string
    {
        return $this->y;
    }
}
