<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\operations;

use Intervention\Image\Image;
use reactivestudio\filestorage\services\image\operations\base\AbstractOperation;

class Rotate extends AbstractOperation
{
    public function apply(Image $image): void
    {
        $rotation = $this->settings->getRotation();
        if (null !== $rotation) {
            $image->rotate($rotation->getDegree());
        }

        parent::apply($image);
    }
}
