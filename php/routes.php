<?php
use ManyThings\Core\Router;

$router = new Router();

$router->setDefaultNamespace('ManyThings\Controllers');

$router->addRoute('/', 'IndexController');

$router->addRoute('/login', 'LoginController', 'login', ['GET']);
$router->addRoute('/login/{path:uri}', 'LoginController', 'login', ['GET']);
$router->addRoute('/login', 'LoginController', 'loginHandler', ['POST']);
$router->addRoute('/logout', 'LoginController', 'logout');
$router->addRoute('/password', 'LoginController', 'password', ['GET']);
$router->addRoute('/password', 'LoginController', 'passwordHandler', ['POST']);
$router->addRoute('/user', 'UserController', 'account', ['GET']);
$router->addRoute('/user', 'UserController', 'accountHandler', ['POST']);

$router->addRoute('/admin', 'AdminController');
$router->addRoute('/admin/s/{nav}', 'AdminController', 'section');
$router->addRoute('/admin/s/{nav}/{sub}', 'AdminController', 'section');
$router->addRoute('/admin/s/{nav}/{sub}/{id}', 'AdminController', 'section');

$router->addRoute('/api', 'ApiController');
$router->addRoute('/api/grid/{grid}', 'ApiController', 'grid', ['GET']);
$router->addRoute('/api/grid/{grid}/{format}', 'ApiController', 'grid', ['GET']);
$router->addRoute('/api/dashboard/{dashboard}', 'ApiController', 'dashboard', ['GET']);

$router->addRoute('/sitemap.xml', 'SitemapController');

$router->addNotFound('/api', 'ApiController');
$router->addNotFound('/', 'IndexController');

return $router;
