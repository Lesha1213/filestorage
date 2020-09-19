<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations\structures;

use reactivestudio\filestorage\interfaces\OperationInterface;
use RuntimeException;

class OperationStack
{
    /**
     * @var OperationInterface[]
     */
    private $operations = [];

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return 0 === count($this->operations);
    }

    /**
     * @param OperationInterface $operation
     * @return OperationStack
     */
    public function push(OperationInterface $operation): self
    {
        $this->operations[] = $operation;
        return $this;
    }

    /**
     * @return OperationInterface
     */
    public function pop(): OperationInterface
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('Operation stack is empty');
        }

        return array_pop($this->operations);
    }
}
