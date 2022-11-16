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
 * @method static mixed pull($key, $default=null)
 * @method static void put($key, $value=null)
 * @method static mixed remember($key, $callback)
 * @method static void push($key, $value)
 * @method static void flash($key, $value=true)
 * @method static mixed remove($key)
 * @method static void forget($keys)
 * @method static void flush()
 * @method static mixed get($key, $default=null)
 * @method static bool has($key)
 * @method static bool exists($key)
 * @method static array only($keys)
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