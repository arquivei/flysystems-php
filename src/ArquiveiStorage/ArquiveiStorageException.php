<?php

namespace Arquivei\Flysystems\ArquiveiStorage;

class ArquiveiStorageException extends \Exception
{
    public function __construct($previous, $code = 0, $message = '')
    {
        parent::__construct($message, $code, $previous);
    }
}