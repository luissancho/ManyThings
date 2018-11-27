<?php

namespace ManyThings\Core\Dal;

use ManyThings\Core\DI;

class CoreDal
{
    const TYPE_SQL = 'sql';
    const TYPE_API = 'api';

    const OP_EQ = 'eq';
    const OP_NE = 'ne';
    const OP_GT = 'gt';
    const OP_GE = 'ge';
    const OP_LT = 'lt';
    const OP_LE = 'le';
    const OP_IN = 'in';
    const OP_LIKE = 'like';
    const OP_TRUE = 'true';
    const OP_FALSE = 'false';
    const OP_EMPTY = 'empty';
    const OP_NOTEMPTY = 'notempty';

    public $type;
    protected $database;
    protected $handler;
    protected $di;

    public function __construct($database = null, $type = self::TYPE_SQL)
    {
        $this->di = self::getDI();

        if (!$database) {
            $this->database = '';
            $this->type = self::TYPE_SQL;
            $this->handler = $this->di->db;
        } else {
            switch ($type) {
                case self::TYPE_SQL:
                    $this->database = $database;
                    $this->type = self::TYPE_SQL;
                    $this->handler = $this->di->dbs[$database];
                    break;
                case self::TYPE_API:
                    $this->database = $database;
                    $this->type = self::TYPE_API;
                    $this->handler = $this->di->apis[$database];
                    break;
                default:
                    $this->database = '';
                    $this->type = self::TYPE_SQL;
                    $this->handler = $this->di->db;
            }
        }
    }

    public function dbQuery($query, $params = [], $method = null)
    {
        return $this->handler->query($query, $params, $method);
    }

    public function dbInsert($query, $params = [], $method = null)
    {
        return $this->handler->insert($query, $params, $method);
    }

    public function dbUpdate($query, $params = [], $method = null)
    {
        return $this->handler->update($query, $params, $method);
    }

    public function dbDelete($query, $params = [], $method = null)
    {
        return $this->handler->delete($query, $params, $method);
    }

    public function dbGetResults($query, $params = [], $method = null)
    {
        return $this->handler->getResults($query, $params, $method);
    }

    public function dbGetRow($query, $params = [], $method = null)
    {
        return $this->handler->getRow($query, $params, $method);
    }

    public function dbGetVar($query, $params = [], $method = null)
    {
        return $this->handler->getVar($query, $params, $method);
    }

    public function dbGetColumns($source)
    {
        return $this->handler->getColumns($source);
    }

    public function dbEscape($value)
    {
        return $this->handler->escape($value);
    }

    public static function isEmpty($value)
    {
        return boolval(empty($value) || $value == self::OP_EMPTY);
    }

    /*
    * Deprecated
    */

    public function sqlQuery($query)
    {
        return $this->dbQuery($query);
    }

    public function sqlInsert($query)
    {
        return $this->dbInsert($query);
    }

    public function sqlUpdate($query)
    {
        return $this->dbUpdate($query);
    }

    public function sqlDelete($query)
    {
        return $this->dbDelete($query);
    }

    public function sqlGetResults($query)
    {
        return $this->dbGetResults($query);
    }

    public function sqlGetRow($query)
    {
        return $this->dbGetRow($query);
    }

    public function sqlGetVar($query)
    {
        return $this->dbGetVar($query);
    }

    public function sqlEscape($value)
    {
        return $this->dbEscape($value);
    }

    protected static function getDI()
    {
        return DI::getDI();
    }
}
