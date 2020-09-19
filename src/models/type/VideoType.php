<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\type;

use reactivestudio\filestorage\interfaces\FileTypeInterface;

class VideoType implements FileTypeInterface
{
    /**
     * @return string[]
     */
    public static function getMimeSearchPatterns(): array
    {
        return ['video'];
    }

    /**
     * @return string
     */
    public static function getName(): string
    {
        return 'video';
    }

    /**
     * @return string[]|null
     */
    public function getAllowedExtensions(): ?array
    {
        return ['mov', 'mpeg4', 'mp4', 'avi', 'wmv', 'mpeg', 'flv', '3gp', 'webm', 'hevc'];
    }
}
