<?php

namespace ManyThings\Core;

use ManyThings\Core\Dal\CoreDal;

class ModelAdmin extends Model
{
    protected $request;
    protected $response;

    protected $admin;
    protected $titleCol;
    protected $timezone;

    // Admin Meta
    protected $meta = [
        'caption' => 'Section',
        'fields' => [
            'id' => [
                'name' => 'ID'
            ]
        ]
    ];

    // Admin Add & Edit Form
    protected $form = [
        'message' => '',
        'fields' => []
    ];

    public function __construct($id = null)
    {
        parent::__construct($id);

        $this->request = $this->di->request;
        $this->response = $this->di->response;

        $session = $this->di->session;
        $config = $this->di->config;

        if ($id && !$this->titleCol) {
            $this->titleCol = $this->dal->getIdCol();
        }

        $this->timezone = $config->date->timezone;
        if ($session->user && $session->user['timezone']) {
            $this->timezone = $session->user['timezone'];
        }
    }

    public function getTitle()
    {
        if (strstr($this->titleCol, '.') !== false) {
            $parts = explode('.', $this->titleCol);

            return $this->data[$parts[0]][$parts[1]];
        } elseif (array_key_exists($this->titleCol, $this->data)) {
            return $this->data[$this->titleCol];
        }

        return '';
    }

    public function getMeta($complete = false)
    {
        if (!isset($this->meta['processed'])) {
            $this->meta['key'] = $this->dal->getIdCol();
            $this->meta['section'] = $this->admin;

            // Main Data
            if (!isset($this->meta['sortname'])) {
                $this->meta['sortname'] = 'id';
            }
            if (!isset($this->meta['sortorder'])) {
                $this->meta['sortorder'] = 'desc';
            }
            if (!isset($this->meta['caption'])) {
                $this->meta['caption'] = 'Section';
            }
            if (!isset($this->meta['load'])) {
                $this->meta['load'] = true;
            }
            if (!isset($this->meta['edit'])) {
                $this->meta['edit'] = false;
            }
            if (!isset($this->meta['add'])) {
                $this->meta['add'] = false;
            }
            if (!isset($this->meta['export'])) {
                $this->meta['export'] = ['csv' => ''];
            }
            if (!isset($this->meta['menu'])) {
                $this->meta['menu'] = false;
            }

            // Fields
            $fields = [];

            foreach ($this->meta['fields'] as $key => $field) {
                if (strstr($key, '.') !== false) {
                    $key = str_replace('.', '__', $key);
                }

                if (!isset($field['id'])) {
                    $field['id'] = $key;
                }
                if (!isset($field['admin'])) {
                    $field['admin'] = false;
                }
                if (!isset($field['name'])) {
                    $field['name'] = Utils::snakeToWords($key);
                }
                if (!isset($field['width'])) {
                    $field['width'] = '60';
                }
                if (!isset($field['align'])) {
                    $field['align'] = 'center';
                }
                if (!isset($field['sortable'])) {
                    $field['sortable'] = true;
                }
                if (!isset($field['search'])) {
                    $field['search'] = true;
                }
                if (!isset($field['stype'])) {
                    $field['stype'] = 'text';
                }
                if (!isset($field['searchoptions'])) {
                    $field['searchoptions'] = '';
                }
                if (!isset($field['where'])) {
                    $field['where'] = null;
                }
                if (!isset($field['wheretype'])) {
                    $field['wheretype'] = null;
                }
                if (!isset($field['format'])) {
                    $field['format'] = '';
                }
                if (!isset($field['formatter'])) {
                    $field['formatter'] = '';
                }
                if (!isset($field['transformer'])) {
                    $field['transformer'] = null;
                }
                if (!isset($field['show_in_list'])) {
                    $field['show_in_list'] = true;
                }
                if (!isset($field['show_in_detail'])) {
                    $field['show_in_detail'] = true;
                }
                if (!isset($field['forward'])) {
                    $field['forward'] = false;
                }

                $fields[$key] = $field;
            }

            $this->meta['fields'] = $fields;

            if ($complete) {
                // Menu
                $menu = ['Details'];

                // Blocks
                $blocks = [];

                if (array_key_exists('blocks', $this->meta)) {
                    foreach ($this->meta['blocks'] as $name => $block) {
                        $blocks[$name] = [];
                        $menu[] = $name;

                        foreach ($block as $key => $field) {
                            if (strstr($key, '.') !== false) {
                                $key = str_replace('.', '__', $key);
                            }

                            if (!isset($field['id'])) {
                                $field['id'] = $key;
                            }
                            if (!isset($field['name'])) {
                                $field['name'] = Utils::snakeToWords($key);
                            }
                            if (!isset($field['width'])) {
                                $field['width'] = '60';
                            }
                            if (!isset($field['align'])) {
                                $field['align'] = 'center';
                            }
                            if (!isset($field['transformer'])) {
                                $field['transformer'] = null;
                            }
                            if (!isset($field['forward'])) {
                                $field['forward'] = false;
                            }

                            $blocks[$name][$key] = $field;
                        }
                    }
                }

                $this->meta['blocks'] = $blocks;

                // Relations
                $relations = [];

                if (array_key_exists('relations', $this->meta)) {
                    foreach ($this->meta['relations'] as $name => $relation) {
                        $menu[] = $relation['name'] ?: $name;

                        if (array_key_exists('fields', $relation)) {
                            foreach ($relation['fields'] as &$key) {
                                if (strstr($key, '.') !== false) {
                                    $key = str_replace('.', '__', $key);
                                }
                            }
                        }

                        if (!isset($relation['name'])) {
                            $relation['name'] = false;
                        }
                        if (!isset($relation['fields'])) {
                            $relation['fields'] = [];
                        }
                        if (!isset($relation['query'])) {
                            $relation['query'] = null;
                        }
                        if (!isset($relation['forward'])) {
                            $relation['forward'] = false;
                        }
                        if (!isset($relation['add'])) {
                            $relation['add'] = false;
                        }
                        if (!isset($relation['actions'])) {
                            $relation['actions'] = [];
                        }

                        foreach ($relation['actions'] as $act => $action) {
                            if (!isset($action['column'])) {
                                $action['column'] = 'id';
                            }
                            if (!isset($action['name'])) {
                                $action['name'] = Utils::snakeToWords($act);
                            }
                            if (!isset($action['vars'])) {
                                $action['vars'] = [];
                            }
                            if (!isset($action['input'])) {
                                $action['input'] = '';
                            } // confirm, prompt
                            if (!isset($action['prompt_name'])) {
                                $action['prompt_name'] = 'Data';
                            }
                            if (!isset($action['prompt_def'])) {
                                $action['prompt_def'] = '';
                            }
                            if (!isset($action['show'])) {
                                $action['show'] = true;
                            }
                            if (!isset($action['log'])) {
                                $action['log'] = false;
                            }

                            $relation['actions'][$act] = $action;
                        }

                        $relations[$name] = $relation;
                    }
                }

                $this->meta['relations'] = $relations;

                // Actions
                $actions = [];

                if (array_key_exists('actions', $this->meta)) {
                    foreach ($this->meta['actions'] as $act => $action) {
                        if (!isset($action['block'])) {
                            $action['block'] = 'main';
                        }
                        if (!isset($action['name'])) {
                            $action['name'] = Utils::snakeToWords($act);
                        }
                        if (!isset($action['vars'])) {
                            $action['vars'] = [];
                        }
                        if (!isset($action['input'])) {
                            $action['input'] = '';
                        } // confirm, prompt
                        if (!isset($action['prompt_name'])) {
                            $action['prompt_name'] = 'Data';
                        }
                        if (!isset($action['prompt_def'])) {
                            $action['prompt_def'] = '';
                        }
                        if (!isset($action['show'])) {
                            $action['show'] = true;
                        }
                        if (!isset($action['log'])) {
                            $action['log'] = false;
                        }

                        $actions[$action['block']][$act] = $action;
                    }
                }

                $this->meta['actions'] = $actions;

                // Extra
                if (array_key_exists('extra', $this->meta)) {
                    foreach ($this->meta['extra'] as $extra) {
                        $menu[] = $extra;
                    }
                }

                if ($this->meta['menu']) {
                    $this->meta['menu'] = $menu;
                }
            }

            $this->meta['processed'] = true;
        }

        return $this->meta;
    }

    public function getGridMeta()
    {
        $meta = $this->getMeta();

        $fields = [];

        foreach ($meta['fields'] as $key => $field) {
            if ($field['show_in_list']) {
                if ($field['stype'] == 'select' && $field['search']) {
                    $options = $this->getOptionsTransformed($field['transformer']);
                    if ($options) {
                        $opt = [];
                        foreach ($options as $k => $v) {
                            $opt[] = $k . ':' . $v;
                        }

                        $field['searchoptions'] = ':All;' . implode(';', $opt) . ';null:None';
                    }
                }

                unset($field['transformer']);
                unset($field['show_in_detail']);
                unset($field['show_in_list']);

                $fields[] = $field;
            }
        }

        $meta['fields'] = $fields;

        unset($meta['actions']);
        unset($meta['processed']);

        return $meta;
    }

    public function getForm()
    {
        if (!isset($this->form['processed'])) {
            $this->form['section'] = $this->admin;

            // Main Data
            if (!isset($this->form['message'])) {
                $this->form['message'] = '';
            }
            if (!isset($this->form['method'])) {
                $this->form['method'] = 'post';
            }
            if (!isset($this->form['relfield'])) {
                $this->form['relfield'] = null;
            }

            // Fields
            $fields = [];

            foreach ($this->form['fields'] as $key => $field) {
                if (strstr($key, '.') !== false) {
                    $key = str_replace('.', '__', $key);
                }

                if (!isset($field['id'])) {
                    $field['id'] = $key;
                }
                if (!isset($field['name'])) {
                    $field['name'] = $key;
                }
                if (!isset($field['label'])) {
                    $field['label'] = Utils::snakeToWords($key);
                }
                if (!isset($field['class'])) {
                    $field['class'] = 'std';
                }
                if (!isset($field['tip'])) {
                    $field['tip'] = '';
                }
                if (!isset($field['type'])) {
                    $field['type'] = 'text';
                }
                if (!isset($field['options'])) {
                    $field['options'] = null;
                }
                if (!isset($field['value'])) {
                    $field['value'] = '';
                }
                if (!isset($field['admin'])) {
                    $field['admin'] = false;
                }
                if (!isset($field['add'])) {
                    $field['add'] = true;
                }
                if (!isset($field['edit'])) {
                    $field['edit'] = true;
                }
                if (!isset($field['empty'])) {
                    $field['empty'] = null;
                }
                if (!isset($field['default'])) {
                    $field['default'] = null;
                }
                if (!isset($field['transformer'])) {
                    $field['transformer'] = null;
                }
                if (!isset($field['cast'])) {
                    $field['cast'] = null;
                }
                if (!isset($field['show'])) {
                    $field['show'] = true;
                }

                $fields[$key] = $field;
            }

            $this->form['fields'] = $fields;

            $this->form['processed'] = true;
        }

        return $this->form;
    }

    public function getFormMeta($action = '', $values = [])
    {
        $form = $this->getForm();
        $data = ($action == 'edit' && !empty($this->id)) ? $this->data : [];
        if ($action == 'add' && !empty($values['rel_id'])) {
            $values[$form['relfield']] = $values['rel_id'];
        }

        $fields = [];

        foreach ($form['fields'] as $key => $field) {
            if (strstr($key, '__') !== false && !array_key_exists($key, $data)) {
                $parts = explode('__', $key);
                $value = $data[$parts[0]][$parts[1]];
            } elseif (array_key_exists($key, $data)) {
                $value = $data[$key];
            } else {
                $value = '';
            }

            if ($field['type'] == 'select') {
                $field['options'] = $this->getOptionsTransformed($field['options']);
            }

            if ($action == 'add' && isset($values[$key])) {
                $field['value'] = $this->getValueTransformed($key, $field['transformer'], $values);
            } elseif ($action != 'edit' && isset($field['default'])) {
                if (is_array($field['default']) && isset($field['default']['method'])) {
                    $field['value'] = call_user_func_array([$this, $field['default']['method']], []);
                } else {
                    $field['value'] = $field['default'];
                }
            } else {
                $field['value'] = $this->getValueTransformed($key, $field['transformer'], $data);
            }

            if (is_array($field['show']) && isset($field['show']['method'])) {
                $field['show'] = call_user_func_array([$this, $field['show']['method']], [$data]);
            }

            $fields[] = $field;
        }

        $form['fields'] = $fields;

        unset($form['processed']);

        return $form;
    }

    public function getFilterWhere($filter)
    {
        $field = $filter->field;
        $meta = $this->getMeta();
        $metaField = $meta['fields'][$field];

        if ($metaField) {
            if ($this->dal->type == CoreDal::TYPE_SQL) {
                $prefix = 't.';

                if (strstr($field, '__') !== false) {
                    $parts = explode('__', $field);
                    $aliases = $this->dal->getJoinsAliases();
                    $prefix = $aliases[$parts[0]] . '.';
                    $field = $parts[1];
                }

                if ($metaField['where']) {
                    return str_ireplace('{value}', $filter->data, $metaField['where']);
                } elseif ($metaField['wheretype'] == 'like') {
                    return $prefix . $field . " LIKE '%" . $this->dal->dbEscape($filter->data) . "%'";
                } elseif ($metaField['wheretype'] == 'begin') {
                    return $prefix . $field . " LIKE '" . $this->dal->dbEscape($filter->data) . "%'";
                } elseif ($filter->data == CoreDal::OP_TRUE) {
                    return $prefix . $field . ' IS TRUE';
                } elseif ($filter->data == CoreDal::OP_FALSE) {
                    return $prefix . $field . ' IS FALSE';
                } elseif ($filter->data == CoreDal::OP_EMPTY) {
                    return $prefix . $field . ' IS NULL OR ' . $prefix . $field . " = ''";
                } elseif ($filter->data == CoreDal::OP_NOTEMPTY) {
                    return $prefix . $field . ' IS NOT NULL AND ' . $prefix . $field . " <> ''";
                } else {
                    return $prefix . $field . " = '" . $this->dal->dbEscape($filter->data) . "'";
                }
            } elseif ($this->dal->type == CoreDal::TYPE_API) {
                if ($metaField['where']) {
                    return str_ireplace('{value}', $filter->data, $metaField['where']);
                } elseif ($metaField['wheretype'] == 'like') {
                    return [
                        $field => [
                            '$regex' => $this->dal->dbEscape($filter->data),
                            '$options' => 'i'
                        ]
                    ];
                } elseif ($metaField['wheretype'] == 'begin') {
                    return [
                        $field => [
                            '$regex' => '^' . $this->dal->dbEscape($filter->data),
                            '$options' => 'i'
                        ]
                    ];
                } elseif ($filter->data == CoreDal::OP_TRUE) {
                    return [
                        $field => true
                    ];
                } elseif ($filter->data == CoreDal::OP_FALSE) {
                    return [
                        $field => false
                    ];
                } elseif ($filter->data == CoreDal::OP_EMPTY) {
                    return [
                        $field => [
                            '$in' => [null, '']
                        ]
                    ];
                } elseif ($filter->data == CoreDal::OP_NOTEMPTY) {
                    return [
                        $field => [
                            '$nin' => [null, '']
                        ]
                    ];
                } else {
                    return [
                        $field => $this->dal->dbEscape($filter->data)
                    ];
                }
            }
        }

        return '';
    }

    public function getOptionsTransformed($transformer)
    {
        if (empty($transformer)) {
            return false;
        }

        $data = [];

        switch ($transformer['type']) {
            case 'model':
                $modelName = $this->di->loader->getClassName($transformer['name']);
                $colName = $transformer['column_name'];
                $model = new $modelName();
                $meta = $model->getMeta();
                if (!$transformer['sort']) {
                    $transformer['sort'] = $meta['sortname'] . ' ' . $meta['sortorder'];
                }
                $items = $modelName::getResults([
                    'order' => $transformer['sort']
                ]);
                foreach ($items as $item) {
                    if (!$transformer['key']) {
                        $transformer['key'] = $model::getDal()->getIdCol();
                    }
                    $data[$item[$transformer['key']]] = $model->getValueTransformed($colName, $meta['fields'][$colName]['transformer'], $item);
                }
                break;
            case 'array':
                $arrayName = $transformer['name'];
                foreach ($this->{$arrayName} as $key => $val) {
                    $data[$key] = $val;
                }
                break;
            default:
                return false;
        }

        if (isset($transformer['filter'])) {
            foreach ($data as $key => $val) {
                $data[$key] = call_user_func_array([$this, $transformer['filter']], [$val]);
            }
        }

        if (empty($data)) {
            return false;
        }

        return $data;
    }

    public function getValueTransformed($key, $transformer, $data = null)
    {
        if (empty($data)) {
            $data = $this->data;
        }

        if (strstr($key, '__') !== false && !array_key_exists($key, $data)) {
            $parts = explode('__', $key);
            $value = $data[$parts[0]][$parts[1]];
        } elseif (array_key_exists($key, $data)) {
            $value = $data[$key];
        } else {
            $value = '';
        }

        if (empty($transformer)) {
            return (!is_null($value)) ? $value : '';
        }

        switch ($transformer['type']) {
            case 'model':
                $modelName = $this->di->loader->getClassName($transformer['name']);
                $colName = $transformer['column_name'];
                if (!$transformer['key']) {
                    $transformer['key'] = $modelName::getDal()->getIdCol();
                }
                $model = $modelName::getRowBy($transformer['key'], $value, true);
                if (!empty($model->id)) {
                    $meta = $model->getMeta();
                    $value = $model->getValueTransformed($colName, $meta['fields'][$colName]['transformer']);
                } else {
                    $value = '';
                }
                break;
            case 'array':
                $arrayName = $transformer['name'];
                $newValue = '';
                foreach ($this->{$arrayName} as $key => $val) {
                    if (($key === CoreDal::OP_TRUE && !empty($value))
                        || ($key === CoreDal::OP_FALSE && empty($value))
                        || ($key === CoreDal::OP_EMPTY && empty($value))
                        || ($key === CoreDal::OP_NOTEMPTY && !empty($value))
                        || strtolower($key) == strtolower($value)) {
                        $newValue = $val;
                        break;
                    }
                }
                $value = $newValue;
                break;
            case 'method':
                $value = call_user_func_array([$this, $transformer['name']], [$data]);
                break;
            case 'formatter':
                if (array_key_exists('name', $transformer)) {
                    if ($transformer['name'] == 'json') {
                        $value = Utils::jsonToText($value);
                    } elseif ($transformer['name'] == 'array') {
                        $value = Utils::arrayToText($value);
                    }
                }
                break;
            default:
                return $value;
        }

        if (!empty($transformer['filter'])) {
            $value = call_user_func_array([$this, $transformer['filter']], [$value]);
        }

        return $value;
    }

    public function getGridData($filters, $page, $rows, $sidx, $sord)
    {
        $meta = $this->getMeta();
        $items = [];
        $ids = [];

        // Initially empty if load is set to false
        if (is_null($filters) && !$meta['load']) {
            return [
                'count' => 0,
                'items' => []
            ];
        }

        // Active pagination
        if ($page > 0) {
            $total = $rows;
            $start = ($page - 1) * $total;
        } else {
            $total = 0;
            $start = 0;
        }

        if ($this->dal->type == CoreDal::TYPE_SQL) {
            $where = [];
            if ($filters) {
                foreach ($filters->rules as $filter) {
                    $where[] = $this->getFilterWhere($filter);
                }
            }
            $sqlWhere = implode(' AND ', $where);

            $tablePrefix = 't.';
            if (strstr($sidx, 'join_') !== false) {
                $tablePrefix = '';
            }
            $sqlOrder = ($sidx) ? $tablePrefix . $sidx . ' ' . $sord : '';

            $elems = $this->dal->getResults([
                'where' => $sqlWhere,
                'order' => $sqlOrder,
                'start' => $start,
                'limit' => $total
            ]);

            $count = $this->dal->getCount($sqlWhere);
        } elseif ($this->dal->type == CoreDal::TYPE_API) {
            $where = [];
            if ($filters) {
                foreach ($filters->rules as $filter) {
                    $where = array_merge($where, $this->getFilterWhere($filter));
                }
            }

            if ($sidx) {
                $order = $sord == 'desc' ? '-' : '';
                $order .= $sidx;
            }

            $elems = $this->dal->getResults([
                'where' => $where,
                'order' => $order,
                'start' => $start,
                'limit' => $total
            ]);

            $count = $this->dal->getCount($where);
        }

        // Get the ultimate array
        foreach ($elems as $elem) {
            $item = $this->getGridItem($elem);
            $items[] = $item;
        }

        $results = [
            'count' => $count,
            'items' => $items
        ];

        return $results;
    }

    public function getGridItem($mixed)
    {
        $meta = $this->getMeta();
        $item = [];

        if (is_numeric($mixed) || Utils::isHash($mixed)) {
            $this->data = static::get($mixed);
        } elseif (is_array($mixed)) {
            $this->data = $mixed;
        } else {
            $this->data = [];
        }

        foreach ($meta['fields'] as $key => $field) {
            if ($field['show_in_list']) {
                $item[$key] = $this->getValueTransformed($key, $field['transformer']);
                if (empty($item[$key])) {
                    $item[$key] = '-';
                }
            }
        }

        return $item;
    }

    public function getDetailsItem()
    {
        $loader = $this->di->loader;

        if (empty($this->id)) {
            return;
        }

        $meta = $this->getMeta(true);
        $relations = $this->dal->getRelations();

        // Fields
        $dataTransformed = [];
        foreach ($meta['fields'] as $key => $field) {
            if (!$field['show_in_detail']) {
                unset($meta['fields'][$key]);
                continue;
            }

            $dataTransformed[$key] = $this->getValueTransformed($key, $field['transformer']);

            if ($field['forward']) {
                if (is_array($field['forward']) && isset($field['forward']['method'])) {
                    $field['forward'] = call_user_func_array([$this, $field['forward']['method']], [$this->data]);
                }

                if ($field['forward']) {
                    $className = $loader->getClassName($field['forward']);
                    $relItem = new $className();
                    $relMeta = $relItem->getMeta();

                    $meta['fields'][$key]['forward'] = $relMeta['section'];
                }
            }
        }

        // Blocks
        $blocksTransformed = [];
        foreach ($meta['blocks'] as $name => $block) {
            $blocksTransformed[$name] = [];
            foreach ($block as $key => $field) {
                $blocksTransformed[$name][$key] = $this->getValueTransformed($key, $field['transformer']);

                if ($field['forward']) {
                    if (is_array($field['forward']) && isset($field['forward']['method'])) {
                        $field['forward'] = call_user_func_array([$this, $field['forward']['method']], [$this->data]);
                    }

                    if ($field['forward']) {
                        $className = $loader->getClassName($field['forward']);
                        $relItem = new $className();
                        $relMeta = $relItem->getMeta();

                        $meta['blocks'][$name][$key]['forward'] = $relMeta['section'];
                    }
                }
            }
        }

        // Relations
        $relationsTransformed = [];
        foreach ($meta['relations'] as $name => $relation) {
            $relationsTransformed[$name] = $relation;

            $className = $loader->getClassName($name);
            $relItem = new $className();
            $relMeta = $relItem->getMeta();

            $relationsTransformed[$name]['items'] = [];
            $relationsTransformed[$name]['actions'] = [];
            $relationsTransformed[$name]['meta'] = $relMeta;
            $relationsTransformed[$name]['type'] = $relations[$name]['type'];

            if (!$relation['name']) {
                $relationsTransformed[$name]['name'] = $relMeta['caption'];
            }

            if (!$relation['fields']) {
                $relationsTransformed[$name]['fields'] = array_keys($relMeta['fields']);
            }

            if ($relation['forward']) {
                $relationsTransformed[$name]['forward'] = $relMeta['section'];
            }

            if ($relation['add']) {
                $relationsTransformed[$name]['add'] = $relMeta['section'];
            }

            foreach ($relMeta['fields'] as $key => $field) {
                if ($field['forward'] && !is_array($field['forward'])) {
                    $className2 = $loader->getClassName($field['forward']);
                    $relItem2 = new $className2();
                    $relMeta2 = $relItem2->getMeta();

                    $relationsTransformed[$name]['meta']['fields'][$key]['forward'] = $relMeta2['section'];
                }
            }

            switch ($relations[$name]['type']) {
                case 'has_many':
                    $relData = $this->getRelationsData($name, $relation['query']);
                    $relationData = $relData;
                    break;
                case 'belongs_to':
                    $relData = $this->getRelationsData($name, $relation['query']);
                    if ($relData) {
                        $relationData = [$relData];
                        $relationsTransformed[$name]['add'] = false;
                    } else {
                        $relationData = [];
                    }
                    break;
            }

            foreach ($relationData as $n => $data) {
                $dataRelationTransformed = $data;

                foreach ($relationsTransformed[$name]['meta']['fields'] as $key => $field) {
                    if (!in_array($key, $relationsTransformed[$name]['fields'])) {
                        unset($relationsTransformed[$name]['meta']['fields'][$key]);
                        continue;
                    }

                    $dataRelationTransformed[$key] = $relItem->getValueTransformed($key, $field['transformer'], $data);
                }

                foreach ($relation['actions'] as $act => $action) {
                    foreach ($action as $option => $optionValue) {
                        if (is_array($optionValue) && isset($optionValue['method'])) {
                            $action[$option] = call_user_func_array([$this, $optionValue['method']], [$this->data, $data]);
                        }
                    }

                    if ($action['show']) {
                        $relationsTransformed[$name]['actions'][$n][$act] = $action;
                    }
                }

                $relationsTransformed[$name]['items'][$n]['data'] = $dataRelationTransformed;
                $relationsTransformed[$name]['items'][$n]['active'] = $relItem->isActive($data);
            }
        }

        // Actions
        foreach ($meta['actions'] as $block => $actions) {
            foreach ($actions as $act => $action) {
                foreach ($action as $option => $optionValue) {
                    if (is_array($optionValue) && isset($optionValue['method'])) {
                        $action[$option] = call_user_func_array([$this, $optionValue['method']], [$this->data]);
                    }
                }

                if ($action['show']) {
                    $meta['actions'][$block][$act] = $action;
                } else {
                    unset($meta['actions'][$block][$act]);
                }
            }

            if (empty($meta['actions'][$block])) {
                unset($meta['actions'][$block]);
            }
        }

        $item = [
            'id' => $this->id,
            'active' => $this->active,
            'htitle' => $this->id,
            'meta' => $meta,
            'data' => $dataTransformed,
            'blocks' => $blocksTransformed,
            'relations' => $relationsTransformed
        ];

        return $item;
    }

    public function addAction($data)
    {
        $values = [];

        $form = $this->getForm();

        foreach ($form['fields'] as $key => $field) {
            if ($field['add']) {
                $value = $this->getValueTransformed($key, $field['cast'], $data);
                $values[$key] = !empty($value) ? $value : $field['empty'];
            }
        }

        $this->create($values);

        AdminLogs::createEvent($this->class, $this->id, AdminLogs::ACTION_CREATE, $values);

        return $this->id;
    }

    public function editAction($data)
    {
        if ($this->id == 0) {
            return false;
        }

        $form = $this->getForm();

        $values = [];
        foreach ($form['fields'] as $key => $field) {
            if ($field['edit']) {
                if ($this->dal->type == CoreDal::TYPE_API && !array_key_exists($key, $this->data)) {
                    $this->data[$key] = null;
                }
                $value = $this->getValueTransformed($key, $field['cast'], $data);
                $values[$key] = !empty($value) ? $value : $field['empty'];
            }
        }

        $this->update($values);

        $changed = end($this->_changed);
        AdminLogs::createEvent($this->class, $this->id, AdminLogs::ACTION_UPDATE, $changed);

        return $changed;
    }

    public function deleteAction()
    {
        if ($this->id == 0) {
            return false;
        }

        $id = $this->id;

        $this->delete();

        AdminLogs::createEvent($this->class, $id, AdminLogs::ACTION_DELETE);

        return true;
    }

    public function performAction($action, $data, $log = false)
    {
        $method = lcfirst($action) . 'Action';
        if (method_exists($this, $method)) {
            $response = call_user_func_array([$this, $method], [$data]);
        } else {
            $method = 'Action' . $action;
            $response = call_user_func_array([$this, $method], [$this->id, $data]);
        }

        if ($log) {
            AdminLogs::createEvent($this->class, $this->id, $action, $data);
        }

        return $response;
    }
}
