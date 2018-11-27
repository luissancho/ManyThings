<?php

namespace ManyThings\Core;

use ezSQL_mysqli;

class MySql extends ezSQL_mysqli
{
    public function __construct($username, $password, $database, $host, $persistent = false)
    {
        parent::__construct($username, $password, $database, $host, 'utf-8');

        if ($persistent) {
            $this->dbhost = 'p:' . $this->dbhost;
        }
    }

    protected function error($error, $query)
    {
        $this->captured_errors = [];
        $this->last_error = null;

        throw new AppException($error, $query);
    }

    public function query($query)
    {
        parent::query($query);

        if ($this->last_error) {
            $this->error($this->last_error, $query);
        }

        return true;
    }

    public function insert($sql)
    {
        $this->query($sql);

        return $this->insert_id;
    }

    public function update($sql)
    {
        $this->query($sql);

        return true;
    }

    public function delete($query)
    {
        $this->query($query);

        return true;
    }

    public function getResults($query)
    {
        $results = $this->get_results($query);

        $array = [];
        if ($results) {
            foreach ($results as $row) {
                $array[] = get_object_vars($row);
            }
        }

        return $array;
    }

    public function getRow($query)
    {
        $row = $this->get_row($query);

        $array = [];
        if ($row) {
            $array = get_object_vars($row);
        }

        return $array;
    }

    public function getVar($query)
    {
        $var = $this->get_var($query);

        if (!isset($var)) {
            return false;
        }

        return $var;
    }

    public function getColumns($source)
    {
        $columns = [];

        $query = 'SHOW COLUMNS FROM ' . $source;
        $results = $this->get_results($query);

        if ($results) {
            foreach ($results as $row) {
                $columns[] = $row->Field;
            }
        }

        return $columns;
    }
}
