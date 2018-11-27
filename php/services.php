<?php
use Aws\S3\S3Client;
use League\Flysystem\AwsS3v2\AwsS3Adapter;
use League\Flysystem\Filesystem;
use ManyThings\Core\API;
use ManyThings\Core\Cli;
use ManyThings\Core\Config;
use ManyThings\Core\DI;
use ManyThings\Core\Log;
use ManyThings\Core\MySql;
use ManyThings\Core\PostgreSql;
use ManyThings\Core\Queue;
use ManyThings\Core\Request;
use ManyThings\Core\Response;
use ManyThings\Core\Sessions;
use ManyThings\Core\SmartyML;
use ManyThings\Core\Utils;

$di = DI::getDI();

/***** Core classes loader *****/
if (isset($loader)) {
    $di->set('loader', $loader);
}

/***** Config *****/
$di->set('config', function () {
    // First get config base
    $configBase = file_get_contents(ABSPATH . 'config-base.json');
    $config = new Config($configBase);

    // Search config file in shared dir or root by default
    if (file_exists(SHARED_PATH . 'config.json')) {
        $configEnv = file_get_contents(SHARED_PATH . 'config.json');
        $config->merge($configEnv);
    } elseif (file_exists(ABSPATH . 'config.json')) {
        $configEnv = file_get_contents(ABSPATH . 'config.json');
        $config->merge($configEnv);
    }

    setlocale(LC_TIME, $config->date->locale);

    // Set log directory
    if (file_exists(SHARED_PATH . 'log')) {
        $logDir = SHARED_PATH . 'log/';
    } elseif (file_exists(ABSPATH . 'log/')) {
        $logDir = ABSPATH . 'log/';
    } else {
        mkdir(ABSPATH . 'log', 0700);
        $logDir = ABSPATH . 'log/';
    }

    define('LOG_PATH', $logDir);

    // Set files directory
    if (file_exists(SHARED_PATH . 'files')) {
        $filesDir = SHARED_PATH . 'files/';
    } elseif (file_exists(ABSPATH . 'files')) {
        $filesDir = ABSPATH . 'files/';
    } else {
        mkdir(ABSPATH . 'files', 0700);
        $filesDir = ABSPATH . 'files/';
    }

    define('FILES_PATH', $filesDir);

    // Errors handling
    if ($config->app->debug) {
        error_reporting(E_ERROR | E_PARSE);
        //error_reporting(E_ALL);
    }

    return $config;
});

/***** Page request object *****/
$di->set('request', function () use ($argv) {
    if (get_magic_quotes_gpc()) {
        $_GET = Utils::stripslashesDeep($_GET);
        $_POST = Utils::stripslashesDeep($_POST);
        $_COOKIE = Utils::stripslashesDeep($_COOKIE);
    }

    $request = new Request($argv, $_SERVER, $_GET, $_POST, $_FILES, $_COOKIE);

    define('DOMPATH', $request->domPath); // Without surrounding slashes

    return $request;
});

/***** Main DB connection *****/
$di->set('db', function () use ($di) {
    $src = $di->config->db;

    switch ($src->adapter) {
        case 'mysql':
            $persistent = APP_TYPE == 'worker' ? true : false; // Persistent connection
            $db = new MySql($src->username, $src->password, $src->database, $src->host, $persistent);
            break;
        case 'pgsql':
            $port = !empty($src->port) ? $src->port : '5432';
            $db = new PostgreSql($src->username, $src->password, $src->database, $src->host, $port);
            break;
        default:
            $db = null;
    }

    return $db;
});

/***** Sources DB connections *****/
$di->set('dbs', function () use ($di) {
    $dbs = [];

    foreach ($di->config->dbs as $name => $src) {
        switch ($src->adapter) {
            case 'mysql':
                $persistent = APP_TYPE == 'worker' ? true : false; // Persistent connection
                $db = new MySql($src->username, $src->password, $src->database, $src->host, $persistent);
                break;
            case 'pgsql':
                $port = !empty($src->port) ? $src->port : '5432';
                $db = new PostgreSql($src->username, $src->password, $src->database, $src->host, $port);
                break;
            default:
                continue;
        }

        $dbs[$name] = $db;
    }

    return $dbs;
});

/***** Sources API connections *****/
$di->set('apis', function () use ($di) {
    $apis = [];

    foreach ($di->config->apis as $name => $src) {
        $api = new API($src->host, $src->headers->toArray(), $src->options->toArray());

        $apis[$name] = $api;
    }

    return $apis;
});

/***** Session *****/
$di->set('session', function () {
    $session = Sessions::getSession();

    date_default_timezone_set($session->timeZone);

    return $session;
});

/***** Page response object *****/
$di->set('response', function () use ($di) {
    $GLOBALS['_LANGUAGES_'] = null;

    $smarty = new SmartyML($di->session->lang);
    $smarty
        ->setTemplateDir(ABSPATH . 'templates')
        ->setCompileDir(ABSPATH . 'templates/smarty/templates_c')
        ->setCacheDir(ABSPATH . 'templates/smarty/cache')
        ->setConfigDir(ABSPATH . 'templates/smarty/configs');

    include ABSPATH . 'php/templates.php';
    $smarty->registerPlugin('function', 'build', 'buildSmartyFunction');
    $smarty->registerPlugin('modifier', 'number', 'numberSmartyModifier');
    $smarty->registerPlugin('modifier', 'currency', 'currencySmartyModifier');

    $response = new Response($smarty);

    return $response;
});

/***** Logger *****/
$di->set('logger', function () use ($di) {
    $logger = new Log($di->config->app->codename, $di->config->app->debug);

    $logger
        ->addLogger(Log::INFO)
        ->addLogger(Log::ERROR);

    return $logger;
});

/***** Router *****/
$di->set('router', function () {
    $router = require ABSPATH . 'php/routes.php';

    return $router;
});

/***** Amazon AWS S3 Filesystem *****/
$di->set('filesystem', function () use ($di) {
    $s3 = $di->config->s3;

    if (!$s3->bucket) {
        return null;
    }

    $client = S3Client::factory([
        'key' => $s3->key,
        'secret' => $s3->secret,
        'region' => $s3->region,
    ]);

    $adapter = new AwsS3Adapter($client, $s3->bucket);
    $filesystem = new Filesystem($adapter);

    return $filesystem;
});

/***** Queue *****/
$di->set('queue', function () use ($di) {
    $resque = $di->config->resque;

    if (!$resque->server) {
        return null;
    }

    $queue = new Queue($resque->server, $resque->database);

    $queue->setJobsNamespace('ManyThings\\Jobs');

    return $queue;
});

/***** Cli *****/
$di->set('cli', function () {
    $cli = new Cli();

    $cli->setJobsNamespace('ManyThings\\Tasks');

    return $cli;
});

return $di;
