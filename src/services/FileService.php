<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services;

use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\exceptions\FileTypeServiceException;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\storages\dto\StorageObject;
use reactivestudio\filestorage\models\base\AbstractFile;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use Yii;

/**
 * @package reactivestudio\filestorage\components
 */
class FileService
{
    /**
     * Базовый путь к директории хранения и обработки файлов.
     * @var string
     */
    public $webFilesDir;

    /**
     * Если заданы права,то после создания файла они будут принудительно назначены
     * @var number|null
     */
    public $fileMode;

    /**
     * @var FileTypeService
     */
    private $fileTypeService;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @param StorageInterface $storage
     * @param string $webFilesDir
     *
     * @throws InvalidConfigException
     * @throws NotInstantiableException
     */
    public function __construct(
        StorageInterface $storage,
        string $webFilesDir = '@app/web/files'
    ) {

        $this->storage = $storage;
        $this->webFilesDir = $webFilesDir;

        $this->fileTypeService = Yii::$container->get(FileTypeService::class);
    }

    /**
     * @param AbstractFile $file
     * @return StorageObject
     * @throws FileServiceException
     */
    public function takeFromStorage(AbstractFile $file): StorageObject
    {
        try {
            return $this->storage->take(HashHelper::encode($file->getRelativePath(), $file->system_name));
        } catch (StorageException $e) {
            throw new FileServiceException("Error take file from storage: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     * @param bool $isForceMode
     *
     * @throws FileServiceException
     */
    public function putToStorage(AbstractFile $file, string $tempFilePath, bool $isForceMode = true): void
    {
        $hash = HashHelper::encode($file->getRelativePath(), $file->system_name);
        if (!$isForceMode && $this->existsInStorage($hash)) {
            throw new FileServiceException("File is already exists in storage with hash: {$hash}");
        }

        try {
            $this->getStrategy($file)->put($file, $tempFilePath);
        } catch (FileStrategyException $e) {
            throw new FileServiceException("Error put file to storage: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     * @throws FileServiceException
     */
    public function removeFromStorage(AbstractFile $file): void
    {
        $hash = HashHelper::encode($file->getRelativePath(), $file->system_name);
        if (!$this->existsInStorage($hash)) {
            return;
        }

        $this->getStrategy($file)->remove($file);
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function existsInStorage(string $hash): bool
    {
        return $this->storage->isExists($hash);
    }

    /**
     * @param AbstractFile $file
     * @return FileStrategyInterface
     * @throws FileServiceException
     */
    private function getStrategy(AbstractFile $file): FileStrategyInterface
    {
        $type = $this->fileTypeService->getType($file->mime);
        try {
            return $this->fileTypeService->getStrategy($type);
        } catch (FileTypeServiceException $e) {
            throw new FileServiceException("Cannot get file strategy: {$e->getMessage()}", 0, $e);
        }
    }
}
