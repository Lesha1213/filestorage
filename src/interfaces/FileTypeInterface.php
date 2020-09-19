<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

interface FileTypeInterface
{
    /**
     * @return string[]
     */
    public static function getMimeSearchPatterns(): array;

    /**
     * @return string
     */
    public static function getName(): string;

    /**
     * @return string[]|null
     */
    public function getAllowedExtensions(): ?array;
}
