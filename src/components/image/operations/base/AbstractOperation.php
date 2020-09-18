<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components\image\operations\base;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use reactivestudio\filestorage\components\image\operations\dto\Position;
use reactivestudio\filestorage\components\image\operations\dto\Quality;
use reactivestudio\filestorage\components\image\operations\dto\Resolution;
use reactivestudio\filestorage\components\image\operations\dto\Rotation;
use reactivestudio\filestorage\components\image\operations\structures\OperationStack;
use reactivestudio\filestorage\interfaces\OperationInterface;

abstract class AbstractOperation implements OperationInterface
{
    /**
     * @var OperationStack
     */
    protected $stack;

    /**
     * @var Resolution|null
     */
    protected $resolution;

    /**
     * @var Position|null
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

    public function __construct()
    {
        $this->stack = new OperationStack();
    }

    /**
     * @return OperationInterface
     */
    public function build(): OperationInterface
    {
        return $this;
    }

    /**
     * @param Image $image
     */
    public function apply(Image $image): void
    {
        while (!$this->stack->isEmpty()) {
            $this->stack->pop()->apply($image);
        }
    }

    /**
     * @param Resolution $resolution
     * @return AbstractOperation
     */
    public function setResolution(Resolution $resolution): self
    {
        $this->resolution = $resolution;
        return $this;
    }

    /**
     * @param Position $position
     * @return AbstractOperation
     */
    public function setPosition(Position $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @param Rotation $rotation
     * @return AbstractOperation
     */
    public function setRotation(Rotation $rotation): self
    {
        $this->rotation = $rotation;
        return $this;
    }

    /**
     * @param Quality $quality
     * @return AbstractOperation
     */
    public function setQuality(Quality $quality): self
    {
        $this->quality = $quality;
        return $this;
    }

    /**
     * @return AbstractOperation
     */
    public function makeUpSize(): self
    {
        $this->isUpSize = true;
        return $this;
    }

    public function getSystemName(): string
    {
        $values = [];
        foreach ($this->arguments() as $argument) {
            $values[] = $this->argumentToString($argument);
        }

        return implode('_', $values);
    }

    /**
     * @return array
     */
    protected function arguments(): array
    {
        return [];
    }

    /**
     * @return callable
     */
    protected function getUpSizeCallback(): callable
    {
        return function (Constraint $constraint) {
            if ($this->isUpSize) {
                $constraint->upsize();
            }
        };
    }

    /**
     * @param mixed $value
     * @return string
     */
    protected function argumentToString($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_int($value)) {
            return (string)$value;
        }

        return (string)$value;
    }
}
