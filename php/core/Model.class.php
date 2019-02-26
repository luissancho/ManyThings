<?php

namespace ManyThings\Core;

class Model extends Core
{
    public $id = 0;
    public $active = false;
    public $data = [];

    protected $_changed = [];

    public function __construct($id = null)
    {
        parent::__construct();

        if ($id) {
            $this->data = static::get($id);

            if ($this->data) {
                $this->id = $id;
                $this->active = $this->isActive();
            }
        }
    }

    public static function getCount($query = null)
    {
        return self::getDal()->getCount($query);
    }

    public static function getIds($query = null)
    {
        return self::getDal()->getIds($query);
    }

    public static function getResults($query = null, $obj = false)
    {
        $results = self::getDal()->getResults($query);

        if ($obj && !empty($results)) {
            $objects = [];
            foreach ($results as $row) {
                $objects[] = self::newInstance($row);
            }

            return $objects;
        } else {
            return $results;
        }
    }

    public static function getRow($query = null, $obj = false)
    {
        $row = self::getDal()->getRow($query);

        if ($obj && !empty($row)) {
            return self::newInstance($row);
        } else {
            return $row;
        }
    }

    public static function getResultsBy($column, $value, $obj = false)
    {
        return static::getResults([
            'where' => [
                $column => $value
            ]
        ], $obj);
    }

    public static function getRowBy($column, $value, $obj = false)
    {
        return static::getRow([
            'where' => [
                $column => $value
            ]
        ], $obj);
    }

    public static function all($obj = false)
    {
        return static::getResults(null, $obj);
    }

    public static function get($id, $obj = false)
    {
        return static::getRowBy(self::getDal()->getIdCol(), $id, $obj);
    }

    public static function newInstance($mixed = null)
    {
        if (is_array($mixed)) {
            return parent::newInstance($mixed[self::getDal()->getIdCol()]);
        }

        return parent::newInstance($mixed);
    }

    public static function truncate()
    {
        return self::getDal()->truncate();
    }

    public static function isEmpty()
    {
        return empty(self::getDal()->getCount());
    }

    public function getRelationsData($name = null, $query = null)
    {
        return $this->dal->getRelationsData($this->id, $name, $query);
    }

    public function create($values)
    {
        if (empty($values)) {
            return 0;
        }

        $this->id = $this->dal->create($values);

        if (empty($this->id) && array_key_exists('id', $values)) {
            $this->id = $values['id'];
        }

        $this->data = static::get($this->id);
        $this->active = $this->isActive();

        return $this->id;
    }

    public function update($values)
    {
        $values = $this->getChangedData($values);

        if (empty($values)) {
            return [];
        }

        $this->dal->update($this->id, $values);

        $this->data = static::get($this->id);
        $this->active = $this->isActive();

        return end($this->_changed);
    }

    public static function updateRows($query, $values)
    {
        if (!$query || !$values) {
            return false;
        }

        self::getDal()->updateRows($query, $values);

        return true;
    }

    public function delete()
    {
        $this->dal->delete($this->id);

        $this->id = 0;
        $this->data = [];
        $this->active = false;

        return true;
    }

    public static function deleteRows($query)
    {
        if (!$query) {
            return false;
        }

        self::getDal()->deleteRows($query);

        return true;
    }

    public static function getColumns()
    {
        return self::getDal()->getColumns();
    }

    public static function getChangeableColumns()
    {
        return self::getDal()->getChangeableColumns();
    }

    public function getChangedColumns($values)
    {
        $changed = [];

        foreach ($values as $key => $val) {
            if (
                array_key_exists($key, $this->data) &&
                (
                    $val != $this->data[$key] ||
                    (is_null($this->data[$key]) && !is_null($val)) ||
                    (is_null($val) && !is_null($this->data[$key]))
                )
            ) {
                $changed[$key] = ['from' => $this->data[$key], 'to' => $val];
            }
        }

        $this->_changed[] = $changed;

        return array_keys($changed);
    }

    public function getChangedData($values)
    {
        $data = [];
        $changed = $this->getChangedColumns($values);

        foreach ($changed as $col) {
            $data[$col] = $values[$col];
        }

        return $data;
    }

    public function getUpdateData($values)
    {
        $data = $this->data;

        foreach ($values as $key => $val) {
            $data[$key] = $val;
        }

        return $data;
    }

    public function isActive($data = null)
    {
        $filter = $this->dal->getActiveFilter();

        if (empty($filter)) {
            return true;
        }

        if (empty($data)) {
            $data = $this->data;
        }

        switch ($filter['op']) {
            case 'eq':
                return $data[$filter['field']] == $filter['value'];
            case 'neq':
                return $data[$filter['field']] != $filter['value'];
            case 'gt':
                return $data[$filter['field']] > $filter['value'];
            case 'gte':
                return $data[$filter['field']] >= $filter['value'];
            case 'lt':
                return $data[$filter['field']] < $filter['value'];
            case 'lte':
                return $data[$filter['field']] <= $filter['value'];
            case 'isnull':
                return is_null($data[$filter['field']]);
            case 'isnotnull':
                return !is_null($data[$filter['field']]);
            case 'isempty':
                return empty($data[$filter['field']]);
            case 'isnotempty':
                return !empty($data[$filter['field']]);
        }

        return true;
    }
}
