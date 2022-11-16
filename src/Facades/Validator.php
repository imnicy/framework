<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Validator
 * @package Framework\Facades
 *
 * @method static \Rakit\Validation\Validation make($inputs, $rules, $messages=[])
 * @method static void|bool validate($inputs, $rules, $messages=[])
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
        return 'validation';
    }
}