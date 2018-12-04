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

    public function summary()
    {
        $queues = [];

        foreach ($this->queues() as $queue) {
            $queues[$queue] = $this->size($queue);
        }

        echo Utils::arrayToText($queues, '', false);
    }

    public function queues()
    {
        return Resque::queues();
    }

    public function size($queue)
    {
        return Resque::redis()->llen('queue:' . $queue);
    }

    public function list($queue, $offset = 0, $limit = 10)
    {
        return Resque::redis()->lrange('queue:' . $queue, $offset, $limit);
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

    public function dequeue($queue)
    {
        Resque::redis()->del('queue:' . $queue);

        return $this;
    }
}
