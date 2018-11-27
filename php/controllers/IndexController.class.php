<?php

namespace ManyThings\Controllers;

use ManyThings\Core\Controller;

class IndexController extends Controller
{
    public function indexAction()
    {
        $this->response->send('index');
    }
}
