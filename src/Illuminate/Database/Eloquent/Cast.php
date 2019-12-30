<?php

namespace Illuminate\Database\Eloquent;

use Illuminate\Contracts\Database\Eloquent\Castable;

abstract class Cast implements Castable
{
    protected $keyType = 'string';

    public function fromDatabase($key, $value = null)
    {
        return $value;
    }

    public function toDatabase($key, $value = null)
    {
        return $value;
    }

    public function getKeyType()
    {
        return $this->keyType;
    }
}
