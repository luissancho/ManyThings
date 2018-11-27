<?php

namespace ManyThings\Core;

use ManyThings\Core\Dal\CoreDal;

class Core
{
    protected $class;
    protected $resource;
    protected $di;
    protected $dal;

    protected static $dals = [];

    public function __construct()
    {
        $curClass = self::getClass();
        if (empty($this->class)) {
            $this->class = $curClass;
        }

        $class = $this->class;
        if ($curClass != $class) {
            $this->resource = new $class();
        } else {
            $this->resource = $this;
        }

        $this->di = self::getDI();
        $this->dal = self::getDal();
    }

    public static function getNS()
    {
        $route = explode('\\', get_called_class());
        array_pop($route);

        return '\\' . implode('\\', $route);
    }

    public static function getClass()
    {
        $route = explode('\\', get_called_class());

        return end($route);
    }

    public static function newInstance($id = null)
    {
        $curClass = get_called_class();

        return new $curClass($id);
    }

    protected static function getDI()
    {
        return DI::getDI();
    }

    public static function getDal()
    {
        $curNS = self::getNS();
        $curClass = self::getClass();
        if (!isset(self::$dals[$curClass])) {
            $dalClass = $curNS . '\\Dal\\' . $curClass . 'Dal';
            self::$dals[$curClass] = (class_exists($dalClass)) ? new $dalClass() : null;
        }

        return self::$dals[$curClass];
    }

    public static function setCoreDal($database = null, $type = CoreDal::TYPE_SQL)
    {
        $curClass = self::getClass();
        $coreDal = new CoreDal($database, $type);

        self::$dals[$curClass] = $coreDal;

        return $coreDal;
    }
}
