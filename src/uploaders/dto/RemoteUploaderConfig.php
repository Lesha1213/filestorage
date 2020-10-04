<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\uploaders\dto;

use reactivestudio\filestorage\uploaders\base\AbstractUploaderConfig;

class RemoteUploaderConfig extends AbstractUploaderConfig
{
    /**
     * @var string
     */
    private $urlToFile;

    /**
     * @return string
     */
    public function getUrlToFile(): string
    {
        return $this->urlToFile;
    }

    /**
     * @param string $urlToFile
     * @return RemoteUploaderConfig
     */
    public function setUrlToFile(string $urlToFile): self
    {
        $this->urlToFile = $urlToFile;
        return $this;
    }
}
