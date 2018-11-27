<?php

namespace ManyThings\Core;

use Resque;
use Resque_Event;
use ResqueScheduler;

class Queue
{
    protected $defaultNS = '';

    public function __construct($server, $database)
    {
        Resque::setBackend($server, $database);

        Resque_Event::listen('onFailure', function ($e, $job) {
            throw new AppException($e);
        });
    }

    public function setJobsNamespace($namespace)
    {
        $this->defaultNS = $namespace;

        return $this;
    }

    public function enqueue($queue, $job, $args = [], $delay = 0)
    {
        $jobClass =
            $this->defaultNS
            . '\\'
            . Utils::snakeToCamelCase($queue)
            . '\\'
            . Utils::snakeToCamelCase($job)
            . 'Job';

        if ($delay > 0) {
            ResqueScheduler::enqueueIn($delay, $queue, $jobClass, $args);
        } else {
            Resque::enqueue($queue, $jobClass, $args);
        }

        return $this;
    }
}
