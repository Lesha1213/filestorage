<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\models\base\AbstractFile;

interface UploadInfoInterface
{
    /**
     * @return array
     */
    public function getFormFields(): array;

    /**
     * @return string
     */
    public function getFileEntityClass(): string;

    /**
     * @param string $fileEntityClass
     */
    public function setFileEntityClass(string $fileEntityClass);

    /**
     * @return AbstractFile
     */
    public function getFileEntity(): AbstractFile;

    /**
     * @param AbstractFile $fileEntity
     */
    public function setFileEntity(AbstractFile $fileEntity);

    /**
     * @return int
     */
    public function getEntityId(): int;

    /**
     * @param int|null $entityId
     */
    public function setEntityId(?int $entityId);

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string;

    /**
     * @param string|null $displayName
     */
    public function setDisplayName(?string $displayName);

    /**
     * @return int|null
     */
    public function getCreatedAt(): ?int;

    /**
     * @param int|null $createdAt
     */
    public function setCreatedAt(?int $createdAt);

    /**
     * @return int|null
     */
    public function getUpdatedAt(): ?int;

    /**
     * @param int|null $updatedAt
     */
    public function setUpdatedAt(?int $updatedAt);

    /**
     * @return bool
     */
    public function isForceMode(): bool;

    /**
     * @param bool $isForce
     */
    public function setForceMode(bool $isForce);

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getParam(string $key);

    /**
     * @param string $key
     * @param mixed $value
     */
    public function addParam(string $key, $value);
}
