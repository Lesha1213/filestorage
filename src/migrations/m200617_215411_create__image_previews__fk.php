<?php

declare(strict_types=1);

use yii\db\Migration;

/**
 * Migration to create a table references fot {{%image_previews}}
 * @package reactivestudio\filestorage\migrations
 */
class m200617_215411_create__image_previews__fk extends Migration
{
    /**
     * Table name
     * @var string
     */
    private $table = '{{%image_previews}}';

    /**
     * {@inheritDoc}
     */
    public function safeUp()
    {
        /** Add index for the field `original_file_id` */
        $this->createIndex(
            '{{%idx__' . 'image_previews' . '__original_file_id}}',
            $this->table,
            'original_file_id'
        );

        /** Add foreign key for reference to table `{{%files}}` */
        $this->addForeignKey(
            '{{%fk__' . 'image_previews' . '__original_file_id}}',
            $this->table,
            'original_file_id',
            '{{%files}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritDoc}
     */
    public function safeDown()
    {
        /** Remove foreign key for reference to table `{{%files}}` */
        $this->dropForeignKey('{{%fk__' . 'image_previews' . '__original_file_id}}', $this->table);

        /** Remove index for the field `original_file_id` */
        $this->dropIndex('{{%idx__' . 'image_previews' . '__original_file_id}}', $this->table);
    }
}
