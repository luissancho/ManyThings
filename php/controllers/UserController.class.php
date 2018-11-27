<?php

namespace ManyThings\Controllers;

use ManyThings\Core\ArgumentException;
use ManyThings\Core\Users;

class UserController extends BaseController
{
    public function accountAction()
    {
        $session = $this->di->session;

        if (empty($session->uid)) {
            $auth = $this->request->get('auth');

            if (!empty($auth)) {
                $session->logAuth($auth);

                $this->response->redirect(DOMPATH . '/user/');

                return;
            }
        }

        $this->response->setRobots(false, false);

        $this->response->setParam('data', $session->user);

        $this->response->send('user');
    }

    public function accountHandlerAction()
    {
        $data =
        [
            'username' => '',
            'email' => '',
            'password' => ''
        ];
        $data = $this->request->getPostData($data);

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user = new Users($this->di->session->uid);

        try {
            $user->update($data);
        } catch (ArgumentException $e) {
            $this->response->showErrors($e->getErrors());

            return;
        }

        $this->response->showSuccess('Data succesfully updated.');
    }
}
