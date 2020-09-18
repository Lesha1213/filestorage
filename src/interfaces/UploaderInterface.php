<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\models\base\AbstractFile;

interface UploaderInterface
{
    public function setFileTypeStrategy(FileStrategyInterface $strategy);
    public function upload(UploadInfoInterface $uploadInfo): AbstractFile;
}
