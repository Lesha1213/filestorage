<?php

declare(strict_types=1);

namespace reactivestudio\filestorage\exceptions;

use Exception;
use yii\web\HttpException;

class FileUploadHttpException extends HttpException
{
    /**
     * {@inheritdoc}
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct(499, $message, $code, $previous);
    }
}
