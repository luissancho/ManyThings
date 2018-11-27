<?php
require_once ABSPATH . 'vendor/autoload.php';
require_once ABSPATH . 'php/core/Loader.class.php';

use ManyThings\Core\Loader;

$loader = new Loader();

$loader->addNamespaces([
    'ManyThings\Core' => ABSPATH . 'php/core',
    'ManyThings\Core\Dal' => ABSPATH . 'php/core/dal',
    'ManyThings\Admin' => ABSPATH . 'php/admin',
    'ManyThings\Admin\Dal' => ABSPATH . 'php/admin/dal',
    'ManyThings\Models' => ABSPATH . 'php/models',
    'ManyThings\Models\Dal' => ABSPATH . 'php/models/dal',
    'ManyThings\Controllers' => ABSPATH . 'php/controllers',
    'ManyThings\Controllers\Dal' => ABSPATH . 'php/controllers/dal',
    'ManyThings\Services' => ABSPATH . 'php/services',
    'ManyThings\Services\Dal' => ABSPATH . 'php/services/dal',
    'ManyThings\Tasks' => ABSPATH . 'php/tasks',
    'ManyThings\Jobs\Admin' => ABSPATH . 'php/jobs/admin'
])->register();

return $loader;
