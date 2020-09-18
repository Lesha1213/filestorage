<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders;

use reactivestudio\filestorage\interfaces\UploadInfoInterface;
use reactivestudio\filestorage\models\forms\FileForm;
use reactivestudio\filestorage\uploaders\base\AbstractUploader;
use reactivestudio\filestorage\uploaders\dto\UploadInfo;
use yii\base\InvalidConfigException;
use yii\web\UploadedFile;
use Yii;

class PostUploader extends AbstractUploader
{
    public const PARAM_UPLOAD_FILE = 'upload_file';

    /**
     * TODO
     * Параметр запроса для идентификатора сущности, к которой будет привязан файл
     */
    public const PARAM_ENTITY_ID = 'entity_id';

    /**
     * TODO
     * Параметр запроса отображаемого имени файла
     */
    public const PARAM_DISPLAY_NAME = 'display_name';

    public const PARAM_CREATED_AT = 'created_at';
    public const PARAM_UPDATED_AT = 'created_at';

    /**
     * @param UploadInfoInterface $uploadInfo
     *
     * @return FileForm
     * @throws InvalidConfigException
     */
    protected function buildForm(UploadInfoInterface $uploadInfo): FileForm
    {
        /** @var UploadInfo $uploadInfo */
        $uploadInfo
            ->setEntityId(Yii::$app->request->post(static::PARAM_ENTITY_ID))
            ->setDisplayName(Yii::$app->request->post(static::PARAM_DISPLAY_NAME))
            ->setCreatedAt(Yii::$app->request->post(static::PARAM_CREATED_AT))
            ->setUpdatedAt(Yii::$app->request->post(static::PARAM_UPDATED_AT));

        $form = parent::buildForm($uploadInfo);

        $form->uploadFile = UploadedFile::getInstance($form, static::PARAM_UPLOAD_FILE);

        return $form;
    }
}
