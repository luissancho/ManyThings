<?php

namespace ManyThings\Core;

use ReflectionClass;

class DI
{
    protected $deps = [];
    protected static $di;

    public static function getDI()
    {
        if (empty(self::$di)) {
            return new self();
        }

        return self::$di;
    }

    public function __construct()
    {
        self::$di = $this;
    }

    public function __get($key)
    {
        if (array_key_exists($key, $this->deps)) {
            return $this->deps[$key];
        }
    }

    public function get($key)
    {
        return $this->{$key};
    }

    public function set($key, $value, $params = [])
    {
        if (is_callable($value)) {
            $this->deps[$key] = call_user_func_array($value, $params);
        } elseif (is_string($value) && class_exists($value)) {
            $class = new ReflectionClass($value);
            $this->deps[$key] = $class->newInstanceArgs($params);
        } else {
            $this->deps[$key] = $value;
        }

        return $this;
    }

    public function loaded($key)
    {
        return array_key_exists($key, $this->deps);
    }
}
