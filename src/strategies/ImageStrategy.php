<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\strategies;

use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\exceptions\ImagePreviewServiceException;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\models\base\AbstractImage;

class ImageStrategy extends BaseStrategy
{
    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     */
    public function doAfterUpload(AbstractFile $file): void
    {
        /** @var AbstractImage $image */
        $image = $file;

        parent::doAfterUpload($image);

        foreach ($image::getPreviewEntityClass()::getPossibleNames() as $previewName) {
            try {
                $this->fileService
                    ->getImagePreviewService()
                    ->build($image, $previewName);
            } catch (ImagePreviewServiceException $e) {
                throw new FileStrategyException("Building previews error: {$e->getMessage()}", 0, $e);
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(AbstractFile $file): void
    {
        /** @var AbstractImage $image */
        $image = $file;

        $this->fileService
            ->getImagePreviewService()
            ->clearPreviews($image);

        parent::delete($image);
    }
}
