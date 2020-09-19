<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\strategies\base;

use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\storages\dto\StorageFileInfo;
use Throwable;
use yii\db\StaleObjectException;
use yii\helpers\VarDumper;

abstract class AbstractStrategy implements FileStrategyInterface
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    public function __construct(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     *
     * @throws StorageException
     * @throws FileStrategyException
     */
    public function put(AbstractFile $file, string $tempFilePath): void
    {
        $storageFileInfo = $this->createFileStorageInfo($file, $tempFilePath);

        $this->storage->put($storageFileInfo);
        $this->storage->removeFromTemp($storageFileInfo);

        $this->fillEntityAfterPut($file, $storageFileInfo);
        $this->saveEntity($file);
    }

    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     */
    public function remove(AbstractFile $file): void
    {
        $hash = HashHelper::encode($file->getRelativePath(), $file->system_name);
        $this->storage->remove($hash);
        try {
            $file->delete();
        } catch (StaleObjectException | Throwable $e) {
            throw new FileStrategyException("Cannot delete entity. Error: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     * @return StorageFileInfo
     */
    protected function createFileStorageInfo(AbstractFile $file, string $tempFilePath): StorageFileInfo
    {
        return (new StorageFileInfo())
            ->setRelativePath($file->getRelativePath())
            ->setFileName($file->system_name)
            ->setTempAbsolutePath($tempFilePath)
            ->setUploadState(false);
    }

    /**
     * @param AbstractFile $file
     * @param StorageFileInfo $storageFileInfo
     */
    protected function fillEntityAfterPut(AbstractFile $file, StorageFileInfo $storageFileInfo): void
    {
        $file->storage_name = $this->storage->getName();
        $file->storage_status = $this->storage::STATUS_IN_STORAGE;
        $file->public_url = $storageFileInfo->getPublicUrl();
    }

    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     */
    protected function saveEntity(AbstractFile $file): void
    {
        if (!$file->validate()) {
            throw new FileStrategyException(
                'Cannot save file entity. Errors: '
                . VarDumper::dumpAsString($file->getErrorSummary(true))
            );
        }

        if (!$file->save()) {
            throw new FileStrategyException('Cannot save file entity for unknown reason');
        }
    }
}
