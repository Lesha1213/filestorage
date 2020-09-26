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
     */
    public function put(AbstractFile $file, string $tempFilePath): void
    {
        /** @var AbstractImage $image */
        $image = $file;
        $storageFileInfo = $this->createFileStorageInfo($file, $tempFilePath);

        try {
            $this->storage->put($storageFileInfo);
        } catch (StorageException $e) {
            throw new FileStrategyException("Strategy put error: {$e->getMessage()}", 0, $e);
        }

        $this->fillEntityAfterPut($image, $storageFileInfo);
        $this->saveEntity($image);

        try {
            $this->imagePreviewService->createPreviews($image);
        } catch (ImagePreviewServiceException $e) {
            throw new FileStrategyException("Strategy put error: {$e->getMessage()}", 0, $e);
        }

        $this->storage->removeFromTemp($storageFileInfo);
    }

    public function remove(AbstractFile $file): void
    {
        /** @var AbstractImage $image */
        $image = $file;

        try {
            $this->imagePreviewService->clearPreviews($image);
        } catch (ImagePreviewServiceException $e) {
            throw new FileStrategyException("Strategy remove error: {$e->getMessage()}", 0, $e);
        }

        parent::remove($image);
    }
}
