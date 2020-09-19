<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\type;

use reactivestudio\filestorage\interfaces\FileTypeInterface;

class GeneralType implements FileTypeInterface
{
    /**
     * @return string[]
     */
    public static function getMimeSearchPatterns(): array
    {
        return [];
    }

    public static function getName(): string
    {
        return 'general';
    }

    public function getAllowedExtensions(): ?array
    {
        return null;
    }
}
