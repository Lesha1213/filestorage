<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components;

use yii\helpers\StringHelper;

class HashService
{
    public function encode(string $value): string
    {
        return StringHelper::base64UrlEncode($value);
    }

    public function decode(string $hash): string
    {
        return StringHelper::base64UrlDecode($hash);
    }
}
