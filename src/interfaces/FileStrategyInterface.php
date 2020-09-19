<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\models\base\AbstractFile;

interface FileStrategyInterface
{
    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     * @throws FileStrategyException
     */
    public function put(AbstractFile $file, string $tempFilePath): void;

    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     */
    public function remove(AbstractFile $file): void;
}
