<?php

namespace ManyThings\Core\Dal;

use ManyThings\Core\Utils;

class ModelApiDal extends ModelDal
{
    protected $checkCols = []; // Columns that must be part of model schema

    public function __construct($database = null)
    {
        if ($database) {
            $this->database = $database;
        }

        parent::__construct($this->database, self::TYPE_API);

        $this->validQuery = $this->getFilterQuery($this->validFilter);
        $this->activeQuery = $this->getFilterQuery($this->activeFilter);
    }

    public function getIds($query = null, $method = null)
    {
        $params = $this->setQueryParams($query);

        $url = $this->source . '/find';

        $results = $this->dbGetResults($url, $params, $method);

        $ids = [];
        foreach ($results as $row) {
            $ids[] = $row[$this->idCol];
        }

        return $ids;
    }

    public function getResults($query = null, $method = null)
    {
        $params = $this->setQueryParams($query);

        foreach ($this->relations as $name => $relation) {
            if (!empty($relation['alias'])) {
                $params['populate'] = $relation['alias'];
            }
        }

        $url = $this->source . '/find';

        $results = $this->dbGetResults($url, $params, $method);

        foreach ($results as $key => $row) {
            $results[$key] = $this->setRow($row);
        }

        return $results;
    }

    public function getRow($query = null, $inactives = true, $method = null)
    {
        if (!$query) {
            return [];
        }

        $raw = false;
        if ($method && $method == 'RAW') {
            $raw = true;
            $method = null;
        }

        $params = [];

        if (!$raw) {
            foreach ($this->relations as $name => $relation) {
                if (!empty($relation['alias'])) {
                    $params['populate'] = $relation['alias'];
                }
            }
        }

        $url = $this->source . '/' . $this->dbEscape($query['where'][$this->getIdCol()]);

        $row = $this->dbGetRow($url, $params, $method);

        if (!$raw && !empty($row)) {
            $row = $this->setRow($row);
        }

        return $row;
    }

    public function getCount($query = null, $method = null)
    {
        $params = $this->setQueryParams($query);

        $url = $this->source . '/count';

        $results = $this->dbGetResults($url, $params, $method);

        return intval($results['size']);
    }

    public function getRelationsData($id, $name = null, $query = null)
    {
        $relations = [];

        if (empty($this->relations)) {
            return [];
        }

        if (empty($name)) {
            foreach ($this->relations as $name => $relation) {
                $relations[] = $this->getRelationData($id, $name, $query);
            }
        } else {
            if (empty($this->relations[$name])) {
                return [];
            }

            $relations = $this->getRelationData($id, $name, $query);
        }

        return $relations;
    }

    public function countRelationsData($id, $name, $query = null)
    {
        if (empty($this->relations) || empty($name) || empty($this->relations[$name]) || $this->relations[$name]['type'] != 'has_many') {
            return 0;
        }

        $relation = $this->relations[$name];
        $className = $this->di->loader->getClassName($name);

        if (array_key_exists($relation['foreign_key'], $data)) {
            $items = $data[$relation['foreign_key']];
            if (!empty($items[0]) && isHashId($items[0])) {
                $where = [
                    '_id' => [
                        '$in' => implode(', ', $items)
                    ]
                ];
            } else {
                return $data[$relation['foreign_key']];
            }
        } else {
            $where = [
                $relation['foreign_key'] => $id
            ];
        }

        if (is_array($query)) {
            if (empty($query['where'])) {
                $query['where'] = $where;
            } else {
                $query['where'] = array_merge($query['where'], $where);
            }
        } else {
            $query = [
                'where' => $where
            ];
        }

        return $className::getCount($query);
    }

    protected function getRelationData($id, $name, $query = null)
    {
        $relation = $this->relations[$name];
        $className = $this->di->loader->getClassName($name);

        $data = $this->getRow([
            'where' => [
                $this->idCol => $id
            ]
        ]);

        if ($relation['type'] == 'has_many') {
            if (array_key_exists($relation['foreign_key'], $data)) {
                $items = $data[$relation['foreign_key']];
                if (!empty($items[0]) && Utils::isHash($items[0])) {
                    $where = [
                        '_id' => [
                            '$in' => implode(', ', $items)
                        ]
                    ];
                } else {
                    return $data[$relation['foreign_key']];
                }
            } elseif (is_array($relation['foreign_key'])) {
                $where = [];
                foreach ($relation['foreign_key'] as $key => $field) {
                    $where[$key] = $data[$field];
                }
            } else {
                $where = [
                    $relation['foreign_key'] => $id
                ];
            }

            if (is_array($query)) {
                if (empty($query['where'])) {
                    $query['where'] = $where;
                } else {
                    $query['where'] = array_merge($query['where'], $where);
                }
            } else {
                $query = [
                    'where' => $where
                ];
            }

            return $className::getResults($query);
        } elseif ($relation['type'] == 'belongs_to') {
            if (array_key_exists($relation['alias'], $data)) {
                return $data[$relation['alias']];
            }

            if ($data[$relation['local_key']]) {
                return $className::get($data[$relation['local_key']]);
            }

            return [];
        }

        return [];
    }

    public function create($values)
    {
        $url = $this->source;

        $result = $this->dbInsert($url, $values);

        return $result[$this->idCol];
    }

    public function update($id, $values)
    {
        $url = $this->source . '/' . $this->dbEscape($id);

        return $this->dbUpdate($url, $values);
    }

    public function delete($id)
    {
        $url = $this->source . '/' . $this->dbEscape($id);

        return $this->dbDelete($url);
    }

    protected function setRow($row)
    {
        foreach ($this->checkCols as $column) {
            if (!array_key_exists($column, $row)) {
                $row[$column] = null;
            }
        }

        foreach ($this->relations as $name => $relation) {
            if ($relation['type'] == 'belongs_to' && !empty($relation['alias'])) {
                if (array_key_exists($relation['local_key'], $row)) {
                    $row[$relation['alias']] = $row[$relation['local_key']];
                    if ($relation['local_key'] != $relation['alias']) {
                        if (array_key_exists('_id', $row[$relation['local_key']])) {
                            $row[$relation['local_key']] = $row[$relation['local_key']]['_id'];
                        } else {
                            unset($row[$relation['local_key']]);
                        }
                    }
                } else {
                    $className = $this->di->loader->getClassName($name);
                    $row[$relation['alias']] = $className::get($row[$relation['local_key']]);
                }
            }
        }

        return $row;
    }

    public function setValidQuery($filter = null)
    {
        if (is_null($filter)) {
            $filter = $this->validFilter;
        }

        $this->validQuery = $this->getFilterQuery($filter);

        return $this;
    }

    public function setActiveQuery($filter = null)
    {
        if (is_null($filter)) {
            $filter = $this->activeFilter;
        }

        $this->activeQuery = $this->getFilterQuery($filter);

        return $this;
    }

    protected function getFilterQuery($filter)
    {
        if (!$filter) {
            return [];
        }

        switch ($filter['op']) {
            case self::OP_EQ:
                return [
                    $filter['field'] => $filter['value']
                ];
            case self::OP_NE:
                return [
                    $filter['field'] => [
                        '$ne' => $filter['value']
                    ]
                ];
            case self::OP_GT:
                return [
                    $filter['field'] => [
                        '$gt' => $filter['value']
                    ]
                ];
            case self::OP_GE:
                return [
                    $filter['field'] => [
                        '$gte' => $filter['value']
                    ]
                ];
            case self::OP_LT:
                return [
                    $filter['field'] => [
                        '$lt' => $filter['value']
                    ]
                ];
            case self::OP_LE:
                return [
                    $filter['field'] => [
                        '$lte' => $filter['value']
                    ]
                ];
            case self::OP_IN:
                return [
                    $filter['field'] => [
                        '$in' => $filter['value']
                    ]
                ];
            case self::OP_LIKE:
                return [
                    $filter['field'] => [
                        '$regex' => $filter['value'],
                        '$options' => 'i'
                    ]
                ];
            case self::OP_TRUE:
                return [
                    $filter['field'] => true
                ];
            case self::OP_FALSE:
                return [
                    $filter['field'] => false
                ];
            case self::OP_EMPTY:
                return [
                    $filter['field'] => [
                        '$in' => [null, '']
                    ]
                ];
            case self::OP_NOTEMPTY:
                return [
                    $filter['field'] => [
                        '$nin' => [null, '']
                    ]
                ];
        }

        return [];
    }

    protected function setQueryParams($query = null, $inactives = false)
    {
        $params =
        [
            'filter' => '',
            'offset' => '0',
            'limit' => '0',
            'sort' => ''
        ];
        $where = [];

        if (is_array($query)) {
            if (!empty($query['check'])) {
                foreach ($query['check'] as $col) {
                    $this->checkCols[] = $col;
                }
            }

            if (is_array($query['where'])) {
                $where = $query['where'];
            } else {
                $where = $query;
            }

            if (!empty($query['start'])) {
                $params['offset'] = $query['start'];
            }

            if (!empty($query['limit'])) {
                $params['limit'] = $query['limit'];
            }

            if (!empty($query['order'])) {
                $params['sort'] = $query['order'];
            } else {
                unset($params['sort']);
            }
        }

        if ($this->validQuery) {
            if (!array_key_exists(key($this->validQuery), $where)) {
                $where = array_merge($where, $this->validQuery);
            }
        }

        if (!$inactives && $this->activeQuery) {
            if (!array_key_exists(key($this->activeQuery), $where)) {
                $where = array_merge($where, $this->activeQuery);
            }
        }

        if ($where) {
            $params['filter'] = json_encode($where);
        } else {
            unset($params['filter']);
        }

        return $params;
    }
}
