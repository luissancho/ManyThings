<?php

namespace ManyThings\Controllers;

use ManyThings\Core\AdminPermissions;
use ManyThings\Core\AdminSections;
use ManyThings\Core\Controller;
use ManyThings\Core\Utils;

class ApiController extends Controller
{
    public function gridAction($grid, $format = null)
    {
        $page = $this->request->get('page');
        $rows = $this->request->get('rows');
        $sidx = $this->request->get('sidx');
        $sord = $this->request->get('sord');

        $search = ($this->request->get('_search') == 'true');
        $filters = ($search) ? json_decode($this->request->get('filters')) : [];

        $section = AdminSections::getRowBy('ref', $grid);
        $section['permissions'] = AdminPermissions::getRow([
            'where' => 't.section_id = :sectionId: AND t.role_id = :roleId:',
            'bind' => [
                'sectionId' => $section['id'],
                'roleId' => $this->di->session->roleId
            ]
        ]);

        if ($section['permissions']['view'] != 1) {
            if (!empty($format)) {
                $this->response->sendJson(['filename' => '']);
            } else {
                $this->response->sendJson([
                    'page' => 1,
                    'total' => 1,
                    'records' => 0,
                    'rows' => []
                ]);
            }

            return;
        }

        $className = $this->di->loader->getClassName($section['class']);
        $obj = new $className();

        if (!empty($format)) {
            $results = $obj->getGridData($filters, 0, 0, $sidx, $sord);

            $items = $results['items'];

            if ($format == 'csv') {
                $fileName = 'section-' . $grid . '.csv';
                $filePath = FILES_PATH . $fileName;
                $fh = @fopen($filePath, 'w');

                @fputcsv($fh, array_keys($items[0]), ';');
                foreach ($items as $item) {
                    foreach ($item as $k => $v) {
                        $item[$k] = Utils::formatExcel($v);
                    }
                    @fputcsv($fh, $item, ';');
                }

                @fclose($fh);
            } elseif ($format == 'zip') {
                $fileName = 'section-' . $grid . '.zip';
                $filePath = FILES_PATH . $fileName;

                $obj->zipExport($items, $filePath);
            }

            $this->response->sendJson([
                'filename' => $fileName
            ]);
        } else {
            $results = $obj->getGridData($filters, $page, $rows, $sidx, $sord);

            $items = $results['items'];
            $count = $results['count'];
            $pages = ceil($count / $rows);

            $this->response->sendJson([
                'page' => $page,
                'total' => $pages,
                'records' => $count,
                'rows' => $items
            ]);
        }
    }

    public function dashboardAction($dashboard)
    {
        ini_set('max_input_time', -1);
        ini_set('default_socket_timeout', 600);
        ini_set('memory_limit', '1G');
        set_time_limit(0);

        $data = $this->request->getAll();

        $section = AdminSections::getRowBy('ref', $dashboard);
        $section['permissions'] = AdminPermissions::getRow([
            'where' => 't.section_id = :sectionId: AND t.role_id = :roleId:',
            'bind' => [
                'sectionId' => $section['id'],
                'roleId' => $this->di->session->roleId
            ]
        ]);

        if ($section['permissions']['export'] != 1) {
            $this->response->sendJson(['status' => 'error']);

            return;
        }

        $className = $this->di->loader->getClassName($section['class']);
        $obj = new $className();
        $results = $obj->dashboardInfo($data);

        $fileName = 'section-' . $dashboard . '.csv';
        $filePath = FILES_PATH . $fileName;
        $fh = @fopen($filePath, 'w');

        @fputcsv($fh, $results['header'], ';');
        foreach ($results['data'] as $item) {
            foreach ($item as $k => $v) {
                $item[$k] = Utils::formatExcel($v);
            }
            @fputcsv($fh, $item, ';');
        }

        @fclose($fh);

        $this->response->sendJson([
            'filename' => $fileName
        ]);
    }

    public function notFoundAction()
    {
        $this->response->sendJson(404);
    }

    public function errorAction($e)
    {
        $this->response->sendJson([
            'status' => 'error',
            'message' => $this->getErrorMessage($e)
        ]);
    }
}
