<?php

namespace ManyThings\Core;

use Monolog\Formatter\JsonFormatter;

class LogSyslogFormatter extends JsonFormatter
{
    public function __construct()
    {
        parent::__construct(self::BATCH_MODE_NEWLINES, false);
    }

    public function format(array $record)
    {
        $message = json_decode($record['message'], true);
        if (!is_array($message)) {
            $message = $record['message'];
        }

        $output = [
            'context' => $record['context'],
            'message' => $message
        ];

        if (empty($output['context'])) {
            unset($output['context']);
        }

        return parent::format($output);
    }
}
