<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders\base;

use reactivestudio\filestorage\components\FileService;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\interfaces\UploaderInterface;
use reactivestudio\filestorage\interfaces\UploadInfoInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\models\forms\FileForm;
use reactivestudio\filestorage\storages\dto\StorageFileInfo;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;
use Yii;

abstract class AbstractUploader implements UploaderInterface
{
    /**
     * @var FileService
     */
    protected $fileService;

    /**
     * @var FileStrategyInterface|null
     */
    protected $fileStrategy;

    public function __construct(
        FileService $fileService,
        ?FileStrategyInterface $strategy = null
    ) {
        $this->fileService = $fileService;
        $this->fileStrategy = $strategy;
    }

    /**
     * @param FileStrategyInterface $strategy
     * @return AbstractUploader
     */
    public function setFileTypeStrategy(FileStrategyInterface $strategy): self
    {
        $this->fileStrategy = $strategy;
        return $this;
    }

    /**
     * @param UploadInfoInterface $uploadInfo
     *
     * @return AbstractFile
     *
     * @throws InvalidConfigException
     * @throws StorageException
     * @throws UploaderException
     */
    public function upload(UploadInfoInterface $uploadInfo): AbstractFile
    {
        /** @var AbstractFile $fileEntity */
        $fileEntity = Yii::createObject($uploadInfo->getFileEntityClass());
        $uploadInfo->setFileEntity($fileEntity);

        $form = $this->buildForm($uploadInfo);
        $this->fillFileEntity($fileEntity, $form);

        $this->validate($uploadInfo, $form, $fileEntity);

        $storageFileInfo = (new StorageFileInfo())
            ->setTempAbsolutePath($form->uploadFile->tempName)
            ->setFileName($fileEntity->system_name)
            ->setUploadState(true);

        $this->fileService->getStorage()->put($storageFileInfo);
        $this->fileService->getStorage()->removeFromTemp($storageFileInfo);

        if (!$fileEntity->save()) {
            throw new UploaderException("File entity save error");
        }

        $this->fileStrategy->doAfterUpload($fileEntity);

        return $fileEntity;
    }

    /**
     * @param UploadInfoInterface $uploadInfo
     *
     * @return FileForm
     * @throws InvalidConfigException
     */
    protected function buildForm(UploadInfoInterface $uploadInfo): FileForm
    {
        $form = Yii::createObject(FileForm::class);
        $form->load($uploadInfo->getFormFields(), '');

        return $form;
    }

    /**
     * @param AbstractFile $fileEntity
     * @param FileForm $form
     */
    protected function fillFileEntity(AbstractFile $fileEntity, FileForm $form): void
    {
        $fileEntity->group = $fileEntity::getGroupName();
        $fileEntity->related_entity_id = $form->entityId;

        $fileEntity->hash = $this->fileService->getHash($fileEntity);

        $fileEntity->original_name = $form->uploadFile->baseName;
        $fileEntity->original_extension = $form->uploadFile->extension;
        $fileEntity->system_name = uniqid('', true) . '.' . $fileEntity->original_extension;
        $fileEntity->display_name = $form->displayName;

        $fileEntity->mime = $form->uploadFile->type;
        $fileEntity->size = $form->uploadFile->size;

        if (null !== $form->createdAt) {
            $fileEntity->created_at = $form->createdAt;
        }

        if (null !== $form->updatedAt) {
            $fileEntity->updated_at = $form->updatedAt;
        }
    }

    /**
     * @param UploadInfoInterface $uploadInfo
     * @param FileForm $form
     * @param AbstractFile $fileEntity
     *
     * @throws UploaderException
     */
    protected function validate(UploadInfoInterface $uploadInfo, FileForm $form, AbstractFile $fileEntity): void
    {
        if (
            !$uploadInfo->isForceMode()
            && $this->fileService->getStorage()->isExists($fileEntity->hash)
        ) {
            throw new UploaderException("File is already in storage with hash: {$fileEntity->hash}");
        }

        if (!$form->validate()) {
            throw new UploaderException(
                'Uploader form validation errors: '
                . VarDumper::dumpAsString($form->getErrorSummary(true))
            );
        }

        if (!$fileEntity->validate()) {
            throw new UploaderException(
                "Uploader file entity: '{$uploadInfo->getFileEntityClass()}' validation errors: "
                . VarDumper::dumpAsString($fileEntity->getErrorSummary(true))
            );
        }
    }
}
