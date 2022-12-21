<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;
use phpDocumentor\Reflection\Types\Array_;

/**
 * Class Session
 * @package Framework\Facades
 *
 * @mixin \Nicy\Framework\Bindings\Session\Store
 *
 * @method static mixed pull(string $key, $default=null)
 * @method static void put(string $key, $value=null)
 * @method static mixed remember(string $key, $callback)
 * @method static void push(string $key, $value)
 * @method static void flash(string $key, $value=true)
 * @method static mixed remove(string $key)
 * @method static void forget(array $keys)
 * @method static void flush()
 * @method static mixed get(string $key, $default=null)
 * @method static bool has(string $key)
 * @method static bool exists(string $key)
 * @method static array only(array $keys)
 * @method static array all()
 *
 */
class Session extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'session.store';
    }
}