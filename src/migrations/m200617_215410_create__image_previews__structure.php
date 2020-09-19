<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Migration to create a table structure {{%image_previews}}
 * @package reactivestudio\filestorage\migrations
 */
class m200617_215410_create__image_previews__structure extends Migration
{
    /**
     * Table name
     * @var string
     */
    private $table = '{{%image_previews}}';

    /**
     * Base encoding
     * @var string
     */
    private $character = 'utf8mb4';

    /**
     * Collate
     * @var string
     */
    private $collate = 'utf8mb4_unicode_ci';

    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        /** Create table */
        $this->createTable(
            $this->table,
            [
                'id' => $this
                    ->integer()
                    ->notNull()
                    ->append('AUTO_INCREMENT PRIMARY KEY')
                    ->comment('Image preview identifier'),
                'original_file_id' => $this
                    ->integer()
                    ->notNull()
                    ->comment('Original file identifier for which the preview was created'),
                'storage_name' => $this
                    ->string(255)
                    ->notNull()
                    ->comment('Storage name of the image preview'),
                'storage_status' => $this
                    ->tinyInteger(3)
                    ->notNull()
                    ->defaultValue(0)
                    ->comment('Storage image preview status'),
                'name' => $this
                    ->string(255)
                    ->notNull()
                    ->comment('Image preview name'),
                'hash' => $this
                    ->string(255)
                    ->notNull()
                    ->comment('Image Preview relative path and filename hash'),
                'system_name' => $this
                    ->string(255)
                    ->notNull()
                    ->unique()
                    ->comment('System image preview name, including extension'),
                'size' => $this
                    ->integer()
                    ->notNull()
                    ->unsigned()
                    ->defaultValue(0)
                    ->comment('Image preview size in bytes'),
                'public_url' => $this
                    ->string(2048)
                    ->null()
                    ->comment('Public URL for image preview download'),
                'created_at' => $this
                    ->integer()
                    ->notNull()
                    ->comment('Date and time of creating image preview, in unix timestamp format'),
                'updated_at' => $this
                    ->integer()
                    ->notNull()
                    ->comment('Date and time of updating image preview, in unix timestamp format'),
            ],
            $this->options()
        );

        /** Add comment to table */
        $this->addCommentOnTable($this->table, 'Image previews');

        /** Add index for the field `name` */
        $this->createIndex('{{%idx__' . 'image_previews' . '__name}}', $this->table, 'name');

        /** Add index for the field `hash` */
        $this->createIndex('{{%idx__' . 'image_previews' . '__hash}}', $this->table, 'hash');

        /** Add index for the field `created_at` */
        $this->createIndex('{{%idx__' . 'image_previews' . '__created_at}}', $this->table, 'created_at');

        /** Add index for the field `updated_at` */
        $this->createIndex('{{%idx__' . 'image_previews' . '__updated_at}}', $this->table, 'updated_at');

        /** Add unique index for the fields `original_file_id`, `name` */
        $this->createIndex(
            '{{%unq__' . 'image_previews' . '__file_id_name}}',
            $this->table,
            ['original_file_id', 'name'],
            true
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        $this->dropTable($this->table);
    }

    /**
     * Get table options
     * @return string|null
     */
    protected function options(): ?string
    {
        return ('mysql' === $this->db->driverName)
            ? 'CHARACTER SET ' . $this->character . ' COLLATE ' . $this->collate . ' ENGINE=InnoDB'
            : null;
    }
}
