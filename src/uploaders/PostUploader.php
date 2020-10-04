<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders;

use reactivestudio\filestorage\models\form\FileForm;
use reactivestudio\filestorage\uploaders\base\AbstractUploader;
use reactivestudio\filestorage\uploaders\base\AbstractUploaderConfig;
use yii\web\UploadedFile;

class PostUploader extends AbstractUploader
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(AbstractUploaderConfig $config): FileForm
    {
        $form = parent::buildForm($config);
        $form->uploadFile = UploadedFile::getInstance($form, static::PARAM_UPLOAD_FILE);

        return $form;
    }
}
