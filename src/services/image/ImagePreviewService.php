<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image;

use Exception;
use Intervention\Image\ImageManager;
use reactivestudio\filestorage\interfaces\OperationInterface;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\exceptions\ImagePreviewServiceException;
use reactivestudio\filestorage\models\base\AbstractImage;
use reactivestudio\filestorage\models\base\preview\AbstractImagePreview;
use reactivestudio\filestorage\services\image\dto\PreviewBuildObject;
use reactivestudio\filestorage\storages\dto\StorageObject;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\helpers\HashHelper;
use Throwable;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use Yii;

class ImagePreviewService
{
    public const PREVIEWS_DIR = 'previews';

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var ImageManager
     */
    private $imageManager;

    public function __construct(
        StorageInterface $storage,
        ImageManager $imageManager
    ) {
        $this->storage = $storage;
        $this->imageManager = $imageManager;
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     *
     * @return OperationInterface
     * @throws ImagePreviewServiceException
     */
    public static function getOperation(AbstractImage $image, string $previewName): OperationInterface
    {
        try {
            /** @var OperationInterface|null $operation */
            $operation = ArrayHelper::getValue($image::getPreviewEntityClass()::operations(), $previewName, null);
        } catch (Exception $e) {
            throw new ImagePreviewServiceException("Error: {$e->getMessage()} for preview: {$previewName}");
        }

        if (null === $operation) {
            throw new ImagePreviewServiceException("Operation is not found for preview name: {$previewName}");
        }

        return $operation;
    }

    /**
     * @param AbstractImage $image
     * @param string|null $previewName
     *
     * @return ActiveQuery
     */
    public function getRelationQuery(AbstractImage $image, ?string $previewName = null): ActiveQuery
    {
        return null !== $previewName
            ? $image
                ->hasOne($image::getPreviewEntityClass(), ['original_file_id' => 'id'])
                ->onCondition(['name' => $previewName])
            : $image->hasMany($image::getPreviewEntityClass(), ['original_file_id' => 'id'])
                ->orderBy(['name' => SORT_ASC]);
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     *
     * @return AbstractImagePreview
     * @throws ImagePreviewServiceException
     */
    public function getPreview(AbstractImage $image, string $previewName): AbstractImagePreview
    {
        $preview = $this->findPreview($image, $previewName);

        if (
            null === $preview
            || !$this->isPreviewInStorage($preview)
            || $this->isPreviewActual($image, $preview)
        ) {
            $preview = $this->createPreview($image, $previewName);
        }

        return $preview;
    }

    /**
     * @param AbstractImage $image
     * @throws ImagePreviewServiceException
     */
    public function createPreviews(AbstractImage $image): void
    {
        try {
            $storageObject = $this->storage->take($image->hash);
        } catch (StorageException $e) {
            throw new ImagePreviewServiceException("Error creating preview: {$e->getMessage()}", 0, $e);
        }
        $this->storage->copyToTemp($storageObject);

        $interventionImage = $this->imageManager->make($storageObject->getTempAbsolutePath());

        foreach ($image::getPreviewEntityClass()::getPossibleNames() as $previewName) {
            $buildObject = (new PreviewBuildObject())
                ->setOriginalImage($image)
                ->setOriginalTempAbsolutePath($storageObject->getTempAbsolutePath())
                ->setPreviewName($previewName)
                ->setInterventionImage($interventionImage);

            $this->buildPreview($buildObject);
        }

        $this->storage->removeFromTemp($storageObject);
    }

    /**
     * @param AbstractImage $image
     * @throws ImagePreviewServiceException
     */
    public function clearPreviews(AbstractImage $image): void
    {
        foreach ($image->previews as $preview) {
            $this->clearPreview($preview);
        }
    }

    /**
     * @param AbstractImagePreview $preview
     * @throws ImagePreviewServiceException
     */
    public function clearPreview(AbstractImagePreview $preview): void
    {
        if (!$this->isPreviewInStorage($preview)) {
            try {
                $preview->delete();
            } catch (Throwable $e) {
                throw new ImagePreviewServiceException(
                    "Error with removing image preview entity: {$e->getMessage()}"
                );
            }

            return;
        }

        $this->storage->remove($preview->hash);
    }

    /**
     * @param AbstractImagePreview $preview
     * @return bool
     */
    public function isPreviewInStorage(AbstractImagePreview $preview): bool
    {
        return $this->storage->isExists($preview->hash);
    }

    /**
     * @param AbstractImage $image
     * @param AbstractImagePreview $preview
     * @return bool
     */
    public function isPreviewActual(AbstractImage $image, AbstractImagePreview $preview): bool
    {
        try {
            $operation = $this::getOperation($image, $preview->name);
        } catch (ImagePreviewServiceException $e) {
            Yii::warning("Error: {$e->getMessage()}");
            return false;
        }

        return $preview->preview_config === $operation->getConfig();
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     * @return AbstractImagePreview|null
     */
    public function findPreview(AbstractImage $image, string $previewName): ?AbstractImagePreview
    {
        /** @var AbstractImagePreview|null $preview */
        $preview = $image
            ::getPreviewEntityClass()
            ::find()
            ->where([
                'original_file_id' => $image->id,
                'name' => $previewName,
            ])
            ->one($image::getDb());

        return $preview;
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     *
     * @return AbstractImagePreview
     * @throws ImagePreviewServiceException
     */
    private function createPreview(AbstractImage $image, string $previewName): AbstractImagePreview
    {
        try {
            $storageObject = $this->storage->take($image->hash);
        } catch (StorageException $e) {
            throw new ImagePreviewServiceException("Error creating preview: {$e->getMessage()}", 0, $e);
        }

        $this->storage->copyToTemp($storageObject);

        $buildObject = (new PreviewBuildObject())
            ->setOriginalImage($image)
            ->setOriginalTempAbsolutePath($storageObject->getTempAbsolutePath())
            ->setPreviewName($previewName)
            ->setInterventionImage($this->imageManager->make($storageObject->getTempAbsolutePath()));

        $oldPreview = $this->findPreview($image, $previewName);
        if (null !== $oldPreview) {
            $this->clearPreview($oldPreview);
        }

        $preview = $this->buildPreview($buildObject);
        $this->storage->removeFromTemp($storageObject);

        return $preview;
    }

    /**
     * @param PreviewBuildObject $buildObject
     * @return AbstractImagePreview
     * @throws ImagePreviewServiceException
     */
    private function buildPreview(PreviewBuildObject $buildObject): AbstractImagePreview
    {
        $buildObject->setOperation(
            $this::getOperation($buildObject->getOriginalImage(), $buildObject->getPreviewName())
        );

        $buildObject->getOperation()->apply($buildObject->getInterventionImage());

        $previewFileName = uniqid('', true) . '.'
            . $buildObject->getOriginalImage()->original_extension;

        $previewTempAbsolutePath = $this->getPreviewTempPath(
            $buildObject->getOriginalTempAbsolutePath(),
            $previewFileName
        );

        $buildObject->getInterventionImage()->save($previewTempAbsolutePath, 100);
        $buildObject->setSize((int)filesize($previewTempAbsolutePath));

        $storageObject = (new StorageObject())
            ->setTempAbsolutePath($previewTempAbsolutePath)
            ->setRelativePath($this->getPreviewRelativePath($buildObject->getOriginalImage()))
            ->setFileName($previewFileName);

        $buildObject->setStorageObject($storageObject);

        try {
            $this->storage->put($storageObject);
        } catch (StorageException $e) {
            throw new ImagePreviewServiceException("Error building preview: {$e->getMessage()}", 0, $e);
        }
        $this->storage->removeFromTemp($storageObject);

        return $this->createPreviewEntity($buildObject);
    }

    /**
     * @param PreviewBuildObject $buildObject
     * @return AbstractImagePreview
     * @throws ImagePreviewServiceException
     */
    private function createPreviewEntity(PreviewBuildObject $buildObject): AbstractImagePreview
    {
        try {
            /** @var AbstractImagePreview $entity */
            $entity = Yii::createObject($buildObject->getOriginalImage()::getPreviewEntityClass());
        } catch (InvalidConfigException $e) {
            throw new ImagePreviewServiceException(
                "Error creating preview entity: {$e->getMessage()}", 0, $e
            );
        }

        $entity->original_file_id = $buildObject->getOriginalImage()->id;
        $entity->storage_name = $this->storage->getName();
        $entity->storage_status = $this->storage::STATUS_IN_STORAGE;
        $entity->name = $buildObject->getPreviewName();
        $entity->hash = HashHelper::encode(
            $this->getPreviewRelativePath($buildObject->getOriginalImage()),
            $buildObject->getStorageObject()->getFileName()
        );
        $entity->preview_config = $buildObject->getOperation()->getConfig();
        $entity->system_name = $buildObject->getStorageObject()->getFileName();
        $entity->size = $buildObject->getSize();
        $entity->public_url = $buildObject->getStorageObject()->getPublicUrl();

        if (!$entity->validate()) {
            throw new ImagePreviewServiceException(
                'Cannot save preview entity. Errors: '
                . VarDumper::dumpAsString($entity->getErrorSummary(true))
            );
        }

        if (!$entity->save()) {
            throw new ImagePreviewServiceException('Cannot save preview entity for unknown reason');
        }

        return $entity;
    }

    private function getPreviewRelativePath(AbstractImage $image): string
    {
        return $image->getRelativePath() . DIRECTORY_SEPARATOR . static::PREVIEWS_DIR;
    }

    private function getPreviewTempPath(string $originalTempPath, string $previewFileName): string
    {
        return StorageHelper::getDirName($originalTempPath) . DIRECTORY_SEPARATOR . $previewFileName;
    }
}
