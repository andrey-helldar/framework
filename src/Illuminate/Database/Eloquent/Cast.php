<?php

namespace Illuminate\Database\Eloquent;

use Exception;
use Illuminate\Contracts\Database\Eloquent\Castable;

abstract class Cast implements Castable
{
    /**
     * Type of variable written to the database.
     *
     * @var string
     */
    public static $databaseKeyType = 'string';

    /**
     * Storing value for processing in instance.
     *
     * @var mixed
     */
    public $value;

    /**
     * Variable name.
     *
     * @var string|null
     */
    protected $key;

    /**
     * Creating an instance with a variable name
     *
     * @param  string|null  $key
     */
    public function __construct($key = null)
    {
        $this->key = $key;
    }

    /**
     * Get a given attribute from the model.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function fromDatabase($value = null)
    {
        return $value;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function toDatabase($value = null)
    {
        return $value;
    }

    /**
     * Getting instance value
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Setting instance value
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Getting the value of a stored variable
     *
     * @param  string  $name
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

    /**
     * Setting the value of a stored variable
     *
     * @param  string  $name
     * @param  null  $value
     */
    public function __set($name, $value = null)
    {
        $this->initializeValue();

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

    /**
     * Check if a variable is an array.
     *
     * @return bool
     */
    protected function isArray()
    {
        return is_array($this->value);
    }

    /**
     * Check if a variable is an object.
     *
     * @return bool
     */
    protected function isObject()
    {
        return is_object($this->value);
    }

    /**
     * Check for the existence of an array or object key.
     *
     * @param  string  $name
     * @return bool
     */
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

    /**
     * Initialization of an array in case of working with an array or object.
     */
    protected function initializeValue()
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
