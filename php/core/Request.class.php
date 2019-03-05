<?php

namespace ManyThings\Core;

use Mobile_Detect;

class Request extends Core
{
    const DEVICE_DESKTOP = 'desktop';
    const DEVICE_TABLET = 'tablet';
    const DEVICE_MOBILE = 'mobile';

    public $domPath;
    public $domRelPath;
    public $relUri;
    public $queryString;
    public $reqMethod;
    public $rawInput;
    public $device;
    public $userIp;

    protected $argv = [];
    protected $server = [];
    protected $get = [];
    protected $post = [];
    protected $files = [];
    protected $cookie = [];
    protected $request = [];

    public function __construct($argv, $server, $get, $post, $files, $cookie)
    {
        parent::__construct();

        $config = $this->di->config;

        $this->argv = $argv;
        $this->server = $server;
        $this->get = $get;
        $this->post = $post;
        $this->files = $files;
        $this->cookie = $cookie;
        $this->request = array_merge($get, $post);

        $uriParts = explode('?', $this->getServer('REQUEST_URI'));
        $requestUri = strtolower(array_shift($uriParts));
        $requestQuery = array_shift($uriParts);

        $reqParts = $this->getRequestParts();

        if (APP_TYPE == 'web') {
            // Check trailing slash (except files or API calls)
            if (substr($requestUri, -1) != '/' && strpos($reqParts['uri'], '.') === false && substr($reqParts['uri'], 0, 4) != 'api/') {
                $url = $requestUri . '/';
                if ($requestQuery) {
                    $url .= '?' . $requestQuery;
                }

                header('Status: 301 Moved Permanently');
                header('Location: ' . $url);
                exit;
            }

            // Check path
            if ($reqParts['path'] != $config->app->dompath) {
                $url = $config->app->dompath . '/';
                if ($reqParts['uri']) {
                    $url .= $reqParts['uri'] . '/';
                }
                if ($requestQuery) {
                    $url .= '?' . $requestQuery;
                }

                header('Status: 301 Moved Permanently');
                header('Location: ' . $url);
                exit;
            }
        }

        $this->domPath = $config->app->dompath;
        $this->domRelPath = $config->app->domrelpath;
        $this->relUri = $reqParts['uri'];
        $this->queryString = $requestQuery;
        $this->reqMethod = $server['REQUEST_METHOD'];
        $this->rawBody = $this->getRawBody();
        $this->device = $this->getDevice();
        $this->userIp = $this->getUserIp();
    }

    protected function getVar($key, $var, $default = null)
    {
        return isset($var[$key]) ? trim($var[$key]) : $default;
    }

    protected function getBulk($items, $var)
    {
        foreach ($items as $key => $val) {
            if (isset($var[$key])) {
                $items[$key] = trim($var[$key]);
            }
        }

        return $items;
    }

    public function getCli($key)
    {
        return $this->getVar($key, $this->argv);
    }

    public function getServer($key)
    {
        return $this->getVar($key, $this->server);
    }

    public function get($key, $default = null)
    {
        return $this->getVar($key, $this->request, $default);
    }

    public function getData($items)
    {
        return $this->getBulk($items, $this->request);
    }

    public function getPost($key, $default = null)
    {
        return $this->getVar($key, $this->post, $default);
    }

    public function getPostData($items)
    {
        return $this->getBulk($items, $this->post);
    }

    public function getAll()
    {
        return $this->request;
    }

    public function getPostAll()
    {
        return $this->post;
    }

    public function getCliAll()
    {
        return $this->argv;
    }

    public function getCookie($key, $default = null)
    {
        return $this->getVar($key, $this->cookie, $default);
    }

    public function setCookie($key, $value)
    {
        setcookie($key, $value, time() + 31536000, '/', '', 0);
    }

    public function clearCookie($key)
    {
        setcookie($key, '', time() - 31536000, '/', '', 0);
    }

    public function getFile($key)
    {
        return isset($this->files[$key]) ? $this->files[$key] : null;
    }

    public function getRawBody()
    {
        return file_get_contents('php://input');
    }

    public function getRequestParts()
    {
        $config = $this->di->config;

        $protocol = strtolower($this->getServer('SERVER_PROTOCOL'));
        $protocol = substr($protocol, 0, strpos($protocol, '/'));
        $protocol .= $this->getServer('HTTPS') == 'on' ? 's' : '';
        $host = $this->getServer('SERVER_NAME');
        $port = !in_array($this->getServer('SERVER_PORT'), ['80', '443']) ? ':' . $this->getServer('SERVER_PORT') : '';

        $uriParts = explode('?', strtolower($this->getServer('REQUEST_URI')));
        $requestUri = array_shift($uriParts);
        $relUri = preg_replace('#/' . $config->app->domrelpath . '(/.?)#', '$1', $requestUri);

        $relPath = $requestUri;
        if (!empty($relUri)) {
            $relPath = preg_replace('#(.?)' . $relUri . '#', '$1', $requestUri);
        }
        $domPath = $protocol . '://' . $host . $port . $relPath;

        return [
            'path' => trim($domPath, '/'),
            'uri' => trim($relUri, '/')
        ];
    }

    public function getUrl()
    {
        $url = $this->domPath . '/';
        if ($this->relUri) {
            $url .= $this->relUri . '/';
        }
        if ($this->queryString) {
            $url .= '?' . $this->queryString;
        }

        return $url;
    }

    public function getReferer()
    {
        $referer = $this->getServer('HTTP_REFERER');
        if (!empty($referer)) {
            return $referer;
        }

        return '';
    }

    public static function getDevice()
    {
        $detector = new Mobile_Detect();

        $device = self::DEVICE_DESKTOP;

        if ($detector->isMobile()) {
            $device = self::DEVICE_MOBILE;
        }

        if ($detector->isTablet()) {
            $device = self::DEVICE_TABLET;
        }

        return $device;
    }

    public function getUserIp()
    {
        $ip = $this->getServer('HTTP_CLIENT_IP');
        if (!empty($ip)) {
            return $ip;
        }

        $ip = $this->getServer('HTTP_X_FORWARDED_FOR');
        if (!empty($ip)) {
            return $ip;
        }

        $ip = $this->getServer('REMOTE_ADDR');
        if (!empty($ip)) {
            return $ip;
        }

        $ip = $this->getServer('REMOTE_HOST');
        if (!empty($ip)) {
            return $ip;
        }

        return '';
    }
}
