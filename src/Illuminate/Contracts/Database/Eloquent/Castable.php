<?php

namespace Illuminate\Contracts\Database\Eloquent;

interface Castable
{
    /**
     * Get a given attribute from the model.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function fromDatabase($value = null);

    /**
     * Set a given attribute on the model.
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function toDatabase($value = null);

    /**
     * Getting instance value
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Setting instance value
     *
     * @param  mixed  $value
     * @return mixed
     */
    public function setValue($value = null);
}
