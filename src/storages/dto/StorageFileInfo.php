<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\storages\dto;

class StorageFileInfo
{
    /**
     * @var string
     */
    private $relativePath;

    /**
     * @var string
     */
    private $fileName;

    /**
     * @var string
     */
    private $publicUrl;

    /**
     * @var bool
     */
    private $isAvailable;

    /**
     * @var string|null
     */
    private $tempAbsolutePath;

    /**
     * @var bool
     */
    private $isUploaded = false;

    /**
     * @return string
     */
    public function getRelativePath(): string
    {
        return $this->relativePath;
    }

    /**
     * @param string $relativePath
     * @return StorageFileInfo
     */
    public function setRelativePath(string $relativePath): self
    {
        $this->relativePath = $relativePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @param string $fileName
     * @return StorageFileInfo
     */
    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;
        return $this;
    }

    /**
     * @return string
     */
    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }

    /**
     * @param string $publicUrl
     * @return StorageFileInfo
     */
    public function setPublicUrl(string $publicUrl): self
    {
        $this->publicUrl = $publicUrl;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    /**
     * @param bool $isAvailable
     * @return StorageFileInfo
     */
    public function setAvailability(bool $isAvailable): self
    {
        $this->isAvailable = $isAvailable;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTempAbsolutePath(): ?string
    {
        return $this->tempAbsolutePath;
    }

    /**
     * @param string $tempAbsolutePath
     * @return StorageFileInfo
     */
    public function setTempAbsolutePath(string $tempAbsolutePath): self
    {
        $this->tempAbsolutePath = $tempAbsolutePath;
        return $this;
    }

    /**
     * @return bool
     */
    public function isUploaded(): bool
    {
        return $this->isUploaded;
    }

    /**
     * @param bool $isUploaded
     * @return StorageFileInfo
     */
    public function setUploadState(bool $isUploaded): self
    {
        $this->isUploaded = $isUploaded;
        return $this;
    }
}
