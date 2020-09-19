<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders;

use reactivestudio\filestorage\models\form\FileForm;
use reactivestudio\filestorage\uploaders\base\AbstractUploader;
use yii\web\UploadedFile;
use Yii;

class PostUploader extends AbstractUploader
{
    public const PARAM_UPLOAD_FILE = 'upload_file';

    /**
     * Параметр запроса для идентификатора сущности, к которой будет привязан файл
     */
    public const PARAM_ENTITY_ID = 'entity_id';

    /**
     * Параметр запроса отображаемого имени файла
     */
    public const PARAM_DISPLAY_NAME = 'display_name';

    public const PARAM_CREATED_AT = 'created_at';
    public const PARAM_UPDATED_AT = 'created_at';

    /**
     * {@inheritDoc}
     */
    public function buildForm(string $fileEntityClass, array $config = []): FileForm
    {
        $form = parent::buildForm($fileEntityClass);

        $form->uploadFile = UploadedFile::getInstance($form, static::PARAM_UPLOAD_FILE);
        $form->entityId = Yii::$app->request->post(static::PARAM_ENTITY_ID);
        $form->displayName = Yii::$app->request->post(static::PARAM_DISPLAY_NAME);
        $form->createdAt = Yii::$app->request->post(static::PARAM_CREATED_AT);
        $form->updatedAt = Yii::$app->request->post(static::PARAM_UPDATED_AT);

        return $form;
    }
}
