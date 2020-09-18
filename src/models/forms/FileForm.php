<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\forms;

use reactivestudio\filestorage\models\base\AbstractFile;
use yii\base\Model;
use yii\web\UploadedFile;

use function get_class;

class FileForm extends Model
{
    /**
     * @var AbstractFile
     */
    public $fileEntity;

    /**
     * @var UploadedFile
     */
    public $uploadFile;

    /**
     * @var int|null
     */
    public $entityId;

    /**
     * @var string|null
     */
    public $displayName;

    /**
     * @var int|null
     */
    public $createdAt;

    /**
     * @var int|null
     */
    public $updatedAt;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [
                [
                    'uploadFile',
                    'fileEntityClass'
                ],
                'required'
            ],
            [
                ['entityId'],
                'exist',
                'skipOnError' => true,
                'targetClass' => get_class($this->fileEntity),
                'targetAttribute' => ['entityId' => implode(',', $this->fileEntity::primaryKey())]
            ],
            ['displayName', 'string', 'max' => 255],
            [['createdAt', 'updatedAt'], 'integer'],
            [
                ['uploadFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => $this->fileEntity::getAllowedExtensions(),
                'checkExtensionByMimeType' => false,
            ],
        ];
    }
}
