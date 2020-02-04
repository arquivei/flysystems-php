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

    public function alert(string $message, array $context = []): void
    {
        $this->log->alert($message, $context);
    }
    public function debug(string $message, array $context = []): void
    {
        $this->log->debug($message, $context);
    }
    public function emergency(string $message, array $context = []): void
    {
        $this->log->emergency($message, $context);
    }
    public function error(string $message, array $context = []): void
    {
        $this->log->error($message, $context);
    }
    public function info(string $message, array $context = []): void
    {
        $this->log->info($message, $context);
    }
    public function notice(string $message, array $context = []): void
    {
        $this->log->notice($message, $context);
    }
    public function warning(string $message, array $context = []): void
    {
        $this->log->warning($message, $context);
    }
}
