<?php

namespace reactivestudio\filestorage\interfaces;

use yii\db\ActiveRecordInterface;

interface PreviewInterface extends ActiveRecordInterface
{
    public static function getImageEntityClass(): string;
    public static function operations(): array;

    /**
     * @return string[]
     */
    public static function getPossibleNames(): array;
}
