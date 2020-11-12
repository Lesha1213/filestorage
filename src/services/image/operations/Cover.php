<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations;

use Intervention\Image\Image;
use reactivestudio\filestorage\services\image\operations\base\AbstractOperation;

class Cover extends AbstractOperation
{
    public function apply(Image $image): void
    {
        if ($this->settings->getOrientate()) {
            $image->orientate();
        }

        $image->fit(
            $this->settings->getResolution()->getWidth(),
            $this->settings->getResolution()->getHeight(),
            $this->getConstraintCallback(),
            $this->settings->getPosition()->toString()
        );

        parent::apply($image);
    }

    protected function arguments(): array
    {
        return [
            'width' => $this->settings->getResolution()->getWidth(),
            'height' => $this->settings->getResolution()->getHeight(),
            'upSize' => $this->settings->isUpSize(),
            'position' => $this->settings->getPosition()->toString(),
            'orientate' => $this->settings->getOrientate(),
        ];
    }
}
