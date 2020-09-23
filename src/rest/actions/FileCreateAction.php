<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\rest\actions;

use reactivestudio\filestorage\exceptions\FileUploadHttpException;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\uploaders\PostUploader;
use yii\base\InvalidConfigException;
use yii\rest\CreateAction;
use Yii;

class FileCreateAction extends CreateAction
{
    /**
     * {@inheritdoc}
     * @throws FileUploadHttpException
     * @throws InvalidConfigException
     * @throws UploaderException
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /** @var PostUploader $uploader */
        $uploader = Yii::createObject(PostUploader::class);

        $form = $uploader->buildForm($this->modelClass);

        try {
            $entity = $uploader->upload($form);
        } catch (UploaderException $e) {
            throw new FileUploadHttpException("File upload error: {$e->getMessage()}", 0, $e);
        }

        Yii::$app->getResponse()->setStatusCode(201);

        return $entity;
    }
}
