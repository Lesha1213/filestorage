<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\uploaders\base\AbstractUploaderConfig;

interface UploaderInterface
{
    public const PARAM_UPLOAD_FILE = 'upload_file';

    /**
     * Параметр запроса для идентификатора сущности, к которой будет привязан файл
     */
    public const PARAM_ENTITY_ID = 'entity_id';

    /**
     * Параметр запроса отображаемого имени файла
     */
    public const PARAM_DISPLAY_NAME = 'display_name';

    public const PARAM_CREATED_AT = 'created_at';
    public const PARAM_UPDATED_AT = 'updated_at';

    /**
     * @param AbstractUploaderConfig $config
     * @return AbstractFile
     * @throws UploaderException
     */
    public function upload(AbstractUploaderConfig $config): AbstractFile;
}
