<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Migration to create a table structure {{%files}}
 * @package reactivestudio\filestorage\migrations
 */
class m200617_215405_create__files__structure extends Migration
{
    /**
     * Table name
     * @var string
     */
    private $table = '{{%files}}';

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
                    ->comment('File identifier'),
                'storage_name' => $this
                    ->string(255)
                    ->notNull()
                    ->comment('Storage name of the file'),
                'storage_status' => $this
                    ->tinyInteger(3)
                    ->notNull()
                    ->defaultValue(0)
                    ->comment('Storage file status'),
                'group' => $this
                    ->string(100)
                    ->notNull()
                    ->comment('Group name of files'),
                'related_entity_id' => $this
                    ->integer()
                    ->null()
                    ->comment('Related entity identifier'),
                'hash' => $this
                    ->string(512)
                    ->notNull()
                    ->comment('File relative path and filename hash'),
                'original_name' => $this
                    ->string(255)
                    ->notNull()
                    ->comment('Original file name'),
                'original_extension' => $this
                    ->string(16)
                    ->notNull()
                    ->comment('Original file extension'),
                'system_name' => $this
                    ->string(255)
                    ->notNull()
                    ->unique()
                    ->comment('System file name, including extension'),
                'display_name' => $this
                    ->string(255)
                    ->null()
                    ->comment('Display file name, including extension'),
                'mime' => $this
                    ->string(255)
                    ->notNull()
                    ->comment('MIME-type of the file'),
                'size' => $this
                    ->integer()
                    ->notNull()
                    ->unsigned()
                    ->defaultValue(0)
                    ->comment('File size in bytes'),
                'public_url' => $this
                    ->string(2048)
                    ->null()
                    ->comment('Public URL for file download'),
                'created_at' => $this
                    ->integer()
                    ->notNull()
                    ->comment('Date and time of creating file, in unix timestamp format'),
                'updated_at' => $this
                    ->integer()
                    ->notNull()
                    ->comment('Date and time of updating file, in unix timestamp format'),
            ],
            $this->options()
        );

        /** Add comment to table */
        $this->addCommentOnTable($this->table, 'Files');

        /** Add index for the field `group` */
        $this->createIndex('{{%idx__' . 'files' . '__group}}', $this->table, 'group');

        /** Add index for the field `related_entity_id` */
        $this->createIndex('{{%idx__' . 'files' . '__related_entity_id}}', $this->table, 'related_entity_id');

        /** Add index for the field `hash` */
        $this->createIndex('{{%idx__' . 'files' . '__hash}}', $this->table, 'hash');

        /** Add index for the field `created_at` */
        $this->createIndex('{{%idx__' . 'files' . '__created_at}}', $this->table, 'created_at');

        /** Add index for the field `updated_at` */
        $this->createIndex('{{%idx__' . 'files' . '__updated_at}}', $this->table, 'updated_at');
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
