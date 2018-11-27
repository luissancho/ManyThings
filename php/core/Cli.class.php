<?php

namespace ManyThings\Core;

class Cli extends Core
{
    public $task;
    public $action;
    public $params = [];

    protected $request;

    protected $defaultNS = '';

    public function __construct()
    {
        parent::__construct();

        $this->request = $this->di->request;
    }

    public function setJobsNamespace($namespace)
    {
        $this->defaultNS = $namespace;

        return $this;
    }

    public function handle()
    {
        $cliArgs = array_slice($this->request->getCliAll(), 1); // Remove script element (first)

        $taskName = array_shift($cliArgs);
        $taskClass = $this->defaultNS . '\\' . Utils::snakeToCamelCase($taskName) . 'Task';
        $this->task = new $taskClass();

        $actionName = array_shift($cliArgs);
        $this->action = lcfirst(Utils::snakeToCamelCase($actionName)) . 'Action';

        $this->params = $cliArgs;

        return $this;
    }

    public function dispatch()
    {
        call_user_func_array([$this->task, $this->action], $this->params);

        return $this;
    }

    public function loaded()
    {
        return !is_null($this->task);
    }
}
