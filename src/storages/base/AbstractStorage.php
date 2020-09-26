<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages\base;

use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\exceptions\StorageObjectIsNotFoundException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\storages\dto\StorageObject;
use reactivestudio\filestorage\exceptions\StorageObjectIsAlreadyExistsException;
use yii\helpers\FileHelper;

abstract class AbstractStorage implements StorageInterface
{
    /**
     * Название дирректрии хранения и обработки файлов.
     */
    protected const STORAGE_DIR = 'storage';

    /**
     * Название директории временного хранения и обработки файлов.
     */
    protected const TEMP_DIR = 'temp';

    /**
     * @var string
     */
    protected $webFilesDir;

    /**
     * @param string $webFilesDir
     * @throws StorageException
     */
    public function __construct(string $webFilesDir)
    {
        $this->webFilesDir = $webFilesDir;
        $this->checkDirs();
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param string $hash
     * @return bool
     */
    abstract public function isExists(string $hash): bool;

    abstract public function copyToTemp(StorageObject $storageObject): void;

    /**
     * @param string $tempPath
     * @param string $destination
     * @return bool
     */
    abstract protected function copyToStorage(string $tempPath, string $destination): bool;

    /**
     * @param StorageObject $storageObject
     * @return string
     */
    abstract protected function buildFileDestination(StorageObject $storageObject): string;

    abstract protected function buildPublicUrl(string $hash): string;

    abstract protected function removeFromStorage(string $hash): bool;

    /**
     * @return string[]
     */
    public static function getStorageDirs(): array
    {
        return [
            static::STORAGE_DIR,
            static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::TEMP_DIR,
        ];
    }

    /**
     * @param string $hash
     * @return StorageObject
     * @throws StorageObjectIsNotFoundException
     */
    public function take(string $hash): StorageObject
    {
        if (!$this->isExists($hash)) {
            throw new StorageObjectIsNotFoundException("Storage object is not found with hash: {$hash}");
        }

        $path = HashHelper::decode($hash);
        $path = FileHelper::normalizePath($path);

        return (new StorageObject())
            ->setRelativePath(StorageHelper::getDirName($path))
            ->setFileName(StorageHelper::getFileName($path))
            ->setPublicUrl($this->buildPublicUrl($hash))
            ->setAvailability(true);
    }

    /**
     * @param StorageObject $storageObject
     * @throws StorageObjectIsAlreadyExistsException
     * @throws StorageException
     */
    public function put(StorageObject $storageObject): void
    {
        $hash = HashHelper::encode($storageObject->getRelativePath(), $storageObject->getFileName());

        if ($this->isExists($hash)) {
            if ($storageObject->isForceMode()) {
                $this->remove($hash);
            } else {
                throw new StorageObjectIsAlreadyExistsException(
                    "Storage object is already exists with hash: {$hash}"
                );
            }
        }

        $destination = $this->buildFileDestination($storageObject);
        $isCopied = $this->copyToStorage($storageObject->getTempAbsolutePath(), $destination);

        if (!$isCopied) {
            throw new StorageException(
                "File put in storage error. \n 
                Temp path: {$storageObject->getTempAbsolutePath()}, \n
                Destination: {$destination}"
            );
        }

        $storageObject->setPublicUrl($this->buildPublicUrl($hash));
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

        if (!$this->removeFromStorage($hash)) {
            throw new StorageException("File is not removed from storage with hash: {$hash}");
        }
    }

    /**
     * @param StorageObject $storageObject
     * @throws StorageException
     */
    public function removeFromTemp(StorageObject $storageObject): void
    {
        StorageHelper::deleteFile($storageObject->getTempAbsolutePath());
    }

    /**
     * @return string
     */
    protected function getTempDir(): string
    {
        return $this->webFilesDir . DIRECTORY_SEPARATOR . static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::TEMP_DIR;
    }

    /**
     * Checks directories
     * @throws StorageException in case there is a problem with creation directory
     */
    protected function checkDirs(): void
    {
        foreach (static::getStorageDirs() as $dir) {
            $path = $this->webFilesDir . DIRECTORY_SEPARATOR . $dir;
            StorageHelper::touchDir($path);
        }
    }
}
