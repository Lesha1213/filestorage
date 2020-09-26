<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages;

use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\storages\base\AbstractStorage;
use reactivestudio\filestorage\storages\dto\StorageObject;
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
     * @throws StorageException in case problem with putting file to storage
     */
    public function put(StorageObject $storageObject): void
    {
        $destination = $this->getContainerAbsolutePath() . DIRECTORY_SEPARATOR
            . $storageObject->getRelativePath() . DIRECTORY_SEPARATOR
            . $storageObject->getFileName();
        $destination = FileHelper::normalizePath(Yii::getAlias($destination));

        if (null !== $this->fileMode && !chmod($destination, $this->fileMode)) {
            throw new StorageException("Cannot change file mode 'chmod' to {$this->fileMode}");
        }

        StorageHelper::copy($storageObject->getTempAbsolutePath(), $destination);

        $hash = HashHelper::encode($storageObject->getRelativePath(), $storageObject->getFileName());
        $storageObject->setPublicUrl($this->getPublicUrl($hash));
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

        $path = $this->getContainerAbsolutePath() . DIRECTORY_SEPARATOR . HashHelper::decode($hash);
        $path = FileHelper::normalizePath(Yii::getAlias($path));

        StorageHelper::deleteFile($path);
    }

    /**
     * @param StorageObject $storageFileInfo
     * @throws StorageException in case problem with coping file to temp
     */
    public function copyToTemp(StorageObject $storageFileInfo): void
    {
        $path = $this->getContainerAbsolutePath() . DIRECTORY_SEPARATOR
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
    private function getContainerAbsolutePath(): string
    {
        return $this->webFilesDir . DIRECTORY_SEPARATOR . static::STORAGE_DIR . DIRECTORY_SEPARATOR . static::CONTAINERS_DIR;
    }

    /**
     * @param string $hash
     * @return string
     */
    protected function getPublicUrl(string $hash): string
    {
        $path = static::STORAGE_DIR . DIRECTORY_SEPARATOR
            . static::CONTAINERS_DIR . DIRECTORY_SEPARATOR . HashHelper::decode($hash);

        $path = str_replace('\\', '/', $path);

        return null !== $this->baseUrl
            ? Url::to($this->baseUrl . '/' . $path, true)
            : Url::base(true) . $path;
    }
}
