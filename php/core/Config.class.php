<?php

namespace ManyThings\Core;

class Config implements \ArrayAccess
{
    public function __construct($config = null)
    {
        $arrayConfig = [];

        if (is_array($config)) {
            $arrayConfig = $config;
        } elseif (is_string($config)) {
            $arrayConfig = json_decode($config, true);
        }

        foreach ($arrayConfig as $key => $val) {
            $this->offsetSet($key, $val);
        }
    }

    public function offsetExists($key)
    {
        return isset($this->{$key});
    }

    public function offsetGet($key)
    {
        if (isset($this->{$key})) {
            return $this->{$key};
        }
    }

    public function offsetSet($key, $value)
    {
        if (is_array($value)) {
            $this->{$key} = new self($value);
        } else {
            $this->{$key} = $value;
        }
    }

    public function offsetUnset($key)
    {
        $this->{$key} = null;
    }

    public function toArray()
    {
        $arrayConfig = [];

        foreach (get_object_vars($this) as $key => $val) {
            if (self::isConfig($val)) {
                $arrayConfig[$key] = $val->toArray();
            } else {
                $arrayConfig[$key] = $val;
            }
        }

        return $arrayConfig;
    }

    public function merge($config)
    {
        return $this->_merge(new self($config));
    }

    protected function _merge($config, $instance = null)
    {
        if (!self::isConfig($instance)) {
            $instance = $this;
        }

        foreach (get_object_vars($config) as $key => $val) {
            $mVal = $instance->{$key};
            if (self::isConfig($mVal) && self::isConfig($val)) {
                $this->_merge($val, $mVal);
                continue;
            } else {
                $instance->{$key} = $val;
            }
        }

        return $instance;
    }

    protected static function isConfig($obj)
    {
        return is_object($obj) && $obj instanceof self;
    }
}
