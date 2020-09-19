<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\helpers;

use yii\helpers\StringHelper;

class HashHelper
{
    public static function encode(string $storageRelativePath, string $fileName): string
    {
        return StringHelper::base64UrlEncode($storageRelativePath . DIRECTORY_SEPARATOR . $fileName);
    }

    public static function decode(string $hash): string
    {
        return StringHelper::base64UrlDecode($hash);
    }
}
