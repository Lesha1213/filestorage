<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders\dto;

use Exception;
use reactivestudio\filestorage\interfaces\UploadInfoInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use yii\helpers\ArrayHelper;

class UploadInfo implements UploadInfoInterface
{
    /**
     * @var string
     */
    private $fileEntityClass;

    /**
     * @var AbstractFile
     */
    private $fileEntity;

    /**
     * @var int
     */
    private $entityId;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @var int|null
     */
    private $createdAt;

    /**
     * @var int|null
     */
    private $updatedAt;

    /**
     * @var bool
     */
    private $isForce = false;

    /**
     * @var array
     */
    private $params = [];

    public function getFormFields(): array
    {
        return [
            'fileEntity' => $this->getFileEntity(),
            'entityId' => $this->getEntityId(),
            'displayName' => $this->getDisplayName(),
            'createdAt' => $this->getCreatedAt(),
            'updatedAt' => $this->getUpdatedAt(),
        ];
    }

    /**
     * @return string
     */
    public function getFileEntityClass(): string
    {
        return $this->fileEntityClass;
    }

    /**
     * @param string $fileEntityClass
     * @return UploadInfo
     */
    public function setFileEntityClass(string $fileEntityClass): self
    {
        $this->fileEntityClass = $fileEntityClass;
        return $this;
    }

    /**
     * @return AbstractFile
     */
    public function getFileEntity(): AbstractFile
    {
        return $this->fileEntity;
    }

    /**
     * @param AbstractFile $fileEntity
     * @return UploadInfo
     */
    public function setFileEntity(AbstractFile $fileEntity): self
    {
        $this->fileEntity = $fileEntity;
        return $this;
    }

    /**
     * @return int
     */
    public function getEntityId(): int
    {
        return $this->entityId;
    }

    /**
     * @param int|null $entityId
     * @return UploadInfo
     */
    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * @param string|null $displayName
     * @return UploadInfo
     */
    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCreatedAt(): ?int
    {
        return $this->createdAt;
    }

    /**
     * @param int|null $createdAt
     * @return UploadInfo
     */
    public function setCreatedAt(?int $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getUpdatedAt(): ?int
    {
        return $this->updatedAt;
    }

    /**
     * @param int|null $updatedAt
     * @return UploadInfo
     */
    public function setUpdatedAt(?int $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForceMode(): bool
    {
        return $this->isForce;
    }

    /**
     * @param bool $isForce
     * @return UploadInfo
     */
    public function setForceMode(bool $isForce): self
    {
        $this->isForce = $isForce;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param string $key
     * @return mixed|null
     * @throws Exception
     */
    public function getParam(string $key)
    {
        return ArrayHelper::getValue($this->params, $key, null);
    }

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return UploadInfo
     */
    public function addParam(string $key, $value): self
    {
        $this->params[$key] = $value;
        return $this;
    }
}
