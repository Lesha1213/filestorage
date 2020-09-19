<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\base;

use Exception;
use reactivestudio\filestorage\interfaces\PreviewInterface;
use reactivestudio\filestorage\models\base\preview\AbstractImagePreview;
use reactivestudio\filestorage\models\type\ImageType;
use reactivestudio\filestorage\services\FileTypeService;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\di\NotInstantiableException;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * @property-read AbstractImagePreview[] $previews
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
        /** @var ImageType $type */
        $type = Yii::createObject(ImageType::class);
        return $type->getAllowedExtensions();
    }

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function rules(): array
    {
        /** @var FileTypeService $service */
        $service = Yii::$container->get(FileTypeService::class);

        $rules = parent::rules();
        $rules[] = [
            ['original_extension'],
            function () use ($service) {
                return $service->getType($this->mime)::getName() === ImageType::getName();
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
