<?php

namespace reactivestudio\filestorage\models\base;

use Exception;
use reactivestudio\filestorage\exceptions\ImagePreviewServiceException;
use reactivestudio\filestorage\interfaces\PreviewInterface;
use reactivestudio\filestorage\models\base\preview\AbstractImagePreview;
use reactivestudio\filestorage\models\type\ImageType;
use reactivestudio\filestorage\services\FileTypeService;
use reactivestudio\filestorage\services\image\ImagePreviewService;
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
     * @var ImagePreviewService
     */
    protected $service;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function init(): void
    {
        parent::init();

        $this->service = Yii::$container->get(ImagePreviewService::class);
    }

    /**
     * @return string|PreviewInterface
     */
    abstract public static function getPreviewEntityClass(): string;

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
        return $this->service->getRelationQuery($this);
    }

    /**
     * @param string $previewName
     * @param bool $buildIfNeeded
     *
     * @return AbstractImagePreview|ActiveQuery
     * @throws ImagePreviewServiceException
     */
    protected function findPreview(string $previewName, bool $buildIfNeeded = false)
    {
        return $buildIfNeeded
            ? $this->service->getPreview($this, $previewName)
            : $this->service->getRelationQuery($this, $previewName);
    }
}
