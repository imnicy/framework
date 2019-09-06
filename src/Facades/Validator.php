<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;
use Rakit\Validation\Validation;

/**
 * Class Validator
 * @package Framework\Facades
 *
 * @method static Validation make(array $inputs, array $rules, array $messages = [])
 * @method static void|bool validate(array $inputs, array $rules, array $messages = [])
 *
 */
class Validator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'validator';
    }
}