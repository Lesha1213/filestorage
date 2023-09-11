<?php

namespace reactivestudio\filestorage\interfaces;

use yii\db\ActiveQueryInterface;

interface FileQueryInterface extends ActiveQueryInterface
{
    /**
     * @param int|null $id
     * @return FileQueryInterface
     */
    public function entityId(?int $id): FileQueryInterface;

    /**
     * @param string $group
     * @return FileQueryInterface
     */
    public function entityGroup(string $group): FileQueryInterface;

    /**
     * @param string $hash
     * @return FileQueryInterface
     */
    public function hash(string $hash): FileQueryInterface;
}
