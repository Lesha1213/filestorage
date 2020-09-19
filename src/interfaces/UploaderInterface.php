<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

use reactivestudio\filestorage\exceptions\UploaderException;
use reactivestudio\filestorage\models\base\AbstractFile;
use reactivestudio\filestorage\models\form\FileForm;

interface UploaderInterface
{
    /**
     * @param string $fileEntityClass
     * @param array $config
     *
     * @return FileForm
     * @throws UploaderException
     */
    public function buildForm(string $fileEntityClass, array $config = []): FileForm;

    /**
     * @param FileForm $form
     * @return AbstractFile
     * @throws UploaderException
     */
    public function upload(FileForm $form): AbstractFile;
}
