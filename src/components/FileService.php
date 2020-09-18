<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\components;

use reactivestudio\filestorage\components\image\ImagePreviewService;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\storages\dto\StorageFileInfo;
use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\models\base\AbstractFile;
use ReflectionClass;
use ReflectionException;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;
use Yii;

/**
 * Основной "StateLess" компонент для управления файлами
 *
 * @package reactivestudio\filestorage\components
 */
class FileService extends Component
{
    /**
     * @var string|null
     */
    public $storageClass;

    /**
     * Базовый URL, который будет подставляться при генерации url к файлу.
     * Если не задан, то будет использован текущий хост
     *
     * @var string|null
     */
    public $baseUrl;

    /**
     * Базовый путь к директории хранения и обработки файлов.
     * @var string
     */
    public $webFilesDir = '@app/web/files';

    /**
     * Если заданы права,то после создания файла они будут принудительно назначены
     * @var number|null
     */
    public $fileMode;

    /**
     * @var StorageInterface
     */
    private $storage;

    /**
     * @var HashService
     */
    private $hashService;

    /**
     * @var ImagePreviewService
     */
    private $imagePreviewService;

    public function __construct(
        HashService $hashService,
        ImagePreviewService $imagePreviewService,
        $config = []
    ) {
        parent::__construct($config);

        $this->hashService = $hashService;
        $this->imagePreviewService = $imagePreviewService;
    }

    /**
     * Проверяем корректность параметров конфигурации компонента
     * @throws InvalidConfigException
     * @throws ReflectionException
     */
    public function init(): void
    {
        if (empty($this->storageClass)) {
            throw new InvalidConfigException($this->getInitErrorMessage('storageClass'));
        }

        $reflector = new ReflectionClass($this->storageClass);
        if (!$reflector->implementsInterface(StorageInterface::class)) {
            throw new InvalidConfigException(
                $this->getInitErrorMessage('storageClass')
                . ' Класс должен реализовывать интерфейс ' . StorageInterface::class
            );
        }

        if (null !== $this->baseUrl && empty($this->baseUrl)) {
            throw new InvalidConfigException($this->getInitErrorMessage('baseUrl'));
        }

        if (empty($this->webFilesDir)) {
            throw new InvalidConfigException($this->getInitErrorMessage('webFilesDir'));
        }

        $this->storage = Yii::createObject(
            $this->storageClass,
            [
                'webFilesDir' => $this->webFilesDir,
                'baseUrl' => $this->baseUrl,
            ]
        );

        parent::init();
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @param AbstractFile $file
     * @return StorageFileInfo
     */
    public function takeStorageInfo(AbstractFile $file): StorageFileInfo
    {
        return $this->storage->take($this->getHash($file));
    }

    /**
     * @param AbstractFile $file
     * @param string $tempFilePath
     *
     * @throws FileServiceException
     * @throws StorageException
     */
    public function putToStorage(AbstractFile $file, string $tempFilePath): void
    {
        if (!file_exists($tempFilePath)) {
            throw new FileServiceException("File is not exists by path: {$tempFilePath}");
        }

        $info = (new StorageFileInfo())
            ->setRelativePath($this->getRelativePath($file))
            ->setFileName($file->system_name)
            ->setTempAbsolutePath($tempFilePath)
            ->setUploadState(false);

        $this->storage->put($info);
        $this->storage->removeFromTemp($info);

        if (!$file->save()) {
            throw new FileServiceException(
                'Cannot save file in DB' . PHP_EOL
                . 'Errors: ' . VarDumper::dumpAsString($file->getErrorSummary(true))
            );
        }
    }

    /**
     * @param AbstractFile $file
     */
    public function remove(AbstractFile $file): void
    {
        $this->storage->remove($this->getHash($file));
    }

    /**
     * @param AbstractFile $file
     * @return string
     */
    public function getHash(AbstractFile $file): string
    {
        return $this->hashService->encode($this->getRelativePath($file));
    }

    /**
     * @return ImagePreviewService
     */
    public function getImagePreviewService(): ImagePreviewService
    {
        return $this->imagePreviewService;
    }

    /**
     * @param AbstractFile $file
     * @return string
     */
    private function getRelativePath(AbstractFile $file): string
    {
        $parts = [
            $file->group,
            $file->related_entity_id,
            $file->system_name,
        ];

        return implode(DIRECTORY_SEPARATOR, array_filter($parts));
    }

    /**
     * @param string $parameter
     * @return string
     */
    private function getInitErrorMessage(string $parameter): string
    {
        return "Parameter: `{$parameter}` is not correct";
    }
}
