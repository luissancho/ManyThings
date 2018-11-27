<?php

namespace ManyThings\Core;

class Controller extends Core
{
    protected $request;
    protected $response;

    protected $action;
    protected $params;

    public function __construct()
    {
        parent::__construct();

        $this->request = $this->di->request;
        $this->response = $this->di->response;
    }

    public function dispatch($action, $params)
    {
        $this->action = $action;
        $this->params = $params;

        $this->beforeDispatch();

        call_user_func_array([$this, $this->action], $this->params);

        $this->afterDispatch();

        return $this;
    }

    protected function beforeDispatch()
    {
        return $this;
    }

    protected function afterDispatch()
    {
        return $this;
    }

    public function notFoundAction()
    {
        $this->response->setRobots(false, false);

        $this->response->send(404);
    }

    public function errorAction($e)
    {
        $this->response->setRobots(false, false);

        $this->response->setParam('message', $this->getErrorMessage($e));

        $this->response->send('message');
    }

    public function getJsonData($json)
    {
        return json_decode(stripslashes(trim($json)), true);
    }

    public function checkSecurityLevel($userLevel, $url = '', $control = 'login')
    {
        $session = $this->di->session;

        if ($session->level < $userLevel) {
            if ($session->level > 0) {
                $this->response->send(403);

                return false;
            }

            // Page that controls action
            if ($control == 'login') {
                $url = $url != '' ? $url : $this->request->relUri;
            }

            $url = str_replace('.php', '', $url);

            $this->response->redirect(DOMPATH . '/' . $control . '/' . $url);

            return false;
        }

        return true;
    }

    protected function getErrorMessage($e)
    {
        if ($e instanceof AppException) {
            return $e->getMessage();
        }

        return AppException::handle($e);
    }
}
