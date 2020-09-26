<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services;

use Exception;
use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\exceptions\FileTypeServiceException;
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
     */
    public function takeFromStorage(AbstractFile $file): StorageObject
    {
        return $this->storage->take(HashHelper::encode($file->getRelativePath(), $file->system_name));
    }

    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     * @param bool $isForceMode
     *
     * @throws FileStrategyException
     * @throws FileServiceException
     * @throws Exception
     */
    public function putToStorage(AbstractFile $file, string $tempFilePath, bool $isForceMode = true): void
    {
        $hash = HashHelper::encode($file->getRelativePath(), $file->system_name);
        if (!$isForceMode && $this->existsInStorage($hash)) {
            throw new FileServiceException("File is already exists in storage with hash: {$hash}");
        }

        $this->getStrategy($file)->put($file, $tempFilePath);
    }

    /**
     * @param AbstractFile $file
     * @throws FileStrategyException
     * @throws FileTypeServiceException
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
     * @throws FileTypeServiceException
     */
    private function getStrategy(AbstractFile $file): FileStrategyInterface
    {
        $type = $this->fileTypeService->getType($file->mime);
        return $this->fileTypeService->getStrategy($type);
    }
}
