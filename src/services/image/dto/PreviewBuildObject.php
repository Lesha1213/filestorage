<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\services\image\dto;

use Intervention\Image\Image;
use reactivestudio\filestorage\interfaces\OperationInterface;
use reactivestudio\filestorage\models\base\AbstractImage;
use reactivestudio\filestorage\storages\dto\StorageObject;

class PreviewBuildObject
{
    /**
     * @var AbstractImage
     */
    private $originalImage;

    /**
     * @var string
     */
    private $originalTempAbsolutePath;

    /**
     * @var string
     */
    private $previewName;

    /**
     * @var Image
     */
    private $interventionImage;

    /**
     * @var int
     */
    private $size;

    /**
     * @var OperationInterface
     */
    private $operation;

    /**
     * @var StorageObject
     */
    private $storageObject;

    /**
     * @return AbstractImage
     */
    public function getOriginalImage(): AbstractImage
    {
        return $this->originalImage;
    }

    /**
     * @param AbstractImage $originalImage
     * @return PreviewBuildObject
     */
    public function setOriginalImage(AbstractImage $originalImage): self
    {
        $this->originalImage = $originalImage;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalTempAbsolutePath(): string
    {
        return $this->originalTempAbsolutePath;
    }

    /**
     * @param string $originalTempAbsolutePath
     * @return PreviewBuildObject
     */
    public function setOriginalTempAbsolutePath(string $originalTempAbsolutePath): self
    {
        $this->originalTempAbsolutePath = $originalTempAbsolutePath;
        return $this;
    }

    /**
     * @return string
     */
    public function getPreviewName(): string
    {
        return $this->previewName;
    }

    /**
     * @param string $previewName
     * @return PreviewBuildObject
     */
    public function setPreviewName(string $previewName): self
    {
        $this->previewName = $previewName;
        return $this;
    }

    /**
     * @return Image
     */
    public function getInterventionImage(): Image
    {
        return $this->interventionImage;
    }

    /**
     * @param Image $interventionImage
     * @return PreviewBuildObject
     */
    public function setInterventionImage(Image $interventionImage): self
    {
        $this->interventionImage = $interventionImage;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return PreviewBuildObject
     */
    public function setSize(int $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return OperationInterface
     */
    public function getOperation(): OperationInterface
    {
        return $this->operation;
    }

    /**
     * @param OperationInterface $operation
     * @return PreviewBuildObject
     */
    public function setOperation(OperationInterface $operation): self
    {
        $this->operation = $operation;
        return $this;
    }

    /**
     * @return StorageObject
     */
    public function getStorageObject(): StorageObject
    {
        return $this->storageObject;
    }

    /**
     * @param StorageObject $storageObject
     * @return PreviewBuildObject
     */
    public function setStorageObject(StorageObject $storageObject): self
    {
        $this->storageObject = $storageObject;
        return $this;
    }
}
