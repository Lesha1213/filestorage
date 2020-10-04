<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders;

use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\models\form\FileForm;
use reactivestudio\filestorage\uploaders\base\AbstractUploader;
use reactivestudio\filestorage\uploaders\base\AbstractUploaderConfig;
use reactivestudio\filestorage\uploaders\dto\RemoteUploaderConfig;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use Yii;

class RemoteUploader extends AbstractUploader
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(AbstractUploaderConfig $config): FileForm
    {
        /** @var RemoteUploaderConfig $config */
        $form = parent::buildForm($config);
        $form->uploadFile = $this->buildUploadedFile($config->getUrlToFile());

        return $form;
    }

    /**
     * @param string $url
     * @return UploadedFile
     * @throws UploaderException
     */
    private function buildUploadedFile(string $url): UploadedFile
    {
        try {
            $tempFilePath = StorageHelper::downloadFile($url);
        } catch (StorageException $e) {
            throw new UploaderException("Download file error: {$e->getMessage()}", 0, $e);
        }

        try {
            $uploadFile = Yii::createObject(UploadedFile::class);
        } catch (InvalidConfigException $e) {
            throw new UploaderException("Error with creating UploadFile: {$e->getMessage()}", 0, $e);
        }

        $uploadFile->tempName = $tempFilePath;
        $uploadFile->name = basename($url);
        $uploadFile->size = filesize($tempFilePath);
        $uploadFile->type = FileHelper::getMimeTypeByExtension($uploadFile->name);
        $uploadFile->error = UPLOAD_ERR_OK;

        return $uploadFile;
    }
}
