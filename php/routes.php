<?php
use ManyThings\Core\Router;

$router = new Router();

$router->setDefaultNamespace('ManyThings\Controllers');

$router->addRoute('/', 'IndexController');

$router->addRoute('/api', 'ApiController');
$router->addRoute('/api/grid/{grid}', 'ApiController', 'grid', ['GET']);
$router->addRoute('/api/grid/{grid}/{format}', 'ApiController', 'grid', ['GET']);
$router->addRoute('/api/dashboard/{dashboard}', 'ApiController', 'dashboard', ['GET']);

$router->addRoute('/sitemap.xml', 'SitemapController');

$router->addNotFound('/api', 'ApiController');
$router->addNotFound('/', 'IndexController');

return $router;
