<?php

namespace ManyThings\Tasks;

use ManyThings\Core\Task;
use Psy\Configuration;
use Psy\Shell;

class ShellTask extends Task
{
    public function runAction()
    {
        $shell = new Shell(new Configuration());
        $shell->run();
    }
}
