<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages\base;

use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\storages\dto\StorageObject;
use reactivestudio\filestorage\exceptions\StorageException;
use yii\base\InvalidConfigException;
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
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @param string $webFilesDir
     *
     * @throws InvalidConfigException
     */
    public function __construct(string $webFilesDir)
    {
        $this->webFilesDir = $webFilesDir;

        $this->checkDirs();
    }

    /**
     * @param string $hash
     * @return bool
     */
    abstract public function isExists(string $hash): bool;

    /**
     * @param StorageObject $storageObject
     * @throws StorageException
     */
    abstract public function put(StorageObject $storageObject): void;

    abstract public function remove(string $hash): void;

    abstract public function copyToTemp(StorageObject $storageFileInfo): void;

    abstract protected function getPublicUrl(string $hash): string;

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
     * @throws StorageException in case storage object is not found
     */
    public function take(string $hash): StorageObject
    {
        if (!$this->isExists($hash)) {
            throw new StorageException("Storage object is not found with hash: {$hash}");
        }

        $path = HashHelper::decode($hash);
        $path = FileHelper::normalizePath($path);

        return (new StorageObject())
            ->setRelativePath(StorageHelper::getDirName($path))
            ->setFileName(StorageHelper::getFileName($path))
            ->setPublicUrl($this->getPublicUrl($hash))
            ->setAvailability(true);
    }

    /**
     * @param StorageObject $storageFileInfo
     * @throws StorageException
     */
    public function removeFromTemp(StorageObject $storageFileInfo): void
    {
        StorageHelper::deleteFile($storageFileInfo->getTempAbsolutePath());
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
     * @throws InvalidConfigException in case there is a problem with creation directory
     */
    protected function checkDirs(): void
    {
        foreach (static::getStorageDirs() as $dir) {
            $path = $this->webFilesDir . DIRECTORY_SEPARATOR . $dir;

            try {
                StorageHelper::touchDir($path);
            } catch (StorageException $e) {
                throw new InvalidConfigException(
                    "Cannot create storageDir: {$path}. \n Error: {$e->getMessage()}"
                );
            }
        }
    }
}
