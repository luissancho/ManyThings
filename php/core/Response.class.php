<?php

namespace ManyThings\Core;

class Response extends Core
{
    protected $handler; // ie. Smarty
    protected $tpl; // Template path
    protected $headers = [];
    protected $redirect = false;

    protected $statusCodes = [
        200 => 'OK',
        301 => 'Moved Permanently',
        302 => 'Found',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        403 => 'Forbidden',
        404 => 'Not Found',
        500 => 'Internal Server Error'
    ];

    public function __construct($handler)
    {
        parent::__construct();

        $this->handler = $handler;
        $this->init();
    }

    public function __clone()
    {
        $this->cleanup();
    }

    protected function init()
    {
        $config = $this->di->config;
        $request = $this->di->request;
        $session = $this->di->session;

        $this->cleanup();

        $siteName = $config->app->site_name;

        $this->setParam('path', $request->domPath);
        $this->setParam('relpath', $request->domRelPath);
        $this->setParam('relurl', $request->relUri);
        $this->setParam('session', $session->toArray());
        $this->setParam('online', $config->app->online);
        $this->setParam('sitename', $siteName);
        $this->setParam('title', $siteName);
        $this->setParam('description', $siteName);
        $this->setParam('keywords', '');
        $this->setParam('robots', '');

        $this->tpl = $request->get('tpl');

        return $this;
    }

    public function cleanup()
    {
        $this->handler->clearAllAssign();
        $this->headers = [];

        return $this;
    }

    public function setParam($key, $value)
    {
        $this->handler->assign($key, $value);

        return $this;
    }

    public function getTemplate()
    {
        return $this->tpl;
    }

    public function setTemplate($tpl, $canonical = true)
    {
        $this->tpl = $tpl;

        if (!$canonical) {
            $this->setParam('relurl', str_replace($tpl . '/', '', $this->di->request->relUri));
        }

        return $this;
    }

    public function setTitle($value)
    {
        $this->handler->assign('title', $value);

        return $this;
    }

    public function setDescription($value)
    {
        $this->handler->assign('description', $value);

        return $this;
    }

    public function setKeywords($value)
    {
        $this->handler->assign('keywords', $value);

        return $this;
    }

    public function setRobots($index = true, $follow = true)
    {
        $value = 'index, follow';

        if (!$index) {
            $value = str_replace('index', 'noindex', $value);
        }

        if (!$follow) {
            $value = str_replace('follow', 'nofollow', $value);
        }

        $this->setParam('robots', $value);

        return $this;
    }

    public function setLanguage($locale)
    {
        $this->handler->setLanguage($locale);

        return $this;
    }

    public function getHeader($key)
    {
        return $this->headers[$key];

        return $this;
    }

    public function setHeader($key, $value = null)
    {
        if (!is_null($value)) {
            $this->headers[$key] = $value;
        } else {
            unset($this->headers[$key]);
        }

        return $this;
    }

    public function sendHeaders()
    {
        foreach ($this->headers as $key => $val) {
            header($key . ': ' . $val);
        }

        return $this;
    }

    public function redirect($url, $statusCode = 302)
    {
        if ($this->redirect) {
            return $this;
        }

        $this->setStatusCode($statusCode);
        $this->setHeader('Location', $url);

        $this->sendHeaders();

        $this->redirect = true;

        return $this;
    }

    public function sendFile($filePath, $contentType = null)
    {
        if ($this->redirect) {
            return $this;
        }

        if (is_null($contentType)) {
            $contentType = 'application/octet-stream';
        }

        $fileName = end(explode('/', $filePath));

        $this
            ->setContentType($contentType)
            ->setHeader('Content-Description', 'File Transfer')
            ->setHeader('Content-Disposition', 'attachment; filename=' . $fileName)
            ->setHeader('Content-Transfer-Encoding', 'binary')
            ->setHeader('Content-Length', filesize($filePath));

        $this->sendHeaders();

        @readfile($filePath);

        return $this;
    }

    public function sendPixel()
    {
        if ($this->redirect) {
            return $this;
        }

        $this->setContentType('image/png');

        $this->sendHeaders();

        $im = imagecreate(1, 1);
        $bg = imagecolorallocate($im, 255, 255, 255);
        imagecolortransparent($im, $bg);
        imagesetpixel($im, 1, 1, $bg);
        imagepng($im);
        imagedestroy($im);

        return $this;
    }

    public function sendRaw($content)
    {
        if ($this->redirect) {
            return $this;
        }

        if (is_numeric($content)) {
            $this->setStatusCode($content);
            $content = $content . ' ' . $this->statusCodes[$content];
        }

        $this->setContentType('text/plain', 'utf-8');

        $this->sendHeaders();

        echo $content;

        return $this;
    }

    public function sendJson($content)
    {
        if ($this->redirect) {
            return $this;
        }

        if (is_numeric($content)) {
            $this->setStatusCode($content);
            $content = [
                'status' => 'error',
                'message' => $content . ' ' . $this->statusCodes[$content]
            ];
        }

        $this->setContentType('application/json', 'utf-8');

        $this->sendHeaders();

        echo json_encode($content);

        return $this;
    }

    public function sendXml($content)
    {
        if ($this->redirect) {
            return $this;
        }

        if (is_numeric($content)) {
            $this->setStatusCode($content);
            $content = [
                'status' => 'error',
                'message' => $content . ' ' . $this->statusCodes[$content]
            ];
        }

        $this->setContentType('application/xml', 'utf-8');

        $this->sendHeaders();

        echo $content;

        return $this;
    }

    public function send($template, $contentType = 'text/html', $charset = 'utf-8')
    {
        if ($this->redirect) {
            return $this;
        }

        if (is_numeric($template)) {
            $this->setStatusCode($template);
        }

        $this->setContentType($contentType, $charset);

        $this->sendHeaders();

        echo $this->fetch($template);

        return $this;
    }

    public function fetch($template)
    {
        $tpl = $template . '.tpl';
        if ($this->tpl) {
            $tpl = $this->tpl . '/' . $tpl;
        }

        return $this->handler->fetch($tpl);
    }

    public function templateExists($template)
    {
        $tpl = $template . '.tpl';
        if ($this->tpl) {
            $tpl = $this->tpl . '/' . $tpl;
        }

        return $this->handler->templateExists($tpl);
    }

    public function preventCache()
    {
        $this
            ->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT')
            ->setHeader('Last-Modified', gmdate('D, d M Y H:i:s') . 'GMT')
            ->setHeader('Cache-Control', 'no-cache, must-revalidate')
            ->setHeader('Pragma', 'no-cache');

        return $this;
    }

    public function setStatusCode($code)
    {
        $this->setHeader('Status', $code . ' ' . $this->statusCodes[$code]);

        return $this;
    }

    public function setContentType($contentType, $charset = null)
    {
        if (!is_null($charset)) {
            $this->setHeader('Content-Type', $contentType . '; charset=' . $charset);
        } else {
            $this->setHeader('Content-Type', $contentType);
        }

        return $this;
    }

    public function showSuccess($message, $url = '', $ulink = '')
    {
        if ($this->redirect) {
            return $this;
        }

        $this->setRobots(false, false);

        $this->setParam('message', $message);
        $this->setParam('url', $url);
        $this->setParam('ulink', $ulink);

        $this->send('success');

        return $this;
    }

    public function showMessage($message)
    {
        if ($this->redirect) {
            return $this;
        }

        $this->setRobots(false, false);

        $this->setParam('message', $message);

        $this->send('message');

        return $this;
    }

    public function showErrors($errors)
    {
        if ($this->redirect) {
            return $this;
        }

        $this->setRobots(false, false);

        $this->setParam('errors', $errors);

        $this->send('errors');

        return $this;
    }
}
