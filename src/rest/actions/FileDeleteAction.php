<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\rest\actions;

use reactivestudio\filestorage\components\FileService;
use reactivestudio\filestorage\exceptions\FileServiceException;
use reactivestudio\filestorage\interfaces\FileStrategyInterface;
use reactivestudio\filestorage\models\base\AbstractFile;
use yii\base\InvalidConfigException;
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
     * @throws InvalidConfigException
     */
    public function run($id): void
    {
        try {
            $model = $this->findModel($id);
        } catch (NotFoundHttpException $e) {
            throw new ServerErrorHttpException('File does not exists.', 0, $e);
        }

        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        if (null === $this->fileStrategy) {
            throw new ServerErrorHttpException('File Strategy must be defined');
        }

        /** @var FileStrategyInterface $strategy */
        $strategy = Yii::createObject($this->fileStrategy, [$this->fileService]);

        try {
            $strategy->delete($model);
        } catch (FileServiceException $e) {
            throw new ServerErrorHttpException(
                "Failed to delete the object. Error: {$e->getMessage()}",
                0,
                $e
            );
        }

        Yii::$app->getResponse()->setStatusCode(204);
    }
}
