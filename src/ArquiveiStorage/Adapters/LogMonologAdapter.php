<?php

namespace Arquivei\Flysystems\ArquiveiStorage\Adapters;

use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LogMonologAdapter
{
    private $log;

    public function __construct()
    {
        $handler = new StreamHandler("php://stdout");
        $handler->setFormatter(new JsonFormatter());
        $this->log = new Logger('arquivei_flysystems_php');
        $this->log->pushHandler($handler);
        $this->log->pushProcessor(function ($record) {
            $record['datetime'] = $record['datetime']->format('c');
            return $record;
        });
    }

    public function alert(string $message, array $context = []): bool
    {
        return $this->log->addAlert($message, $context);
    }

    public function debug(string $message, array $context = []): bool
    {
        return $this->log->addDebug($message, $context);
    }

    public function emergency(string $message, array $context = []): bool
    {
        return $this->log->addEmergency($message, $context);
    }

    public function error(string $message, array $context = []): bool
    {
        return $this->log->addError($message, $context);
    }

    public function info(string $message, array $context = []): bool
    {
        return $this->log->addInfo($message, $context);
    }

    public function notice(string $message, array $context = []): bool
    {
        return $this->log->addNotice($message, $context);
    }

    public function warning(string $message, array $context = []): bool
    {
        return $this->log->warning($message, $context);
    }
}
