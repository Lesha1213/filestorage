<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\models\base;

use reactivestudio\filestorage\interfaces\FileQueryInterface;
use reactivestudio\filestorage\interfaces\FileTypeInterface;
use reactivestudio\filestorage\interfaces\StorageInterface;
use reactivestudio\filestorage\models\query\FileQuery;
use yii\base\InvalidConfigException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * Base File ActiveRecord class.
 * Table: {{%files}}
 *
 * Describes uploaded file information
 *
 * @property int $id File identifier
 * @property string $storage_name Storage name of the file
 * @property int $storage_status Storage file status
 * @property string $group Group name of files
 * @property int|null $related_entity_id Related entity identifier
 * @property string $hash File relative path and filename hash
 * @property string $original_name Original file name
 * @property string $original_extension Original file extension
 * @property string $system_name System file name, including extension
 * @property string|null $display_name Display file name, including extension
 * @property string $mime MIME-type of the file
 * @property int $size File size in bytes
 * @property string|null $public_url Public URL for file download
 * @property int $created_at Date and time of creating file, in unix timestamp format
 * @property int $updated_at Date and time of updating file, in unix timestamp format
 *
 * @property-read FileTypeInterface $type
 * @property-read string $relativePath
 * @property-read string $originalFullName Original file name with extension
 *
 * @package reactivestudio\filestorage\models\base
 */
abstract class AbstractFile extends ActiveRecord
{
    public const SCENARIO_UPLOAD = 'upload';

    /**
     * Group separator
     */
    public const GROUP_SEPARATOR = '__';

    /**
     * Get related entity name. Used only for grouping
     * @return string
     */
    abstract public static function getRelatedEntityName(): string;

    /**
     * Get file entity name. Used only for grouping
     * @return string
     */
    abstract public static function getFileEntityName(): string;

    /**
     * Get the list of all allowed file extension in lowercase
     * @return string[]
     */
    abstract public static function getAllowedExtensions(): array;

    /**
     * Get group name
     * @return string
     */
    public static function getGroupName(): string
    {
        $groupName = static::getRelatedEntityName();
        $groupName .= empty(static::getFileEntityName()) ? '' : static::GROUP_SEPARATOR . static::getFileEntityName();

        return $groupName;
    }

    /**
     * @return FileQueryInterface
     * @throws InvalidConfigException
     */
    public static function find(): FileQueryInterface
    {
        /** @var FileQuery $query */
        $query = Yii::createObject(FileQuery::class, [static::class]);
        return $query->entityGroup(static::getGroupName());
    }

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%files}}';
    }

    public function scenarios(): array
    {
        $scenarios = [
            static::SCENARIO_UPLOAD => [
                'group',
                'related_entity_id',
                'hash',
                'original_name',
                'original_extension',
                'system_name',
                'display_name',
                'mime',
                'size',
                'created_at',
                'updated_at',
            ],
        ];

        return ArrayHelper::merge(parent::scenarios(), $scenarios);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [
                [
                    'storage_name',
                    'storage_status',
                    'group',
                    'hash',
                    'original_name',
                    'original_extension',
                    'system_name',
                    'mime',
                    'size',
                ],
                'required'
            ],
            [['related_entity_id', 'storage_status', 'size', 'created_at', 'updated_at'], 'integer'],
            [['storage_status'], 'in', 'range' => StorageInterface::STATUSES],
            [['storage_status'], 'default', 'value' => StorageInterface::STATUS_NOT_IN_STORAGE],
            [['storage_name', 'original_name', 'system_name', 'display_name', 'mime'], 'string', 'max' => 255],
            ['hash', 'string', 'max' => 1024],
            [['group'], 'string', 'max' => 100],
            [['group'], 'in', 'range' => [static::getGroupName()]],
            [['original_extension'], 'string', 'max' => 16],
            [['system_name'], 'unique'],
            [
                [
                    'group',
                    'original_name',
                    'original_extension',
                    'system_name',
                    'display_name',
                    'mime',
                ],
                'filter',
                'filter' => '\yii\helpers\Html::encode'
            ],
            [['original_extension'], 'in', 'range' => static::getAllowedExtensions(), 'skipOnEmpty' => false],
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
     * {@inheritdoc}
     * @return array
     */
    public function fields(): array
    {
        $fields = [
            'originalFullName',
        ];

        return ArrayHelper::merge(parent::fields(), $fields);
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
            'id' => 'File identifier',
            'storage_name' => 'Storage name of the file',
            'storage_status' => 'Storage file status',
            'group' => 'Group name of files',
            'related_entity_id' => 'Related entity identifier',
            'hash' => 'File relative path and filename hash',
            'original_name' => 'Original file name',
            'original_extension' => 'Original file extension',
            'system_name' => 'System file name, including extension',
            'display_name' => 'Display file name, including extension',
            'mime' => 'MIME-type of the file',
            'size' => 'File size in bytes',
            'public_url' => 'Public URL for file download',
            'created_at' => 'Date and time of creating file, in unix timestamp format',
            'updated_at' => 'Date and time of updating file, in unix timestamp format',
            'originalFullName' => 'Original file name with extension',
        ];
    }

    /**
     * Get original file name with extension
     * @return string
     */
    public function getOriginalFullName(): string
    {
        return $this->original_name . '.' . $this->original_extension;
    }

    /**
     * @return string
     */
    public function getRelativePath(): string
    {
        $parts = [
            $this::getGroupName(),
            $this->related_entity_id,
        ];

        return implode(DIRECTORY_SEPARATOR, array_filter($parts));
    }
}
