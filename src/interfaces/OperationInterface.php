<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use Intervention\Image\Image;

interface OperationInterface
{
    public function build(): OperationInterface;
    public function apply(Image $image): void;
    public function getConfig(): string;
}
