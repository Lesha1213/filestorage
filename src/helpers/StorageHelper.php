<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\helpers;

use reactivestudio\filestorage\exceptions\StorageException;
use yii\base\Exception;
use yii\helpers\FileHelper;
use Yii;

/**
 * Class StorageHelper
 * @package reactivestudio\filestorage\helpers
 */
class StorageHelper
{
    public static function getDirName(string $path): string
    {
         return pathinfo($path, PATHINFO_DIRNAME);
    }

    public static function getFileName(string $path): string
    {
        return pathinfo($path, PATHINFO_BASENAME);
    }

    public static function getExtension(string $path): string
    {
        return pathinfo($path, PATHINFO_EXTENSION);
    }

    /**
     * @param string $alias
     * @throws StorageException
     */
    public static function touchDir(string $alias): void
    {
        $path = FileHelper::normalizePath(Yii::getAlias($alias));

        if (file_exists($path)) {
            return;
        }

        try {
            $isCreated = FileHelper::createDirectory($path);
        } catch (Exception $e) {
            throw new StorageException("Cannot create directory: {$path}. \n Error: {$e->getMessage()}");
        }

        if (!$isCreated) {
            throw new StorageException("Cannot create directory: {$path}.");
        }
    }

    /**
     * If destination file is existed, then file wil be overwritten
     *
     * @param string $source
     * @param string $destination
     *
     * @throws StorageException
     */
    public static function copy(string $source, string $destination): void
    {
        $dir = self::getDirName($destination);
        self::touchDir($dir);

        if (!copy($source, $destination)) {
            throw new StorageException(
                "Cannot copy file. \n 
                Source: {$source}. \n 
                Destination: {$destination}"
            );
        }
    }

    /**
     * @param string $path
     * @throws StorageException
     */
    public static function deleteFile(string $path): void
    {
        if (!@unlink($path)) {
            throw new StorageException("Cannot remove file with path: {$path}");
        }
    }

    /**
     * @param string $url
     * @return string
     * @throws StorageException
     */
    public static function downloadFile(string $url): string
    {
        $downloadedContent = @file_get_contents($url);
        if ($downloadedContent === false) {
            throw new StorageException("File cannot be downloaded by url: {$url}");
        }

        $tempFileName = tempnam(sys_get_temp_dir(), 'f_');
        if ($tempFileName === false) {
            throw new StorageException('Cannot create temp file');
        }

        if (!@file_put_contents($tempFileName, $downloadedContent)) {
            throw new StorageException('Cannot save temp file');
        }

        return $tempFileName;
    }
}
