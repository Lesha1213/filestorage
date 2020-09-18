<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components\image\operations;

use Intervention\Image\Image;
use reactivestudio\filestorage\components\image\operations\base\AbstractOperation;
use reactivestudio\filestorage\interfaces\OperationInterface;

class Cover extends AbstractOperation
{
    public function build(): OperationInterface
    {
        $rotate = (new Rotate())->setRotation($this->rotation);
        $this->stack->push($rotate);

        return parent::build();
    }

    public function apply(Image $image): void
    {
        $image->fit(
            $this->resolution->getWidth(),
            $this->resolution->getHeight(),
            $this->getUpSizeCallback(),
            $this->position->toString()
        );

        parent::apply($image);
    }

    protected function arguments(): array
    {
        return [
            $this->resolution->getWidth(),
            $this->resolution->getHeight(),
            $this->isUpSize,
            $this->position->toString(),
        ];
    }
}
