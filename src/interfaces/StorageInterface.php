<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\storages\dto\StorageFileInfo;
use reactivestudio\filestorage\exceptions\StorageException;

/**
 * Интерфейс хранилища файлов
 *
 * @package reactivestudio\filestorage\models\components\interfaces
 */
interface StorageInterface
{
    public const STATUS_NOT_IN_STORAGE = 0;
    public const STATUS_IN_STORAGE = 1;
    public const STATUS_NEED_TO_REMOVE = 2;

    public const STATUSES = [
        self::STATUS_NOT_IN_STORAGE,
        self::STATUS_IN_STORAGE,
        self::STATUS_NEED_TO_REMOVE,
    ];

    public function getName(): string;

    public function isExists(string $hash): bool;

    public function take(string $hash): StorageFileInfo;

    /**
     * @param StorageFileInfo $storageFileInfo
     * @throws StorageException
     */
    public function put(StorageFileInfo $storageFileInfo): void;

    public function remove(string $hash): void;

    public function copyToTemp(StorageFileInfo $storageFileInfo): void;

    public function removeFromTemp(StorageFileInfo $storageFileInfo): void;
}
