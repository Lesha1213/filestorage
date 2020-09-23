<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\form;

use reactivestudio\filestorage\models\base\AbstractFile;
use yii\base\Model;
use yii\web\UploadedFile;

class FileForm extends Model
{
    /**
     * @var AbstractFile
     */
    public $fileEntityClass;

    /**
     * @var bool
     */
    public $isForceMode = false;

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
            ['displayName', 'string', 'max' => 255],
            [['entityId', 'createdAt', 'updatedAt'], 'integer'],
            ['isForceMode', 'boolean'],
            [
                ['uploadFile'],
                'file',
                'skipOnEmpty' => false,
                'extensions' => $this->fileEntityClass::getAllowedExtensions(),
                'checkExtensionByMimeType' => false,
            ],
        ];
    }
}
