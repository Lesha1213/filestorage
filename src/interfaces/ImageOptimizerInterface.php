<?php

namespace reactivestudio\filestorage\interfaces;

/**
 * Интерфейс для реализации оптимизаторов файлов изображений.
 */
interface ImageOptimizerInterface
{
    /**
     * Данные изображения считать по пути $fileName и туда же записать результат оптимизации.
     *
     * @param string $fileName
     * @return mixed
     */
    public function optimizeFile(string $fileName);
}
