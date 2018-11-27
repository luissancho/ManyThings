<?php

namespace ManyThings\Core;

class Op extends Core
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute()
    {
        $data = [];
        $times = [];

        $start = microtime(true);
        $time = $start;

        /***** Example
        $data['Op'] = (self::exampleFunction()) ? 'OK' : 'KO';
        $now = microtime(true);
        $times['Op'] = $now - $time;
        $time = $now;
        *****/

        $end = $time;
        $times['Total'] = $end - $start;

        /***** Send Log *****/
        $message = [];
        foreach ($data as $key => $val) {
            $message[$key] = $val . ' (' . round($times[$key], 3) . ' sg)';
        }
        $message['Total'] = round($times['Total'], 3) . ' sg';

        $this->di->logger->info($message, 'op');

        $results =
        [
            'data' => $data,
            'times' => $times
        ];

        return $results;
    }

    /***** Example
    protected static function exampleFunction()
    {
        $sql = "SELECT id
                FROM table
                ORDER BY id";
        $items = self::getDal()->sqlGetResults($sql);

        foreach ($items as $item) {
            //
        }

        return true;
    }
    *****/
}
