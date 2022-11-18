<?php

namespace Nicy\Framework\Bindings\JWT\Validators;

use Nicy\Framework\Bindings\JWT\Contracts\Validator as ValidatorContract;
use Nicy\Framework\Bindings\JWT\Support\RefreshFlow;

abstract class Validator implements ValidatorContract
{
    use RefreshFlow;

    /**
     * Run the validation.
     *
     * @param array $value
     * @return void
     */
    abstract public function check($value);
}
