<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\interfaces;

interface PreviewInterface
{
    public static function getImageEntityClass(): string;
    public static function operations(): array;

    /**
     * @return string[]
     */
    public static function getPossibleNames(): array;
    public static function findOperation(string $previewName): ?OperationInterface;
}
