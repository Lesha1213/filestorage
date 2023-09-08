<?php

namespace reactivestudio\filestorage\interfaces;

use Intervention\Image\Image;
use reactivestudio\filestorage\services\image\operations\dto\Settings;

interface OperationInterface
{
    public static function create(Settings $settings): OperationInterface;
    public function apply(Image $image): void;
    public function getConfig(): string;
    public function getSettings(): Settings;
}
