<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages;

use frostealth\yii2\aws\s3\interfaces\Service as S3ServiceInterface;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\storages\base\AbstractStorage;
use reactivestudio\filestorage\storages\dto\StorageObject;
use yii\helpers\FileHelper;
use Yii;

class AwsStorage extends AbstractStorage
{
    /**
     * @var S3ServiceInterface
     */
    private $s3Service;

    /**
     * @param S3ServiceInterface $s3Service
     * {@inheritDoc}
     */
    public function __construct(S3ServiceInterface $s3Service, string $webFilesDir)
    {
        parent::__construct($webFilesDir);

        $this->s3Service = $s3Service;
    }

    public function getName(): string
    {
        return 's3';
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function isExists(string $hash): bool
    {
        return $this->s3Service
            ->commands()
            ->exist(HashHelper::decode($hash))
            ->execute();
    }

    /**
     * @param StorageObject $storageObject
     * @throws StorageException
     */
    public function copyFromStorageToTemp(StorageObject $storageObject): void
    {
        $destination = $this->getTempDir() . DIRECTORY_SEPARATOR . $storageObject->getFileName();
        $destination = FileHelper::normalizePath(Yii::getAlias($destination));

        $tempFilePath = StorageHelper::downloadFile($storageObject->getPublicUrl());

        StorageHelper::copy($tempFilePath, $destination);
        StorageHelper::deleteFile($tempFilePath);

        $storageObject->setTempAbsolutePath($destination);
    }

    /**
     * @param string $tempPath
     * @param string $destination
     * @return bool
     */
    protected function copyFromTempToStorage(string $tempPath, string $destination): bool
    {
        return $this->s3Service
            ->commands()
            ->upload($destination, $tempPath)
            ->execute();
    }

    /**
     * @param StorageObject $storageObject
     * @return string
     */
    protected function buildFileDestination(StorageObject $storageObject): string
    {
        return $storageObject->getRelativePath() . DIRECTORY_SEPARATOR . $storageObject->getFileName();
    }

    /**
     * @param string $hash
     * @return bool
     */
    protected function removeFromStorage(string $hash): bool
    {
        return $this->s3Service
            ->commands()
            ->delete(HashHelper::decode($hash))
            ->execute();
    }

    protected function buildPublicUrl(string $hash): string
    {
        return $this->s3Service
            ->commands()
            ->getUrl(HashHelper::decode($hash))
            ->execute();
    }
}
