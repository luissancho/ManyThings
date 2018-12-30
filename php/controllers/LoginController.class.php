<?php

namespace ManyThings\Controllers;

use ManyThings\Core\ArgumentException;
use ManyThings\Core\Controller;
use ManyThings\Core\Users;
use ManyThings\Core\Utils;

class LoginController extends Controller
{
    public function loginAction($path = null)
    {
        $session = $this->di->session;

        $auth = $this->request->get('auth');

        if (!empty($auth)) {
            $userId = $session->logAuth($auth);

            $this->response->redirect(DOMPATH . '/' . $path);

            return;
        }

        $this->response->setRobots(false, false);

        $this->response->setParam('url', $path);

        if ($this->response->templateExists('login')) {
            $this->response->send('login');
        } else {
            $this->response->send('admin/login');
        }
    }

    public function loginHandlerAction()
    {
        $session = $this->di->session;

        $path = $this->request->getPost('url');

        if ($session->level == 0) {
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            $autologin = $this->request->getPost('autologin') ? true : false;

            try {
                $userId = $session->login($email, $password, $autologin);
            } catch (ArgumentException $e) {
                $this->response->showErrors($e->getErrors());

                return;
            }
        }

        $this->response->redirect(DOMPATH . '/' . $path);
    }

    public function logoutAction()
    {
        $session = $this->di->session;

        if ($session->level > 0) {
            $result = $session->logout();
        }

        $this->response->redirect(DOMPATH . '/');
    }

    public function passwordAction()
    {
        $this->response->setRobots(false, false);

        if ($this->response->templateExists('password')) {
            $this->response->send('password');
        } else {
            $this->response->send('admin/password');
        }
    }

    public function passwordHandlerAction()
    {
        $config = $this->di->config;
        $session = $this->di->session;

        $email = $this->request->getPost('email');

        $user = Users::getRowBy('email', $email, true);

        if (empty($user)) {
            $this->response->showErrors([_T('Email does not exist.')]);

            return;
        }

        $auth = $session->createAuth($user->id);

        $emailfrom = $config->app->email;
        $emailData = [
            'name' => $user->data['username'],
            'auth' => $auth
        ];

        Utils::sendEmail('password', $user->data['email'], $emailfrom, $emailData, _T('Your new password for ') . $config->app->site_name);

        $this->response->showSuccess('Email sent.');
    }
}
