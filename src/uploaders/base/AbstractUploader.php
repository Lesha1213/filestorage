<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders\base;

use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\helpers\HashHelper;
use reactivestudio\filestorage\services\FileService;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\interfaces\UploaderInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\models\form\FileForm;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;
use Yii;

abstract class AbstractUploader implements UploaderInterface
{
    /**
     * @var FileService
     */
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    /**
     * @param string $fileEntityClass
     * @param array $config
     *
     * @return FileForm
     * @throws UploaderException
     */
    public function buildForm(string $fileEntityClass, array $config = []): FileForm
    {
        try {
            $form = Yii::createObject(FileForm::class);
        } catch (InvalidConfigException $e) {
            throw new UploaderException("Error with building form: {$e->getMessage()}", 0, $e);
        }

        $form->fileEntityClass = $fileEntityClass;
        $form->isForceMode = true;

        return $form;
    }

    /**
     * @param FileForm $form
     * @return AbstractFile
     * @throws UploaderException
     */
    public function upload(FileForm $form): AbstractFile
    {
        /** @var AbstractFile $entity */
        try {
            $entity = Yii::createObject($form->fileEntityClass);
        } catch (InvalidConfigException $e) {
            throw new UploaderException("Error with creating file entity: {$e->getMessage()}", 0, $e);
        }

        $entity->scenario = $entity::SCENARIO_UPLOAD;
        $this->fillFileEntity($entity, $form);

        if (
            !$form->isForceMode
            && $this->fileService->existsInStorage($entity->hash)
        ) {
            throw new UploaderException("File is already in storage with hash: {$entity->hash}");
        }

        $this->validateForm($form);
        $this->validateEntity($entity);

        $entity->scenario = $entity::SCENARIO_DEFAULT;

        try {
            $this->fileService->putToStorage($entity, $form->uploadFile->tempName);
        } catch (FileServiceException $e) {
            throw new UploaderException("Put to storage error: {$e->getMessage()}", 0, $e);
        }

        return $entity;
    }

    /**
     * @param AbstractFile $entity
     * @param FileForm $form
     */
    protected function fillFileEntity(AbstractFile $entity, FileForm $form): void
    {
        $entity->group = $entity::getGroupName();
        $entity->related_entity_id = $form->entityId;

        $entity->original_name = $form->uploadFile->baseName;
        $entity->original_extension = $form->uploadFile->extension;
        $entity->system_name = uniqid('', true) . '.' . $entity->original_extension;
        $entity->display_name = $form->displayName;

        $entity->hash = HashHelper::encode($entity->getRelativePath(), $entity->system_name);

        $entity->mime = $form->uploadFile->type;
        $entity->size = $form->uploadFile->size;

        if (null !== $form->createdAt) {
            $entity->created_at = $form->createdAt;
        }

        if (null !== $form->updatedAt) {
            $entity->updated_at = $form->updatedAt;
        }
    }

    /**
     * @param FileForm $form
     * @throws UploaderException
     */
    protected function validateForm(FileForm $form): void
    {
        if (!$form->validate()) {
            throw new UploaderException(
                'Uploader form validation errors: '
                . VarDumper::dumpAsString($form->getErrorSummary(true))
            );
        }
    }

    /**
     * @param AbstractFile $entity
     * @throws UploaderException
     */
    protected function validateEntity(AbstractFile $entity): void
    {
        if (!$entity->validate()) {
            throw new UploaderException(
                'Uploader file entity validation errors: ' . PHP_EOL
                . VarDumper::dumpAsString($entity->getErrorSummary(true))
            );
        }
    }
}
