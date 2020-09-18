<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\models\base\AbstractFile;

interface FileStrategyInterface
{
    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     */
    public function doAfterUpload(AbstractFile $file): void;

    /**
     * @param AbstractFile $file
     * @throws FileServiceException
     */
    public function delete(AbstractFile $file): void;
}
