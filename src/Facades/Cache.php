<?php

namespace Nicy\Framework\Facades;

use Closure;
use Nicy\Framework\Support\Facade;
use Phpfastcache\Helper\Psr16Adapter;

/**
 * Class Cache
 * @package Framework\Facades
 *
 * @method static $this extend($driver, Closure $callback)
 * @method static Psr16Adapter driver($name = null)
 * @method static mixed|null get($key, $default = null)
 * @method static bool set($key, $value, $ttl = null)
 * @method static bool delete($key)
 * @method static bool clear()
 * @method static iterable getMultiple($keys, $default = null)
 * @method static bool setMultiple($values, $ttl = null)
 * @method static bool deleteMultiple($keys)
 * @method static bool has($key)
 *
 * @see https://github.com/PHPSocialNetwork/phpfastcache/wiki
 */
class Cache extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'cache';
    }
}