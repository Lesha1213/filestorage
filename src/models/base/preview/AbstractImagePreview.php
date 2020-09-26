<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\base\preview;

use reactivestudio\filestorage\interfaces\PreviewInterface;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\models\base\AbstractImage;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * Base Image preview ActiveRecord class.
 * Table: {{%image_previews}}
 *
 * Describes Image preview information
 *
 * @property int $id Image preview identifier
 * @property int $original_file_id Original file identifier for which the preview was created
 * @property string $storage_name Storage name of the image preview
 * @property int $storage_status Storage image preview status
 * @property string $name Image preview name
 * @property string $hash Image Preview relative path and filename hash
 * @property string $preview_config Image Preview config as string
 * @property string $system_name System image preview name, including extension
 * @property int $size Image preview size in bytes
 * @property string|null $public_url Public URL for image preview download
 * @property int $created_at Date and time of creating image preview, in unix timestamp format
 * @property int $updated_at Date and time of updating image preview, in unix timestamp format
 *
 * @property-read AbstractImage $original
 *
 * @package reactivestudio\filestorage\models\base\preview
 */
abstract class AbstractImagePreview extends ActiveRecord implements PreviewInterface
{
    public const NAME_SMALL = 'small';
    public const NAME_STANDARD = 'standard';
    public const NAME_LARGE = 'large';

    abstract public static function getImageEntityClass(): string;

    /**
     * @return array
     */
    abstract public static function operations(): array;

    /**
     * @return string[]
     */
    public static function getPossibleNames(): array
    {
        return [
            static::NAME_SMALL,
            static::NAME_STANDARD,
            static::NAME_LARGE,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%image_previews}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [
                [
                    'original_file_id',
                    'storage_name',
                    'storage_status',
                    'name',
                    'hash',
                    'preview_config',
                    'system_name',
                    'size',
                ],
                'required'
            ],
            [['original_file_id', 'storage_status', 'size', 'created_at', 'updated_at'], 'integer'],
            [['storage_status'], 'in', 'range' => StorageInterface::STATUSES],
            [['storage_status'], 'default', 'value' => StorageInterface::STATUS_NOT_IN_STORAGE],
            [['storage_name', 'name', 'system_name'], 'string', 'max' => 255],
            [['hash', 'preview_config'], 'string', 'max' => 1024],
            [['name'], 'in', 'range' => static::getPossibleNames()],
            [['name'], 'unique', 'targetAttribute' => ['original_file_id', 'name']],
            [
                ['original_file_id'],
                'exist',
                'skipOnError' => false,
                'targetClass' => static::getImageEntityClass(),
                'targetAttribute' => ['original_file_id' => 'id']
            ],
            [['system_name'], 'filter', 'filter' => '\yii\helpers\Html::encode'],
            [['public_url'], 'string', 'max' => 2048],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function transactions(): array
    {
        return [
            'default' => self::OP_ALL,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function behaviors(): array
    {
        return [
            /** Working with auto timestamp fields for create and update events */
            [
                'class' => TimestampBehavior::class,
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels(): array
    {
        return [
            'id' => 'Image preview identifier',
            'original_file_id' => 'Original file identifier for which the preview was created',
            'storage_name' => 'Storage name of the image preview',
            'storage_status' => 'Storage image preview status',
            'name' => 'Image preview name',
            'hash' => 'Image Preview relative path and filename hash',
            'preview_config' => 'Image Preview config as string',
            'system_name' => 'System image preview name, including extension',
            'size' => 'Image preview size in bytes',
            'public_url' => 'Public URL for image preview download',
            'created_at' => 'Date and time of creating image preview, in unix timestamp format',
            'updated_at' => 'Date and time of updating image preview, in unix timestamp format',
        ];
    }

    /**
     * @return ActiveQuery
     */
    public function getOriginal(): ActiveQuery
    {
        return $this->hasOne(static::getImageEntityClass(), ['id' => 'original_file_id']);
    }
}
