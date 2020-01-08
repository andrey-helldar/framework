<?php

namespace Illuminate\Contracts\Database\Eloquent;

interface Castable
{
    /**
     * Get a given attribute from the model.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function fromDatabase($value = null);

    /**
     * Set a given attribute on the model.
     *
     * @param mixed $value
     *
     * @return mixed
     */
    public function toDatabase($value = null);

    public function getValue();

    public function setValue($value = null);
}
