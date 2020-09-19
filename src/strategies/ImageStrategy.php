<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\strategies;

use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\exceptions\ImagePreviewServiceException;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\models\base\AbstractImage;
use reactivestudio\filestorage\services\image\ImagePreviewService;
use reactivestudio\filestorage\strategies\base\AbstractStrategy;
use yii\base\InvalidConfigException;

class ImageStrategy extends AbstractStrategy
{
    /**
     * @var ImagePreviewService
     */
    private $imagePreviewService;

    public function __construct(
        StorageInterface $storage,
        ImagePreviewService $imagePreviewService
    ) {
        parent::__construct($storage);

        $this->imagePreviewService = $imagePreviewService;
    }

    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     *
     * @throws FileStrategyException
     * @throws StorageException
     * @throws ImagePreviewServiceException
     * @throws InvalidConfigException
     */
    public function put(AbstractFile $file, string $tempFilePath): void
    {
        /** @var AbstractImage $image */
        $image = $file;
        $storageFileInfo = $this->createFileStorageInfo($file, $tempFilePath);

        $this->storage->put($storageFileInfo);

        $this->fillEntityAfterPut($image, $storageFileInfo);
        $this->saveEntity($image);

        $this->imagePreviewService->createPreviews($image);

        $this->storage->removeFromTemp($storageFileInfo);
    }

    public function remove(AbstractFile $file): void
    {
        /** @var AbstractImage $image */
        $image = $file;

        $this->imagePreviewService->clearPreviews($image);
        parent::remove($image);
    }
}
