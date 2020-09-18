<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\rest\actions;

use reactivestudio\filestorage\components\FileService;
use reactivestudio\filestorage\exceptions\FileUploadHttpException;
use reactivestudio\filestorage\exceptions\StorageException;
use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\uploaders\dto\UploadInfo;
use reactivestudio\filestorage\uploaders\PostUploader;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\rest\Controller;
use yii\rest\CreateAction;
use Yii;

class FileCreateAction extends CreateAction
{
    /**
     * @var string|null
     */
    public $fileStrategy;

    /**
     * @var FileService
     */
    private $fileService;

    public function __construct(
        string $id,
        Controller $controller,
        FileService $fileService,
        array $config = []
    ) {
        parent::__construct($id, $controller, $config);

        $this->fileService = $fileService;
    }

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

        /** @var FileStrategyInterface|null $strategy */
        $strategy = null !== $this->fileStrategy
            ? Yii::createObject($this->fileStrategy, [$this->fileService])
            : null;

        /** @var PostUploader $uploader */
        $uploader = Yii::createObject(PostUploader::class, [$this->fileService, $strategy]);

        /** @var UploadInfo $uploadInfo */
        $uploadInfo = Yii::createObject(UploadInfo::class);

        $uploadInfo->setFileEntityClass($this->modelClass);

        try {
            $fileEntity = $uploader->upload($uploadInfo);
        } catch (StorageException | UploaderException $e) {
            throw new FileUploadHttpException("File upload error: {$e->getMessage()}", 0, $e);
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(201);

        $redirectUrl = Url::toRoute([$this->viewAction, 'id' => $fileEntity->id], true);
        $response->getHeaders()->set('Location', $redirectUrl);
    }
}
