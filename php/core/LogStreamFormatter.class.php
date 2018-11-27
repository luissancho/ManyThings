<?php

namespace ManyThings\Core;

use Monolog\Formatter\NormalizerFormatter;

class LogStreamFormatter extends NormalizerFormatter
{
    public function format(array $record)
    {
        $message = json_decode($record['message'], true);
        if (!is_array($message)) {
            $message = $record['message'];
        }

        $output = [
            'datetime' => $record['datetime']->format('Y-m-d H:i:s'),
            'context' => $record['context'],
            'message' => $message
        ];

        if (empty($output['context'])) {
            unset($output['context']);
        }

        return $this->arrayToString($output) . PHP_EOL;
    }

    protected function arrayToString($array, $prefix = '')
    {
        $tab = '    ';
        $string = '';

        foreach ($array as $key => $val) {
            if (is_array($val)) {
                $string .= $prefix . '[' . $key . ']' . PHP_EOL;
                $string .= $this->arrayToString($val, $prefix . $tab);
            } else {
                $string .= $prefix . '[' . $key . '] ' . $val . PHP_EOL;
            }
        }

        return $string;
    }
}
