<?php

namespace ManyThings\Core;

class AppException extends \Exception
{
    const TYPE_APP = 'app';
    const TYPE_SQL = 'sql';

    public function __construct($e, $query = null)
    {
        $di = DI::getDI();

        if (is_object($e)) {
            $instance = $e;
            $message = $e->getMessage();
        } else {
            $instance = $this;
            $message = $e;
        }

        $type = !empty($query) ? self::TYPE_SQL : self::TYPE_APP;

        $logContext = [
            'location' => $di->loaded('request') ? $di->request->relUri : '',
            'user_id' => $di->loaded('session') ? $di->session->uid : 0,
            'username' => $di->loaded('session') ? $di->session->user['username'] : ''
        ];

        $logMessage = [
            'type' => $type,
            'message' => $message,
            'query' => $query,
            'trace' => Utils::linesToArray($instance->getTraceAsString())
        ];

        if ($type == self::TYPE_APP) {
            unset($logMessage['query']);
        }

        if ($di->loaded('logger')) {
            $di->logger->error($logMessage, $logContext);
        }

        if ($di->loaded('config') && $di->config->app->debug) {
            $message = Utils::arrayToText($logMessage);
        }

        parent::__construct($message);
    }

    public static function handle($e)
    {
        $instance = new self($e);

        return $instance->getMessage();
    }
}
