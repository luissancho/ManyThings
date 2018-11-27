<?php

namespace ManyThings\Core;

class Router extends Core
{
    public $controller;
    public $action;
    public $params = [];

    protected $prefixes = [];
    protected $routes = [];
    protected $notFound = [];
    protected $redirs = [];

    protected $request;
    protected $response;

    protected $defaultNS = '';
    protected $aliasMap = [
        '#{(\w+)}#' => '{$1:[0-9a-z_:\-]+}',
        '#{(\w+):number}#' => '{$1:[0-9]+}',
        '#{(\w+):locale}#' => '{$1:[a-z]{2}}',
        '#{(\w+):string}#' => '{$1:[a-z]+}',
        '#{(\w+):uri}#' => '{$1:.+}'
    ];

    public function __construct()
    {
        parent::__construct();

        $this->request = $this->di->request;
        $this->response = $this->di->response;
    }

    public function addRoute($pattern, $controller, $action = 'index', $methods = ['GET', 'POST'])
    {
        $segments = [];
        $params = [];

        $pattern = strtolower($pattern);
        $aliases = array_keys($this->aliasMap);
        $patterns = array_values($this->aliasMap);

        $parts = explode('/', ltrim(preg_replace($aliases, $patterns, $pattern), '/'));

        foreach ($parts as $part) {
            if (preg_match('#{(\w+):(.+)}#', $part, $matches)) {
                $params[] = $matches[1];
                $segments[] = '(' . $matches[2] . ')';
            } else {
                $segments[] = $part;
            }
        }

        if (strstr($controller, '\\') === false && !empty($this->defaultNS)) {
            $controller = $this->defaultNS . '\\' . $controller;
        }

        $this->routes[] = [
            'pattern' => '/' . implode('/', $segments),
            'params' => $params,
            'controller' => $controller,
            'action' => $action,
            'methods' => $methods
        ];

        return $this;
    }

    public function addNotFound($pattern, $controller, $action = 'notFound')
    {
        if (strstr($controller, '\\') === false && !empty($this->defaultNS)) {
            $controller = $this->defaultNS . '\\' . $controller;
        }

        $this->notFound[] = [
            'pattern' => $pattern,
            'controller' => $controller,
            'action' => $action
        ];

        return $this;
    }

    public function addPrefix($tpl, $canonical = true)
    {
        $this->prefixes[] = [
            'pattern' => $tpl,
            'canonical' => $canonical
        ];

        return $this;
    }

    public function addRedir($pattern, $replacement, $methods = ['GET', 'POST'], $code = 301)
    {
        $segments = [];
        $params = [];

        $pattern = strtolower($pattern);
        $aliases = array_keys($this->aliasMap);
        $patterns = array_values($this->aliasMap);

        $parts = explode('/', ltrim(preg_replace($aliases, $patterns, $pattern), '/'));

        foreach ($parts as $part) {
            if (preg_match('#{(\w+):(.+)}#', $part, $matches)) {
                $params[] = $matches[1];
                $segments[] = '(' . $matches[2] . ')';
            } else {
                $segments[] = $part;
            }
        }

        $this->redirs[] = [
            'pattern' => '/' . implode('/', $segments),
            'params' => $params,
            'replacement' => $replacement,
            'methods' => $methods,
            'code' => $code
        ];

        return $this;
    }

    public function addPattern($alias, $pattern)
    {
        $alias = '#{(\w+):' . $alias . '}#';
        $pattern = '{$1:' . $pattern . '}';

        $this->patterns[$alias] = $pattern;

        return $this;
    }

    public function setDefaultNamespace($namespace)
    {
        $this->defaultNS = $namespace;

        return $this;
    }

    protected function parseRoute($route, $uri, $method)
    {
        $pattern = '#^' . $route['pattern'] . '$#i';
        if (preg_match($pattern, $uri, $matches) && in_array($method, $route['methods'])) {
            array_shift($matches);

            $params = [];
            for ($i = 0; $i < count($matches); $i++) {
                $params[$route['params'][$i]] = $matches[$i];
            }

            return $params;
        }

        return false;
    }

    protected function checkNotFound($route, $uri)
    {
        if (strncmp($uri, $route['pattern'], strlen($route['pattern'])) === 0) {
            return true;
        }

        return false;
    }

    protected function checkPrefix($route, $uri)
    {
        if (strncmp($uri, $route['pattern'], strlen($route['pattern'])) === 0) {
            return true;
        }

        return false;
    }

    public function handle($uri = null)
    {
        $method = $this->request->reqMethod;
        if (empty($uri)) {
            $uri = '/' . $this->request->relUri;
        }

        // Set prefix as template
        foreach ($this->prefixes as $prefix) {
            if ($this->checkPrefix($prefix, $uri)) {
                $tpl = trim($prefix['pattern'], '/');
                $this->response->setTemplate($tpl, $prefix['canonical']);
                $uri = preg_replace('#/' . $tpl . '(/.?)#', '$1', $uri);

                break;
            }
        }

        // Get from configured redirections
        foreach ($this->redirs as $redir) {
            $params = $this->parseRoute($redir, $uri, $method);
            if ($params !== false) {
                $path = $redir['replacement'] . '/';
                foreach ($params as $key => $val) {
                    $path = str_replace('{' . $key . '}', $val, $path);
                }
                if ($this->request->queryString) {
                    $path .= '?' . $this->request->queryString;
                }
                $this->response->redirect(DOMPATH . '/' . $path, $redir['code']);

                return $this;
            }
        }

        // Get from configured routes
        foreach ($this->routes as $route) {
            $params = $this->parseRoute($route, $uri, $method);
            if ($params !== false) {
                $this->controller = new $route['controller']();
                $this->action = $route['action'] . 'Action';
                $this->params = $params;

                return $this;
            }
        }

        // Get from additional not found routes
        foreach ($this->notFound as $route) {
            if ($this->checkNotFound($route, $uri)) {
                $this->controller = new $route['controller']();
                $this->action = $route['action'] . 'Action';

                return $this;
            }
        }

        // Get predefined not found by default
        $this->controller = new Controller();
        $this->action = 'notFoundAction';

        return $this;
    }

    public function dispatch()
    {
        if ($this->loaded()) {
            $this->controller->dispatch($this->action, $this->params);
        }

        return $this;
    }

    public function loaded()
    {
        return !is_null($this->controller);
    }

    public function dispatchError($e)
    {
        $this->controller->errorAction($e);

        return $this;
    }
}
