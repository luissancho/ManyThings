<?php

namespace ManyThings\Core;

class Cron extends Core
{
    public function __construct()
    {
        parent::__construct();
        $this->dal = self::setCoreDal();
    }

    public function execute()
    {
        $data = [];
        $times = [];

        $start = microtime(true);
        $time = $start;

        /***** Cron operations *****/

        /***** Example
        $data['Op'] = (self::exampleFunction()) ? 'OK' : 'KO';
        $now = microtime(true);
        $times['Op'] = $now - $time;
        $time = $now;
        *****/

        /***** Total time *****/
        $end = $time;
        $times['Total'] = $end - $start;

        /***** Send Log *****/
        $message = [];
        foreach ($data as $key => $val) {
            $message[$key] = $val . ' (' . round($times[$key], 3) . ' sg)';
        }
        $message['Total'] = round($times['Total'], 3) . ' sg';

        $this->di->logger->info($message, 'cron');

        return $message;
    }
}
