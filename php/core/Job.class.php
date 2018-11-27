<?php

namespace ManyThings\Core;

class Job extends Core
{
    public function setUp()
    {
        ini_set('max_input_time', -1);
        ini_set('default_socket_timeout', 600);
        ini_set('memory_limit', '1G');
        set_time_limit(0);
    }

    public function tearDown()
    {
    }

    public function perform()
    {
    }
}
