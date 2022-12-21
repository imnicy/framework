<?php

namespace Nicy\Framework\Bindings\JWT\Contracts;

interface Validator
{
    /**
     * Perform some checks on the value.
     *
     * @param mixed $value
     * @return void
     */
    public function check($value);
}
