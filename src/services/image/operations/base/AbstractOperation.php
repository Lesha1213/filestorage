<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations\base;

use Intervention\Image\Constraint;
use Intervention\Image\Image;
use reactivestudio\filestorage\services\image\operations\dto\Settings;
use reactivestudio\filestorage\services\image\operations\structures\OperationStack;
use reactivestudio\filestorage\interfaces\OperationInterface;

abstract class AbstractOperation implements OperationInterface
{
    /**
     * @var OperationStack
     */
    protected $stack;

    /**
     * @var Settings
     */
    protected $settings;

    private function __construct(Settings $settings)
    {
        $this->stack = new OperationStack();
        $this->settings = $settings;
    }

    /**
     * @param Settings $settings
     * @return OperationInterface
     */
    public static function create(Settings $settings): OperationInterface
    {
        return (new static($settings))->build();
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

    public function getConfig(): string
    {
        $values = [];
        foreach ($this->arguments() as $name => $value) {
            $values[] = "{$name}:{$this->argumentToString($value)}";
        }

        return implode(';', $values);
    }

    /**
     * @return OperationInterface
     */
    protected function build(): OperationInterface
    {
        return $this;
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
            if ($this->settings->isUpSize()) {
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
