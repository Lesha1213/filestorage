<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\rest\actions;

use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\exceptions\FileStrategyException;
use reactivestudio\filestorage\services\FileService;
use reactivestudio\filestorage\models\base\AbstractFile;
use yii\rest\Controller;
use yii\rest\DeleteAction;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use Yii;

/**
 * @method AbstractFile findModel($id)
 * @package reactivestudio\filestorage\rest\actions
 */
class FileDeleteAction extends DeleteAction
{
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
     */
    public function run($id): void
    {
        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            throw new ServerErrorHttpException('File does not exists.', 0, $e);
        }

        $this->callCheckAccessIfNeeded();

        try {
            $this->fileService->removeFromStorage($model);
        } catch (FileServiceException | FileStrategyException $e) {
            throw new ServerErrorHttpException(
                "Failed to delete the object. Error: {$e->getMessage()}",
                0,
                $e
            );
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }

    protected function callCheckAccessIfNeeded(): void
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }
    }
}
