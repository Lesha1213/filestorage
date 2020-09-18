<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages;

use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\storages\base\AbstractStorage;
use reactivestudio\filestorage\storages\dto\StorageFileInfo;
use reactivestudio\filestorage\exceptions\StorageException;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use Yii;

class LocalStorage extends AbstractStorage
{
    /**
     * Название директории временного хранения и обработки файлов.
     */
    private const FILES_DIR = 'files';

    public static function getStorageDirs(): array
    {
        return ArrayHelper::merge(
            parent::getStorageDirs(),
            [static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::FILES_DIR]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(string $hash): bool
    {
        $path = FileHelper::normalizePath($this->hashService->decode($hash));
        return file_exists($path);
    }

    /**
     * @param StorageFileInfo $storageFileInfo
     * @throws StorageException in case problem with putting file to storage
     */
    public function put(StorageFileInfo $storageFileInfo): void
    {
        $destination = $this->getFilesDir() . DIRECTORY_SEPARATOR
            . $storageFileInfo->getRelativePath() . DIRECTORY_SEPARATOR
            . $storageFileInfo->getFileName();
        $destination = FileHelper::normalizePath(Yii::getAlias($destination));

        StorageHelper::copy($storageFileInfo->getTempAbsolutePath(), $destination);
    }

    /**
     * @param string $hash
     * @throws StorageException
     */
    public function remove(string $hash): void
    {
        if (!$this->isExists($hash)) {
            return;
        }

        $path = $this->getFilesDir() . DIRECTORY_SEPARATOR . $this->hashService->decode($hash);
        $path = FileHelper::normalizePath(Yii::getAlias($path));

        StorageHelper::deleteFile($path);
    }

    /**
     * @param StorageFileInfo $storageFileInfo
     * @throws StorageException in case problem with coping file to temp
     */
    public function copyToTemp(StorageFileInfo $storageFileInfo): void
    {
        $path = $this->getFilesDir() . DIRECTORY_SEPARATOR
            . $storageFileInfo->getRelativePath() . DIRECTORY_SEPARATOR
            . $storageFileInfo->getFileName();
        $path = FileHelper::normalizePath(Yii::getAlias($path));

        $destination = $this->getTempDir() . DIRECTORY_SEPARATOR . $storageFileInfo->getFileName();
        $destination = FileHelper::normalizePath(Yii::getAlias($destination));

        StorageHelper::copy($path, $destination);
        $storageFileInfo->setTempAbsolutePath($destination);
    }

    /**
     * @return string
     */
    private function getFilesDir(): string
    {
        return $this->webFilesDir . DIRECTORY_SEPARATOR . static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::FILES_DIR;
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function getPublicUrl(string $hash): string
    {
        $path = $this->getFilesDir() . DIRECTORY_SEPARATOR . $this->hashService->decode($hash);
        $path = str_replace('\\', '/', $path);

        if (null !== $this->baseUrl) {
            return Url::to($this->baseUrl . '/' . $path, true);
        }

        return Url::base(true) . $path;
    }
}
