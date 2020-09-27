<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations;

use Intervention\Image\Image;
use reactivestudio\filestorage\services\image\operations\base\AbstractOperation;
use reactivestudio\filestorage\interfaces\OperationInterface;

class Heighten extends AbstractOperation
{
    public function apply(Image $image): void
    {
        $image->heighten(
            $this->settings->getResolution()->getHeight(),
            $this->getUpSizeCallback()
        );

        parent::apply($image);
    }

    protected function build(): OperationInterface
    {
        if (null !== $this->settings->getRotation()) {
            $this->stack->push(Rotate::create($this->settings));
        }

        return parent::build();
    }

    protected function arguments(): array
    {
        return [
            'height' => $this->settings->getResolution()->getHeight(),
            'upSize' => $this->settings->isUpSize(),
        ];
    }
}
