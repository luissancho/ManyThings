<?php
use ManyThings\Core\AppException;

/***** Exceptions/Errors handling will be set by config *****/
ini_set('display_errors', 0);
error_reporting(0);
set_exception_handler(function ($e) {
    die($e->getMessage());
});

/***** Root absolute path *****/
$absPath = str_replace('php', '', dirname(__FILE__)) . '/';
$absPath = str_replace('//', '/', $absPath);
define('ABSPATH', $absPath); // With final slash

/***** Production deploy shared path *****/
define('SHARED_PATH', ABSPATH . '../../shared/');

/***** Set app type *****/
define('APP_TYPE', 'worker');

/***** Global functions *****/
include ABSPATH . 'php/functions.php';

try {
    /***** Core classes autoload *****/
    $loader = require ABSPATH . 'php/autoload.php';

    /***** Services dependency injector *****/
    $di = require ABSPATH . 'php/services.php';
} catch (\Throwable $e) {
    if ($e instanceof AppException) {
        echo $e->getMessage();
    } else {
        echo AppException::handle($e);
    }
}
