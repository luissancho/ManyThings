<?php

namespace ManyThings\Core\Dal;

use ManyThings\Core\Dates;

class ModelDal extends CoreDal
{
    protected $source = '';
    protected $idCol = 'id';
    protected $createCol = null;
    protected $updateCol = null;
    protected $validFilter = []; // Entity elements (inside the table)
    protected $activeFilter = []; // Active elements (not deleted)
    protected $relations = null;

    protected $validQuery = '';
    protected $activeQuery = '';

    public function __construct($database = null, $type = self::TYPE_SQL)
    {
        if ($database) {
            $this->database = $database;
        }
        if ($type) {
            $this->type = $type;
        }

        parent::__construct($this->database, $this->type);

        $this->validQuery = $this->getFilterQuery($this->validFilter);
        $this->activeQuery = $this->getFilterQuery($this->activeFilter);
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getIdCol()
    {
        return $this->idCol;
    }

    public function getRelations()
    {
        return $this->relations;
    }

    public function getActiveFilter()
    {
        return $this->activeFilter;
    }

    public function getColumns()
    {
        return $this->dbGetColumns($this->source);
    }

    public function getChangeableColumns()
    {
        $changeable = [];

        $columns = $this->getColumns();

        foreach ($columns as $col) {
            if (!in_array($col, [$this->idCol, $this->createCol, $this->updateCol])) {
                $changeable[] = $col;
            }
        }

        return $changeable;
    }

    public function getIds($query = null)
    {
        $params = $this->setQueryParams($query);

        $sql = 'SELECT t.' . $this->idCol . '
                FROM ' . $this->getTableSql() . '
                ' . $this->getJoinsSql();
        $sql .= (!empty($params['where'])) ? ' WHERE ' . $params['where'] : '';
        //$sql .= ' GROUP BY t.' . $this->idCol;
        $sql .= (!empty($params['order'])) ? ' ORDER BY ' . $params['order'] : ' ORDER BY t.' . $this->idCol;
        $sql .= (!empty($params['limit'])) ? ' LIMIT ' . $params['limit'] : '';
        $sql .= (!empty($params['offset'])) ? ' OFFSET ' . $params['offset'] : '';

        $results = $this->dbGetResults($sql);

        $ids = [];
        foreach ($results as $row) {
            $ids[] = $row[$this->idCol];
        }

        return $ids;
    }

    public function getResults($query = null)
    {
        $params = $this->setQueryParams($query);

        $sql = 'SELECT ' . $params['columns'] . '
                FROM ' . $this->getTableSql() . '
                ' . $this->getJoinsSql();
        $sql .= (!empty($params['where'])) ? ' WHERE ' . $params['where'] : '';
        //$sql .= ' GROUP BY t.' . $this->idCol;
        $sql .= (!empty($params['order'])) ? ' ORDER BY ' . $params['order'] : ' ORDER BY t.' . $this->idCol;
        $sql .= (!empty($params['limit'])) ? ' LIMIT ' . $params['limit'] : '';
        $sql .= (!empty($params['offset'])) ? ' OFFSET ' . $params['offset'] : '';

        $results = $this->dbGetResults($sql);

        foreach ($results as $key => $row) {
            $results[$key] = $this->setRow($row);
        }

        return $results;
    }

    public function getRow($query = null, $inactives = true)
    {
        if (!$query) {
            return [];
        }

        $params = $this->setQueryParams($query, $inactives);

        $sql = 'SELECT ' . $params['columns'] . '
                FROM ' . $this->getTableSql() . '
                ' . $this->getJoinsSql();
        $sql .= (!empty($params['where'])) ? ' WHERE ' . $params['where'] : '';
        //$sql .= ' GROUP BY t.' . $this->idCol;
        $sql .= (!empty($params['order'])) ? ' ORDER BY ' . $params['order'] : ' ORDER BY t.' . $this->idCol;
        $sql .= ' LIMIT 1';

        $row = $this->setRow($this->dbGetRow($sql));

        return $row;
    }

    public function getCount($query = null)
    {
        $params = $this->setQueryParams($query);

        $sql = 'SELECT COUNT(t.' . $this->idCol . ')
                FROM ' . $this->getTableSql() . '
                ' . $this->getJoinsSql();
        $sql .= (!empty($params['where'])) ? ' WHERE ' . $params['where'] : '';

        return intval($this->dbGetVar($sql));
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

    protected function getRelationData($id, $name, $query = null)
    {
        $relation = $this->relations[$name];
        $className = $this->di->loader->getClassName($name);

        if ($relation['type'] == 'has_many') {
            $where = 't.' . $relation['foreign_key'] . " = ':id:'";

            if (is_array($query)) {
                if (empty($query['where'])) {
                    $query['where'] = $where;
                } else {
                    $query['where'] .= ' AND ' . $where;
                }

                if (empty($query['bind'])) {
                    $query['bind'] = ['id' => $id];
                } else {
                    $query['bind']['id'] = $id;
                }
            } elseif (is_string($query) && !empty($query)) {
                $query['where'] .= ' AND ' . $where;
                $query['bind'] = ['id' => $id];
            } else {
                $query = [
                    'where' => $where,
                    'bind' => ['id' => $id]
                ];
            }

            return $className::getResults($query);
        } elseif ($relation['type'] == 'belongs_to') {
            $where = 't.' . $this->idCol . " = ':id:'";

            if (is_array($query)) {
                if (empty($query['where'])) {
                    $query['where'] = $where;
                } else {
                    $query['where'] .= ' AND ' . $where;
                }

                if (empty($query['bind'])) {
                    $query['bind'] = ['id' => $id];
                } else {
                    $query['bind']['id'] = $id;
                }
            } elseif (is_string($query) && !empty($query)) {
                $query['where'] .= ' AND ' . $where;
                $query['bind'] = ['id' => $id];
            } else {
                $query = [
                    'where' => $where,
                    'bind' => ['id' => $id]
                ];
            }

            $data = $this->getRow($query, false);

            return $className::get($data[$relation['local_key']]);
        }

        return [];
    }

    public function create($values)
    {
        $columns = $this->getColumns();

        $colNames = [];
        $colValues = [];
        foreach ($columns as $col) {
            if (array_key_exists($col, $values)) {
                $colNames[] = '`' . $col . '`';
                $colValues[] = (!is_null($values[$col])) ? "'" . $this->dbEscape($values[$col]) . "'" : 'NULL';
            }
        }

        if (empty($colNames)) {
            return 0;
        }

        if (!array_key_exists($this->createCol, $values) && !empty($this->createCol)) {
            $colNames[] = '`' . $this->createCol . '`';
            $colValues[] = "'" . Dates::sqlNow() . "'";
        }

        if (!array_key_exists($this->updateCol, $values) && !empty($this->updateCol)) {
            $colNames[] = '`' . $this->updateCol . '`';
            $colValues[] = "'" . Dates::sqlNow() . "'";
        }

        $sql = 'INSERT INTO ' . $this->source . ' (';
        $sql .= implode(',', $colNames);
        $sql .= ') VALUES (';
        $sql .= implode(',', $colValues);
        $sql .= ')';

        return $this->dbInsert($sql);
    }

    public function update($id, $values)
    {
        $columns = $this->getColumns();

        $colSet = [];
        foreach ($columns as $col) {
            if (array_key_exists($col, $values)) {
                $colSet[] = (!is_null($values[$col])) ? '`' . $col . "` = '" . $this->dbEscape($values[$col]) . "'" : '`' . $col . '` = NULL';
            }
        }

        if (empty($colSet)) {
            return true;
        }

        if (!array_key_exists($this->updateCol, $values) && !empty($this->updateCol)) {
            $colSet[] = '`' . $this->updateCol . "` = '" . Dates::sqlNow() . "'";
        }

        $sql = 'UPDATE ' . $this->source . ' SET ';
        $sql .= implode(',', $colSet);
        $sql .= ' WHERE `' . $this->idCol . "` = '" . $this->dbEscape($id) . "'";

        return $this->dbUpdate($sql);
    }

    public function updateRows($query, $values)
    {
        $columns = $this->getColumns();

        $colSet = [];
        foreach ($columns as $col) {
            if (array_key_exists($col, $values)) {
                $colSet[] = (!is_null($values[$col])) ? '`' . $col . "` = '" . $this->dbEscape($values[$col]) . "'" : '`' . $col . '` = NULL';
            }
        }

        if (empty($colSet)) {
            return true;
        }

        if (!array_key_exists($this->updateCol, $values) && !empty($this->updateCol)) {
            $colSet[] = 't.' . $this->updateCol . " = '" . Dates::sqlNow() . "'";
        }

        $params = $this->setQueryParams($query);

        $sql = 'UPDATE ' . $this->getTableSql() . '
                ' . $this->getJoinsSql() . ' SET ';
        $sql .= implode(',', $colSet);
        $sql .= (!empty($params['where'])) ? ' WHERE ' . $params['where'] : '';

        return $this->dbUpdate($sql);
    }

    public function delete($id)
    {
        $sql = 'DELETE FROM ' . $this->source . '
                WHERE `' . $this->idCol . "` = '" . $this->dbEscape($id) . "'";

        return $this->dbDelete($sql);
    }

    public function deleteRows($query)
    {
        $params = $this->setQueryParams($query);

        $sql = 'DELETE FROM ' . $this->source;
        $sql .= (!empty($params['where'])) ? ' WHERE ' . $params['where'] : '';

        return $this->dbDelete($sql);
    }

    public function truncate()
    {
        $sql = 'TRUNCATE TABLE ' . $this->source;

        return $this->dbQuery($sql);
    }

    public function getJoinsAliases()
    {
        $aliases = [];

        if (isset($this->relations)) {
            $count = 0;

            foreach ($this->relations as $name => $options) {
                if ($options['type'] == 'belongs_to') {
                    $count++;
                    $aliases[$name] = (!empty($options['alias'])) ? $options['alias'] : 'j' . $count;
                }
            }
        }

        return $aliases;
    }

    protected function getJoinsSql()
    {
        $sql = '';

        if (isset($this->relations)) {
            $count = 0;

            foreach ($this->relations as $name => $options) {
                if ($options['type'] == 'belongs_to') {
                    $count++;

                    $alias = (!empty($options['alias'])) ? $options['alias'] : 'j' . $count;
                    $className = $this->di->loader->getClassName($name);
                    $table = $className::getDal()->getSource();
                    $idCol = $className::getDal()->getIdCol();
                    $localKey = (strstr($options['local_key'], '.') !== false) ? $options['local_key'] : 't.' . $options['local_key'];

                    $sql .= ' LEFT JOIN ' . $table . ' AS ' . $alias . ' ON ' . $localKey . ' = ' . $alias . '.' . $idCol;
                    $this->cache['joins'][$name]['alias'] = $alias;
                }
            }
        }

        return ' ' . $sql . ' ';
    }

    protected function getTableSql()
    {
        return ' ' . $this->source . ' AS t ';
    }

    public function getColumnsSql()
    {
        $columns = [];

        $tableColumns = $this->getColumns();
        foreach ($tableColumns as $column) {
            $columns[] = 't.' . $column;
        }

        $aliases = $this->getJoinsAliases();
        foreach ($aliases as $name => $alias) {
            $className = $this->di->loader->getClassName($name);
            $tableColumns = $className::getDal()->getColumns();
            foreach ($tableColumns as $column) {
                $columns[] = $alias . '.' . $column . ' AS ' . $name . '__' . $column;
            }
        }

        return ' ' . implode(',', $columns) . ' ';
    }

    protected function setRow($row)
    {
        foreach ($row as $key => $val) {
            if (strstr($key, '__') !== false) {
                $parts = explode('__', $key);
                foreach (array_keys($this->relations) as $rel) {
                    if (strtolower($rel) == strtolower($parts[0])) {
                        $row[$rel][$parts[1]] = $val;
                        unset($row[$key]);
                        break;
                    }
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
            return '';
        }

        switch ($filter['op']) {
            case self::OP_EQ:
                return 't.' . $filter['field'] . " = '" . $filter['value'] . "'";
            case self::OP_NE:
                return 't.' . $filter['field'] . " <> '" . $filter['value'] . "'";
            case self::OP_GT:
                return 't.' . $filter['field'] . " > '" . $filter['value'] . "'";
            case self::OP_GE:
                return 't.' . $filter['field'] . " >= '" . $filter['value'] . "'";
            case self::OP_LT:
                return 't.' . $filter['field'] . " < '" . $filter['value'] . "'";
            case self::OP_LE:
                return 't.' . $filter['field'] . " <= '" . $filter['value'] . "'";
            case self::OP_IN:
                return 't.' . $filter['field'] . ' IN (' . implode(', ', $filter['value']) . ')';
            case self::OP_LIKE:
                return 'LOWER(t.' . $filter['field'] . ") LIKE '%" . $filter['value'] . "%'";
            case self::OP_TRUE:
                return 't.' . $filter['field'] . ' IS TRUE';
            case self::OP_FALSE:
                return 't.' . $filter['field'] . ' IS FALSE';
            case self::OP_EMPTY:
                return '(t.' . $filter['field'] . ' IS NULL OR t.' . $filter['field'] . " = '')";
            case self::OP_NOTEMPTY:
                return 't.' . $filter['field'] . ' IS NOT NULL AND t.' . $filter['field'] . " <> ''";
        }

        return '';
    }

    protected function setQueryParams($query = null, $inactives = false)
    {
        $params = [
            'columns' => $this->getColumnsSql(),
            'where' => '',
            'order' => '',
            'limit' => '',
            'offset' => ''
        ];

        if (is_array($query)) {
            if (!empty($query['columns'])) {
                $params['columns'] = ' ' . implode(',', $query['columns']) . ' ';
            }

            if (is_array($query['where'])) {
                $q = [];
                foreach ($query['where'] as $key => $val) {
                    $q[] = 't.' . $key . " = '" . $val . "'";
                }
                $params['where'] = implode(' AND ', $q);
            } elseif ($query['where']) {
                $params['where'] = $query['where'];
                if (!empty($query['bind'])) {
                    foreach ($query['bind'] as $key => $val) {
                        $params['where'] = str_replace(':' . $key . ':', $this->dbEscape($val), $params['where']);
                    }
                }
            }

            if (!empty($query['order'])) {
                $params['order'] = $query['order'];
            }

            if (!empty($query['limit'])) {
                $params['limit'] = $query['limit'];
            }

            if (!empty($query['start'])) {
                $params['offset'] = $query['start'];
            }
        } elseif (is_numeric($query)) {
            $params['where'] = 't.' . $this->idCol . ' = ' . $this->dbEscape($query);
        } elseif ($query && is_string($query)) {
            $params['where'] = $query;
        }

        if ($this->validQuery) {
            if ($params['where']) {
                $params['where'] .= ' AND ';
            }

            $params['where'] .= $this->validQuery;
        }

        if (!$inactives && $this->activeQuery) {
            if ($params['where']) {
                $params['where'] .= ' AND ';
            }

            $params['where'] .= $this->activeQuery;
        }

        return $params;
    }
}
