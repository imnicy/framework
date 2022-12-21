<?php

namespace Nicy\Framework\Facades;

use Closure;
use Nicy\Framework\Support\Facade;

/**
 * Class Cache
 * @package Framework\Facades
 *
 * @method static $this extend(string $driver, Closure $callback)
 * @method static \Phpfastcache\Helper\Psr16Adapter driver(string $name=null)
 * @method static mixed|null get(string $key, $default=null)
 * @method static bool set(string $key, $value, $ttl=null)
 * @method static bool delete(string $key)
 * @method static bool clear()
 * @method static iterable getMultiple(array $keys, $default=null)
 * @method static bool setMultiple(array $values, $ttl=null)
 * @method static bool deleteMultiple(array $keys)
 * @method static bool has(string $key)
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