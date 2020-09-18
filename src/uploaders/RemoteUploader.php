<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders;

use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\helpers\StorageHelper;
use reactivestudio\filestorage\interfaces\UploadInfoInterface;
use reactivestudio\filestorage\models\forms\FileForm;
use reactivestudio\filestorage\uploaders\base\AbstractUploader;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use Yii;

class RemoteUploader extends AbstractUploader
{
    /**
     * @param UploadInfoInterface $uploadInfo
     *
     * @return FileForm
     *
     * @throws InvalidConfigException
     * @throws UploaderException
     */
    protected function buildForm(UploadInfoInterface $uploadInfo): FileForm
    {
        $form = parent::buildForm($uploadInfo);

        $url = $uploadInfo->getParam('url');
        $form->uploadFile = $this->buildUploadedFile($url);

        return $form;
    }

    /**
     * @param string $url
     *
     * @return UploadedFile
     *
     * @throws InvalidConfigException
     * @throws UploaderException
     */
    private function buildUploadedFile(string $url): UploadedFile
    {
        try {
            $tempFilePath = StorageHelper::downloadFile($url);
        } catch (StorageException $e) {
            throw new UploaderException("Download file error: {$e->getMessage()}", 0, $e);
        }

        $uploadFile = Yii::createObject(UploadedFile::class);
        $uploadFile->tempName = $tempFilePath;
        $uploadFile->name = basename($url);
        $uploadFile->size = filesize($tempFilePath);
        $uploadFile->type = FileHelper::getMimeTypeByExtension($uploadFile->name);
        $uploadFile->error = UPLOAD_ERR_OK;

        return $uploadFile;
    }
}
