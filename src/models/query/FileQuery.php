<?php

namespace reactivestudio\filestorage\models\query;

use reactivestudio\filestorage\interfaces\FileQueryInterface;
use yii\db\ActiveQuery;

/**
 * Class FileQuery
 *
 * @method FileQueryInterface all($db = null)
 * @method FileQueryInterface one($db = null)
 *
 * @see \reactivestudio\filestorage\models\base\AbstractFile
 * @package reactivestudio\filestorage\models\query
 */
class FileQuery extends ActiveQuery implements FileQueryInterface
{
    public function entityId(?int $id): FileQueryInterface
    {
        return (null === $id) ? $this : $this->andWhere(['related_entity_id' => $id]);
    }

    public function entityGroup(string $group): FileQueryInterface
    {
        return $this->andWhere(['group' => $group]);
    }

    public function hash(string $hash): FileQueryInterface
    {
        return $this->andWhere(['hash' => $hash]);
    }
}
