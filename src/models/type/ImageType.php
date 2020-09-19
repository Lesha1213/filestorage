<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\type;

use reactivestudio\filestorage\interfaces\FileTypeInterface;

class ImageType implements FileTypeInterface
{
    /**
     * @return string[]
     */
    public static function getMimeSearchPatterns(): array
    {
        return ['image'];
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'image';
    }

    /**
     * @return string[]|null
     */
    public function getAllowedExtensions(): ?array
    {
        return ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'ico', 'webp'];
    }
}
