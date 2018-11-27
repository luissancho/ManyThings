<?php

namespace ManyThings\Controllers;

use ManyThings\Core\AdminPermissions;
use ManyThings\Core\AdminSections;
use ManyThings\Core\ArgumentException;
use ManyThings\Core\Controller;
use ManyThings\Core\Cron;
use ManyThings\Core\Op;
use ManyThings\Core\Utils;

class AdminController extends Controller
{
    protected $nav = 'index';
    protected $section = [];
    protected $sections = [
        'admin' => [],
        'model' => [],
        'controller' => [],
        'dashboard' => []
    ];
    protected $timezone;

    protected $modelTypes = ['model', 'admin'];
    protected $controllerTypes = ['controller', 'dashboard'];

    public function indexAction()
    {
        $this->initialize();

        $this->response->send('admin/index');
    }

    public function sectionAction($nav, $sub = 'list', $id = 0)
    {
        $this->initialize($nav);

        if ($this->section['permissions']['view'] != 1) {
            $this->response->redirect(DOMPATH . '/admin/');

            return;
        }

        if (in_array($this->section['type'], $this->modelTypes)) {
            switch ($sub) {
                case 'list':
                    $this->processList();
                    break;
                case 'details':
                    $this->processDetails($id);
                    break;
                case 'add':
                    $this->processAdd($id);
                    break;
                case 'edit':
                    $this->processEdit($id);
                    break;
            }
        } elseif (in_array($this->section['type'], $this->controllerTypes)) {
            $this->processController();
        } else {
            $this->response->redirect(DOMPATH . '/admin/');
        }
    }

    public function processList()
    {
        $className = $this->di->loader->getClassName($this->section['class']);
        $obj = new $className();
        $grid = $obj->getGridMeta();

        if ($grid['add'] && $this->section['permissions']['add'] != 1) {
            $grid['add'] = false;
        }
        if ($grid['edit'] && $this->section['permissions']['edit'] != 1) {
            $grid['edit'] = false;
        }
        if ($grid['export'] && $this->section['permissions']['export'] != 1) {
            $grid['export'] = false;
        }

        $htitle = $this->section['name'];
        $grid = json_encode($grid);

        $this->response->setParam('htitle', $htitle);
        $this->response->setParam('grid', $grid);

        $this->response->send('admin/list');
    }

    public function processDetails($id)
    {
        $className = $this->di->loader->getClassName($this->section['class']);
        $obj = new $className($id);
        $item = $obj->getDetailsItem($id);

        if (empty($item)) {
            $this->response->showMessage('Item does not exist.');

            return;
        }

        foreach ($item['meta']['fields'] as $key => $field) {
            if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                unset($item['meta']['fields'][$key]);
            }
        }

        if (!$this->response->templateExists('admin/sections/' . $this->nav)) {
            $this->response->setParam('nav', '');
        }

        $htitle = $obj->getTitle() . ' - ' . $this->section['name'];

        $this->response->setParam('htitle', $htitle);
        $this->response->setParam('item', $item);

        $this->response->send('admin/details');
    }

    public function processAdd($id)
    {
        if ($this->section['permissions']['add'] != 1) {
            $this->response->redirect(DOMPATH . '/admin/s/' . $this->nav . '/');

            return false;
        }

        $className = $this->di->loader->getClassName($this->section['class']);
        $obj = new $className();

        if (isset($obj->id)) {
            $form = $obj->getFormMeta('add', ['rel_id' => $id]);
        } else {
            $form = $obj->getFormMeta();
        }

        $action = $this->request->get('action');

        if ($action == 'add') {
            $data = $this->request->getPostAll();
            unset($data['action']);

            foreach ($form['fields'] as $field) {
                if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                    unset($data[$field['name']]);
                }
            }

            try {
                if (isset($obj->id)) {
                    $newId = $obj->addAction($data);
                } else {
                    $newId = $obj->AddItem($data);
                }

                $this->response->redirect(DOMPATH . '/admin/s/' . $this->nav . '/details/' . $newId . '/');

                return;
            } catch (ArgumentException $e) {
                $this->response->showErrors($e->getErrors());

                return;
            }
        }

        $data = [];
        foreach ($form['fields'] as $key => $field) {
            if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                unset($form['fields'][$key]);
            }

            $data[$field['name']] = $field['value'];
        }

        $htitle = $this->section['name'];

        $this->response->setParam('htitle', $htitle);
        $this->response->setParam('data', $data);
        $this->response->setParam('form', $form);
        $this->response->setParam('id', $id);

        $this->response->send('admin/add');
    }

    public function processEdit($id)
    {
        if ($this->section['permissions']['edit'] != 1) {
            $this->response->redirect(DOMPATH . '/admin/s/' . $this->nav . '/details/' . $id . '/');

            return false;
        }

        $className = $this->di->loader->getClassName($this->section['class']);
        $obj = new $className($id);

        if (isset($obj->id)) {
            $form = $obj->getFormMeta('edit');
        } else {
            $form = $obj->getFormMeta($id);
        }

        $action = $this->request->get('action');

        if ($action == 'edit') {
            $data = $this->request->getPostAll();
            unset($data['action']);

            foreach ($form['fields'] as $field) {
                if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                    unset($data[$field['name']]);
                }
            }

            try {
                if (isset($obj->id)) {
                    $obj->editAction($data);
                } else {
                    $obj->EditItem($id, $data);
                }

                $this->response->redirect(DOMPATH . '/admin/s/' . $this->nav . '/details/' . $id . '/');

                return;
            } catch (ArgumentException $e) {
                $this->response->showErrors($e->getErrors());

                return;
            }
        } elseif (!empty($action)) {
            $log = $this->request->get('log');

            $data = $this->request->getPostAll();
            unset($data['action']);
            unset($data['log']);

            foreach ($form['fields'] as $field) {
                if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                    unset($data[$field['name']]);
                }
            }

            try {
                $fn = Utils::snakeToCamelCase($action);
                if (isset($obj->id)) {
                    $response = $obj->performAction($fn, $data, boolval($log));
                } else {
                    $method = 'Action' . $fn;
                    $response = $obj->$method($id, $data);
                }

                $this->response->redirect(DOMPATH . '/admin/s/' . $this->nav . '/details/' . $id . '/');

                return;
            } catch (ArgumentException $e) {
                $this->response->showErrors($e->getErrors());

                return;
            }
        }

        $data = [];
        foreach ($form['fields'] as $key => $field) {
            if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                unset($form['fields'][$key]);
            }

            $data[$field['name']] = $field['value'];
        }

        $htitle = $obj->getTitle() . ' - ' . $this->section['name'];

        $this->response->setParam('htitle', $htitle);
        $this->response->setParam('data', $data);
        $this->response->setParam('form', $form);
        $this->response->setParam('id', $id);

        $this->response->send('admin/edit');
    }

    public function processController()
    {
        if ($this->section['permissions']['view'] != 1) {
            $this->response->redirect(DOMPATH . '/admin/');

            return false;
        }

        $htitle = $this->section['name'];

        $className = $this->di->loader->getClassName($this->section['class']);
        $obj = new $className();
        $form = $obj->getFormMeta();

        $action = $this->request->get('action');

        if ($action == 'action' || ($action == '' && count($form['fields']) == 0)) {
            $data = $this->request->getAll();
            unset($data['action']);

            foreach ($form['fields'] as $field) {
                if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                    unset($data[$field['name']]);
                }
            }

            try {
                $response = $obj->mainAction($data);

                if (!$this->response->templateExists('admin/sections/' . $this->nav)) {
                    $this->response->setParam('nav', '');
                }

                $this->response->setParam('htitle', $htitle);
                $this->response->setParam('response', $response);
                $this->response->setParam('action', $action);

                $this->response->send('admin/result');

                return;
            } catch (ArgumentException $e) {
                $this->response->showErrors($e->getErrors());

                return;
            }
        } elseif (!empty($action)) {
            $data = $this->request->getAll();
            unset($data['action']);

            foreach ($form['fields'] as $field) {
                if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                    unset($data[$field['name']]);
                }
            }

            try {
                $fn = Utils::snakeToCamelCase($action);
                $response = $obj->performAction($fn, $data, false);

                if (!$this->response->templateExists('admin/sections/' . $this->nav)) {
                    $this->response->setParam('nav', '');
                }

                $this->response->setParam('htitle', $htitle);
                $this->response->setParam('response', $response);
                $this->response->setParam('action', $action);

                $this->response->send('admin/result');

                return;
            } catch (ArgumentException $e) {
                $this->response->showErrors($e->getErrors());

                return;
            }
        }

        $data = [];
        foreach ($form['fields'] as $key => $field) {
            if ($field['admin'] && $this->section['permissions']['admin'] != 1) {
                unset($form['fields'][$key]);
            }

            $data[$field['name']] = $field['value'];
        }

        $this->response->setParam('htitle', $htitle);
        $this->response->setParam('data', $data);
        $this->response->setParam('form', $form);

        $this->response->send('admin/controller');
    }

    public function initialize($nav = 'index')
    {
        if (!$this->checkSecurityLevel(2)) {
            return;
        }

        ini_set('memory_limit', '1G');
        $this->response->preventCache();

        $this->nav = $nav;

        $permissions = AdminPermissions::getResultsByRole($this->di->session->roleId);
        $sections = AdminSections::getResults(['order' => 'tab, ord']);
        foreach ($sections as $section) {
            $section['permissions'] = $permissions[$section['id']];

            if ($section['permissions']['view'] == 1 && $section['permissions']['nav'] == 1) {
                $this->sections[$section['type']][$section['tab']][$section['ref']] = $section;
            }

            if ($nav == $section['ref']) {
                $this->section = $section;
            }
        }

        $this->di->session->section = $this->section;

        $this->timezone = $this->di->config->date->timezone;
        if ($this->di->session->user && $this->di->session->user['timezone']) {
            $this->timezone = $this->di->session->user['timezone'];
        }

        $this->response->setRobots(false, false);

        $this->response->setParam('nav', $this->nav);
        $this->response->setParam('section', $this->section);
        $this->response->setParam('sections', $this->sections);
        $this->response->setParam('timezone', $this->timezone);

        return $this;
    }

    public function cronAction()
    {
        if ($this->di->session->userIp != $this->request->getServer('SERVER_ADDR') &&
            !$this->checkSecurityLevel(2)) {
            return;
        }

        ini_set('max_input_time', -1);
        ini_set('default_socket_timeout', 600);
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $this->response->preventCache();

        $cron = new Cron();
        $message = $cron->execute();

        $this->response->setRobots(false, false);

        $this->response->setParam('message', nl2br($message));

        $this->response->send('admin/cron');
    }

    public function opAction()
    {
        if (!$this->checkSecurityLevel(2)) {
            return;
        }

        ini_set('max_input_time', -1);
        ini_set('default_socket_timeout', 600);
        ini_set('memory_limit', '1G');
        set_time_limit(0);
        $this->response->preventCache();

        $op = new Op();
        $results = $op->execute();

        $message = 'Resultados:<br /><br />';
        foreach ($results['data'] as $k => $v) {
            $itemName = $k;
            $itemValue = (!is_array($v)) ? $v : 'OK';
            $itemTime = round($results['times'][$k], 3);
            $message .= $itemName . ': ' . $itemValue . ' (' . $itemTime . ' sg)<br />';
        }
        $message .= '<br />Total: ' . round($results['times']['total'], 3) . ' sg';

        $this->response->showSuccess($message);
    }
}
