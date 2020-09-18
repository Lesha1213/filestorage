<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\base;

use Exception;
use reactivestudio\filestorage\helpers\FileTypeHelper;
use reactivestudio\filestorage\interfaces\PreviewInterface;
use reactivestudio\filestorage\models\base\preview\AbstractImagePreview;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;

/**
 * Class AbstractImage
 *
 * @property-read AbstractImagePreview[] $previews
 *
 * @package reactivestudio\filestorage\models\base
 */
abstract class AbstractImage extends AbstractFile
{
    /**
     * @return PreviewInterface
     */
    abstract public static function getPreviewEntityClass(): PreviewInterface;

    /**
     * @return string[]
     * @throws Exception
     */
    public static function getAllowedExtensions(): array
    {
        return FileTypeHelper::getAllowedExtensionsForType(FileTypeHelper::TYPE_IMAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $rules[] = [
            ['original_extension'],
            function () {
                return FileTypeHelper::isImage($this);
            }
        ];

        return $rules;
    }

    public function extraFields(): array
    {
        $extra = [
            'previews',
        ];

        return ArrayHelper::merge(parent::extraFields(), $extra);
    }

    /**
     * @return ActiveQuery
     */
    public function getPreviews(): ActiveQuery
    {
        return $this->hasMany(static::getPreviewEntityClass(), ['original_file_id' => 'id'])
            ->orderBy(['name' => SORT_ASC]);
    }
}
