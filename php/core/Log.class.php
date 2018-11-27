<?php

namespace ManyThings\Core;

use DateTimeZone;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Logger;

class Log
{
    const DEBUG = 'debug';
    const INFO = 'info';
    const NOTICE = 'notice';
    const WARNING = 'warning';
    const ERROR = 'error';
    const CRITICAL = 'critical';
    const ALERT = 'alert';
    const EMERGENCY = 'emergency';

    protected $loggers = [];

    protected $appName;
    protected $isDebug;
    protected $timezone;

    protected $logTypes = [
        self::DEBUG => Logger::DEBUG,
        self::INFO => Logger::INFO,
        self::NOTICE => Logger::NOTICE,
        self::WARNING => Logger::WARNING,
        self::ERROR => Logger::ERROR,
        self::CRITICAL => Logger::CRITICAL,
        self::ALERT => Logger::ALERT,
        self::EMERGENCY => Logger::EMERGENCY
    ];

    public function __construct($codename = 'mt', $debug = false, $timezone = 'Etc/UTC')
    {
        $this->appName = $codename;
        $this->isDebug = $debug;
        $this->timezone = $timezone;
    }

    public function addLogger($type)
    {
        $level = $this->logTypes[$type];

        $logger = new Logger($type);
        $logger->setTimezone(new DateTimeZone($this->timezone));

        $handler = new StreamHandler(LOG_PATH . $type . '.log', $level);
        $handler->setFormatter(new LogStreamFormatter());
        $logger->pushHandler($handler);

        if (!$this->isDebug) {
            $handler = new SyslogHandler($this->appName, LOG_USER, $level, true, LOG_NDELAY);
            $handler->setFormatter(new LogSyslogFormatter());
            $logger->pushHandler($handler);
        }

        $this->loggers[$type] = $logger;

        return $this;
    }

    public function addLoggerHandler($logger, $handler, $formatter = null)
    {
        if (!$this->isDebug) {
            $handler->setFormatter($formatter ?: new LogStreamFormatter());
            $logger->pushHandler($handler);
        }

        return $this;
    }

    public function log($type, $message, $context = null)
    {
        if (is_array($message)) {
            $message = json_encode($message);
        }

        if (empty($context)) {
            $context = [];
        } elseif (!is_array($context)) {
            $context = [$type => $context];
        }

        $this->loggers[$type]->addRecord($this->logTypes[$type], $message, $context);

        return $this;
    }

    public function debug($message, $context = null)
    {
        return $this->log(self::DEBUG, $message, $context);
    }

    public function info($message, $context = null)
    {
        return $this->log(self::INFO, $message, $context);
    }

    public function notice($message, $context = null)
    {
        return $this->log(self::NOTICE, $message, $context);
    }

    public function warning($message, $context = null)
    {
        return $this->log(self::WARNING, $message, $context);
    }

    public function error($message, $context = null)
    {
        return $this->log(self::ERROR, $message, $context);
    }

    public function critical($message, $context = null)
    {
        return $this->log(self::CRITICAL, $message, $context);
    }

    public function alert($message, $context = null)
    {
        return $this->log(self::ALERT, $message, $context);
    }

    public function emergency($message, $context = null)
    {
        return $this->log(self::EMERGENCY, $message, $context);
    }
}
