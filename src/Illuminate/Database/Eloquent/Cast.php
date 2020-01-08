<?php

namespace Illuminate\Database\Eloquent;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Castable;

abstract class Cast implements Castable
{
    public static $databaseKeyType = 'string';

    public $value;

    protected $key;

    public function __construct($key = null)
    {
        $this->key = $key;
    }

    public function fromDatabase($value = null)
    {
        return $value;
    }

    public function toDatabase($value = null)
    {
        return $value;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @param $name
     *
     * @throws Exception
     * @return int
     */
    public function __get($name)
    {
        if (method_exists(self::class, $name)) {
            return call_user_func([self::class, $name]);
        }

        if (! $this->isValueExists($name)) {
            throw new Exception("Unknown \"{$name}\" key", 500);
        }

        if ($this->isArray()) {
            return $this->value[$name];
        }

        if ($this->isObject()) {
            return $this->value->$name;
        }

        return $this->value;
    }

    public function __set($name, $value = null)
    {
        $this->castValue();

        if ($this->isArray()) {
            $this->value[$name] = $value;

            return;
        }

        if ($this->isObject()) {
            $this->value->$name = $value;

            return;
        }

        $this->value = $value;
    }

    protected function isArray()
    {
        return is_array($this->value);
    }

    protected function isObject()
    {
        return is_object($this->value);
    }

    protected function isValueExists($name)
    {
        if ($this->isArray()) {
            return isset($this->value[$name]);
        }

        if ($this->isObject()) {
            return isset($this->value->$name);
        }

        return true;
    }

    protected function castValue()
    {
        if ($this->value !== null) {
            return;
        }

        switch (static::$databaseKeyType) {
            case 'array':
            case 'object':
            case 'json':
                $this->setValue([]);
        }
    }
}
