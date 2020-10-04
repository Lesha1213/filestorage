<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders\base;

use DateTimeInterface;

abstract class AbstractUploaderConfig
{
    /**
     * @var string
     */
    private $fileEntityClass;

    /**
     * @var bool
     */
    private $isForceMode = true;

    /**
     * @var int|null
     */
    private $entityId;

    /**
     * @var string|null
     */
    private $displayName;

    /**
     * @var DateTimeInterface|null
     */
    private $createdAt;

    /**
     * @var DateTimeInterface|null
     */
    private $updatedAt;

    /**
     * @return string
     */
    public function getFileEntityClass(): string
    {
        return $this->fileEntityClass;
    }

    /**
     * @param string $fileEntityClass
     * @return AbstractUploaderConfig
     */
    public function setFileEntityClass(string $fileEntityClass): self
    {
        $this->fileEntityClass = $fileEntityClass;
        return $this;
    }

    /**
     * @return bool
     */
    public function isForceMode(): bool
    {
        return $this->isForceMode;
    }

    /**
     * @param bool $isForceMode
     * @return AbstractUploaderConfig
     */
    public function setForceMode(bool $isForceMode): self
    {
        $this->isForceMode = $isForceMode;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    /**
     * @param int|null $entityId
     * @return AbstractUploaderConfig
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
     * @return AbstractUploaderConfig
     */
    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|null $createdAt
     * @return AbstractUploaderConfig
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface|null $updatedAt
     * @return AbstractUploaderConfig
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

}
