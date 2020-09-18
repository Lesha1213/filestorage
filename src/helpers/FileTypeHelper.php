<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\helpers;

use Exception;
use reactivestudio\filestorage\models\base\AbstractFile;
use yii\helpers\ArrayHelper;

class FileTypeHelper
{
    public const TYPE_UNKNOWN = 'unknown';
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    private const EXTENSIONS_BY_TYPE = [
        self::TYPE_IMAGE => ['jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'ico', 'webp'],
        self::TYPE_VIDEO => ['mov', 'mpeg4', 'mp4', 'avi', 'wmv', 'mpeg', 'flv', '3gp', 'webm', 'hevc'],
    ];

    /**
     * @param string $type
     * @return array
     * @throws Exception
     */
    public static function getAllowedExtensionsForType(string $type): array
    {
        return ArrayHelper::getValue(self::EXTENSIONS_BY_TYPE, $type, []);
    }

    /**
     * @param AbstractFile $file
     * @return bool
     *
     * @throws Exception
     */
    public static function isImage(AbstractFile $file): bool
    {
        return static::isTypeOf($file->mime, static::TYPE_IMAGE);
    }

    /**
     * @param AbstractFile $file
     * @return bool
     *
     * @throws Exception
     */
    public static function isVideo(AbstractFile $file): bool
    {
        return static::isTypeOf($file->mime, static::TYPE_VIDEO);
    }

    protected static function getMimeSearchPatterns(): array
    {
        return [
            static::TYPE_IMAGE => [
                'image',
            ],
            static::TYPE_VIDEO => [
                'video',
            ],
        ];
    }

    /**
     * @param string $mime
     * @param string $type
     * @return bool
     *
     * @throws Exception
     */
    protected static function isTypeOf(string $mime, string $type): bool
    {
        $patterns = ArrayHelper::getValue(static::getMimeSearchPatterns(), $type, []);
        if (empty($mime) || 0 === count($patterns)) {
            return false;
        }

        foreach ($patterns as $pattern) {
            if (false !== strpos($mime, $pattern)) {
                return true;
            }
        }

        return false;
    }
}
