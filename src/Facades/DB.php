<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class DB
 * @package Framework\Facades
 *
 * @method static \Nicy\Framework\Bindings\DB\Query\Builder connection($name = null)
 *
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'db';
    }
}