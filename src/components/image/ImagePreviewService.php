<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components\image;

use Intervention\Image\ImageManager;
use reactivestudio\filestorage\components\FileService;
use reactivestudio\filestorage\exceptions\ImagePreviewServiceException;
use reactivestudio\filestorage\models\base\AbstractImage;
use yii\base\InvalidConfigException;
use Yii;

class ImagePreviewService
{
    public const PREVIEWS_DIR = 'previews';

    /**
     * @var FileService
     */
    private $fileService;

    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * @param ImageManager $imageManager
     * @throws InvalidConfigException
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->fileService = Yii::$app->get('fileService');
        $this->imageManager = $imageManager;
    }

    public function reBuild(AbstractImage $image, string $previewName): void
    {

    }

    /**
     * @param AbstractImage $image
     * @param string $previewName
     * @throws ImagePreviewServiceException
     */
    public function build(AbstractImage $image, string $previewName): void
    {
        $fileStorageInfo = $this->fileService->getStorage()->take($image->hash);
        $this->fileService->getStorage()->copyToTemp($fileStorageInfo);

        $interventionImage = $this->imageManager->make($fileStorageInfo->getTempAbsolutePath());
        $operation = $image::getPreviewEntityClass()::findOperation($previewName);

        if (null === $operation) {
            throw new ImagePreviewServiceException("Operation is not found for preview name: {$previewName}");
        }

        $operation->apply($interventionImage);


    }

    public function clearPreviews(AbstractImage $image): void
    {

    }
}
