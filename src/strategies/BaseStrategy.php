<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\strategies;

use reactivestudio\filestorage\components\FileService;
use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use Throwable;
use yii\db\StaleObjectException;

class BaseStrategy implements FileStrategyInterface
{
    /**
     * @var FileService
     */
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @param AbstractFile $file
     */
    public function doAfterUpload(AbstractFile $file): void
    {
    }

    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     */
    public function delete(AbstractFile $file): void
    {
        $this->fileService->remove($file);

        try {
            $isDeleted = (bool)$file->delete();
        } catch (StaleObjectException | Throwable $e) {
            throw new FileStrategyException("File entity delete error {$e->getMessage()}", 0, $e);
        }

        if (!$isDeleted) {
            throw new FileStrategyException("File entity delete error");
        }
    }
}
