<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\rest\actions;

use DateTime;
use reactivestudio\filestorage\exceptions\FileUploadHttpException;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\uploaders\base\AbstractUploaderConfig;
use reactivestudio\filestorage\uploaders\dto\BaseUploaderConfig;
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
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        /** @var PostUploader $uploader */
        $uploader = Yii::createObject(PostUploader::class);
        $config = $this->buildUploaderConfig();

        try {
            $entity = $uploader->upload($config);
        } catch (UploaderException $e) {
            throw new FileUploadHttpException("File upload error: {$e->getMessage()}", 0, $e);
        }

        Yii::$app->getResponse()->setStatusCode(201);

        return $entity;
    }

    /**
     * @return AbstractUploaderConfig
     */
    private function buildUploaderConfig(): AbstractUploaderConfig
    {
        $createdAt = Yii::$app->request->post(PostUploader::PARAM_CREATED_AT);
        $createdAt = is_int($createdAt) ? (new DateTime())->setTimestamp($createdAt) : null;

        $updatedAt = Yii::$app->request->post(PostUploader::PARAM_UPDATED_AT);
        $updatedAt = is_int($updatedAt) ? (new DateTime())->setTimestamp($updatedAt) : null;


        return (new BaseUploaderConfig())
            ->setFileEntityClass($this->modelClass)
            ->setForceMode(true)
            ->setEntityId((int)Yii::$app->request->post(PostUploader::PARAM_ENTITY_ID))
            ->setDisplayName(Yii::$app->request->post(PostUploader::PARAM_ENTITY_ID))
            ->setCreatedAt($createdAt)
            ->setUpdatedAt($updatedAt);
    }
}
