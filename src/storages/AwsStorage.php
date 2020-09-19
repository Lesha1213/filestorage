<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages;

use frostealth\yii2\aws\s3\interfaces\Service as S3ServiceInterface;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\storages\base\AbstractStorage;
use reactivestudio\filestorage\storages\dto\StorageFileInfo;
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

    public function put(StorageFileInfo $storageFileInfo): void
    {
        $relatedFilePath = $storageFileInfo->getRelativePath() . DIRECTORY_SEPARATOR . $storageFileInfo->getFileName();
        $hash = HashHelper::encode($storageFileInfo->getRelativePath(), $storageFileInfo->getFileName());

        if ($this->isExists($hash)) {
            $this->remove($hash);
        }

        $result = $this->s3Service
            ->commands()
            ->upload($relatedFilePath, $storageFileInfo->getTempAbsolutePath())
            ->withCacheControl()
            ->execute();

        if (!$result) {
            throw new StorageException(
                "File put in storage error for temp path: {$storageFileInfo->getTempAbsolutePath()}"
            );
        }
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

        $isRemoved = $this->s3Service
            ->commands()
            ->delete(HashHelper::decode($hash))
            ->execute();

        if (!$isRemoved) {
            throw new StorageException("File is not removed from storage with hash: {$hash}");
        }
    }

    /**
     * @param StorageFileInfo $storageFileInfo
     * @throws StorageException
     */
    public function copyToTemp(StorageFileInfo $storageFileInfo): void
    {
        $destination = $this->getTempDir() . DIRECTORY_SEPARATOR . $storageFileInfo->getFileName();
        $destination = FileHelper::normalizePath(Yii::getAlias($destination));

        $tempFilePath = StorageHelper::downloadFile($storageFileInfo->getPublicUrl());

        StorageHelper::copy($tempFilePath, $destination);
        StorageHelper::deleteFile($tempFilePath);

        $storageFileInfo->setTempAbsolutePath($destination);
    }

    protected function getPublicUrl(string $hash): string
    {
        return $this->s3Service
            ->commands()
            ->getUrl(HashHelper::decode($hash))
            ->execute();
    }
}
