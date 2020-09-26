<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages;

use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\storages\base\AbstractStorage;
use reactivestudio\filestorage\storages\dto\StorageObject;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Url;
use Yii;

class LocalStorage extends AbstractStorage
{
    /**
     * Название директории временного хранения и обработки файлов.
     */
    private const CONTAINERS_DIR = 'containers';

    /**
     * @var string|null
     */
    private $baseUrl;

    /**
     * Если заданы права,то после создания файла они будут принудительно назначены
     * @var int|null
     */
    private $fileMode;

    /**
     * @param int|null $fileMode
     * {@inheritDoc}
     */
    public function __construct(string $baseUrl, string $webFilesDir, ?int $fileMode = null)
    {
        parent::__construct($webFilesDir);

        $this->baseUrl = $baseUrl;
        $this->fileMode = $fileMode;
    }

    public static function getStorageDirs(): array
    {
        return ArrayHelper::merge(
            parent::getStorageDirs(),
            [static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::CONTAINERS_DIR]
        );
    }

    public function getName(): string
    {
        return 'local';
    }

    /**
     * {@inheritDoc}
     */
    public function isExists(string $hash): bool
    {
        $path = FileHelper::normalizePath(HashHelper::decode($hash));
        return file_exists($path);
    }

    /**
     * @param StorageObject $storageObject
     * @throws StorageException in case problem with coping file to temp
     */
    public function copyFromStorageToTemp(StorageObject $storageObject): void
    {
        $path = $this->getContainerAbsolutePath() . DIRECTORY_SEPARATOR
            . $storageObject->getRelativePath() . DIRECTORY_SEPARATOR
            . $storageObject->getFileName();
        $path = FileHelper::normalizePath(Yii::getAlias($path));

        $destination = $this->getTempDir() . DIRECTORY_SEPARATOR . $storageObject->getFileName();
        $destination = FileHelper::normalizePath(Yii::getAlias($destination));

        StorageHelper::copy($path, $destination);
        $storageObject->setTempAbsolutePath($destination);
    }

    /**
     * @param string $tempPath
     * @param string $destination
     * @return bool
     */
    protected function copyFromTempToStorage(string $tempPath, string $destination): bool
    {
        try {
            StorageHelper::copy($tempPath, $destination);
        } catch (StorageException $e) {
            Yii::warning($e->getMessage());
            return false;
        }

        if (null !== $this->fileMode && !chmod($destination, $this->fileMode)) {
            Yii::warning("Cannot change file mode 'chmod' to {$this->fileMode}");
            return false;
        }

        return true;
    }

    /**
     * @param StorageObject $storageObject
     * @return string
     */
    protected function buildFileDestination(StorageObject $storageObject): string
    {
        $destination = $this->getContainerAbsolutePath() . DIRECTORY_SEPARATOR
            . $storageObject->getRelativePath() . DIRECTORY_SEPARATOR
            . $storageObject->getFileName();

        return FileHelper::normalizePath(Yii::getAlias($destination));
    }

    protected function removeFromStorage(string $hash): bool
    {
        $path = $this->getContainerAbsolutePath() . DIRECTORY_SEPARATOR . HashHelper::decode($hash);
        $path = FileHelper::normalizePath(Yii::getAlias($path));

        try {
            StorageHelper::deleteFile($path);
        } catch (StorageException $e) {
            Yii::warning($e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function buildPublicUrl(string $hash): string
    {
        $path = static::STORAGE_DIR . DIRECTORY_SEPARATOR
            . static::CONTAINERS_DIR . DIRECTORY_SEPARATOR . HashHelper::decode($hash);

        $path = str_replace('\\', '/', $path);

        return null !== $this->baseUrl
            ? Url::to($this->baseUrl . '/' . $path, true)
            : Url::base(true) . $path;
    }

    /**
     * @return string
     */
    private function getContainerAbsolutePath(): string
    {
        return $this->webFilesDir . DIRECTORY_SEPARATOR . static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::CONTAINERS_DIR;
    }
}
