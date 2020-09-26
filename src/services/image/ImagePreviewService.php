<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image;

use Exception;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\exceptions\ImagePreviewServiceException;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\interfaces\OperationInterface;
use reactivestudio\filestorage\models\base\AbstractImage;
use reactivestudio\filestorage\models\base\preview\AbstractImagePreview;
use reactivestudio\filestorage\storages\dto\StorageObject;
use yii\base\InvalidConfigException;
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
     * @param string $previewName
     *
     * @return AbstractImagePreview
     *
     * @throws ImagePreviewServiceException
     * @throws InvalidConfigException
     * @throws StorageException
     */
    public function getPreview(AbstractImage $image, string $previewName): AbstractImagePreview
    {
        $preview = $this->findPreview($image, $previewName);

        if (
            null === $preview
            || !$this->isPreviewInStorage($preview)
            || !$this->isPreviewActual($image, $preview)
        ) {
            $preview = $this->createPreview($image, $previewName);
        }

        return $preview;
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     *
     * @throws ImagePreviewServiceException
     * @throws InvalidConfigException
     * @throws StorageException
     */
    public function reCreatePreview(AbstractImage $image, string $previewName): void
    {
        $preview = $this->getPreview($image, $previewName);
        $this->clearPreview($preview);
        $this->createPreview($image, $previewName);
    }

    /**
     * @param AbstractImage $image
     *
     * @throws ImagePreviewServiceException
     * @throws InvalidConfigException
     * @throws StorageException
     */
    public function createPreviews(AbstractImage $image): void
    {
        $storageFileInfo = $this->storage->take($image->hash);
        $this->storage->copyToTemp($storageFileInfo);

        $interventionImage = $this->imageManager->make($storageFileInfo->getTempAbsolutePath());

        foreach ($image::getPreviewEntityClass()::getPossibleNames() as $previewName) {
            $this->buildPreview($image, $previewName, $interventionImage, $storageFileInfo->getTempAbsolutePath());
        }

        $this->storage->removeFromTemp($storageFileInfo);
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     *
     * @return AbstractImagePreview
     *
     * @throws ImagePreviewServiceException
     * @throws InvalidConfigException
     * @throws StorageException
     */
    public function createPreview(AbstractImage $image, string $previewName): AbstractImagePreview
    {
        $storageFileInfo = $this->storage->take($image->hash);
        $this->storage->copyToTemp($storageFileInfo);

        $interventionImage = $this->imageManager->make($storageFileInfo->getTempAbsolutePath());

        $preview = $this->buildPreview(
            $image,
            $previewName,
            $interventionImage,
            $storageFileInfo->getTempAbsolutePath()
        );

        $this->storage->removeFromTemp($storageFileInfo);

        return $preview;
    }

    /**
     * @param AbstractImage $image
     */
    public function clearPreviews(AbstractImage $image): void
    {
        foreach ($image->previews as $preview) {
            $this->clearPreview($preview);
        }
    }

    /**
     * @param AbstractImagePreview $preview
     */
    public function clearPreview(AbstractImagePreview $preview): void
    {
        if (!$this->storage->isExists($preview->hash)) {
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

        $actualPreviewFileName = $this->getPreviewFileName($operation, $preview->name, $image->original_extension);

        return $preview->system_name === $actualPreviewFileName;
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
     * @param Image $interventionImage
     * @param string $originalTempAbsolutePath
     *
     * @return AbstractImagePreview
     *
     * @throws ImagePreviewServiceException
     * @throws StorageException
     * @throws InvalidConfigException
     */
    private function buildPreview(
        AbstractImage $image,
        string $previewName,
        Image $interventionImage,
        string $originalTempAbsolutePath
    ): AbstractImagePreview {
        $operation = $this::getOperation($image, $previewName);
        $operation->apply($interventionImage);

        $previewTempAbsolutePath = $this->getPreviewTempPath(
            $originalTempAbsolutePath,
            $this->getPreviewFileName($operation, $previewName, $image->original_extension)
        );

        $interventionImage->save($previewTempAbsolutePath, 100);
        $size = (int)filesize($previewTempAbsolutePath);

        $storagePreviewInfo = (new StorageObject())
            ->setTempAbsolutePath($previewTempAbsolutePath)
            ->setRelativePath($this->getPreviewRelativePath($image))
            ->setFileName($this->getPreviewFileName($operation, $previewName, $image->original_extension));

        $this->storage->put($storagePreviewInfo);
        $this->storage->removeFromTemp($storagePreviewInfo);

        return $this->createPreviewEntity($image, $previewName, $storagePreviewInfo, $size);
    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     * @param StorageObject $storagePreviewInfo
     * @param int $size
     *
     * @return AbstractImagePreview
     *
     * @throws InvalidConfigException
     * @throws ImagePreviewServiceException
     */
    private function createPreviewEntity(
        AbstractImage $image,
        string $previewName,
        StorageObject $storagePreviewInfo,
        int $size
    ): AbstractImagePreview {
        /** @var AbstractImagePreview $entity */
        $entity = Yii::createObject($image::getPreviewEntityClass());

        $entity->original_file_id = $image->id;
        $entity->storage_name = $this->storage->getName();
        $entity->storage_status = $this->storage::STATUS_IN_STORAGE;
        $entity->name = $previewName;
        $entity->hash = HashHelper::encode(
            $this->getPreviewRelativePath($image),
            $storagePreviewInfo->getFileName()
        );
        $entity->system_name = $storagePreviewInfo->getFileName();
        $entity->size = $size;
        $entity->public_url = $storagePreviewInfo->getPublicUrl();

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

    private function getPreviewFileName(OperationInterface $operation, string $previewName, string $extension): string
    {
        return $previewName . '_' . $operation->getSystemName() . '.' . $extension;
    }

    private function getPreviewTempPath(string $originalTempPath, string $previewFileName): string
    {
        return StorageHelper::getDirName($originalTempPath) . DIRECTORY_SEPARATOR . $previewFileName;
    }
}
