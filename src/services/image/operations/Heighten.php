<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations;

use Intervention\Image\Image;
use reactivestudio\filestorage\services\image\operations\base\AbstractOperation;

class Heighten extends AbstractOperation
{
    public function apply(Image $image): void
    {
        if ($this->settings->getOrientate()) {
            $image->orientate();
        }

        $image->heighten(
            $this->settings->getResolution()->getHeight(),
            $this->getConstraintCallback()
        );

        parent::apply($image);
    }

    protected function arguments(): array
    {
        return [
            'height' => $this->settings->getResolution()->getHeight(),
            'upSize' => $this->settings->isUpSize(),
            'orientate' => $this->settings->getOrientate(),
        ];
    }
}
