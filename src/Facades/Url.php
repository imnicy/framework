<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Url
 * @package Nicy\Framework\Facades
 *
 * @method static string full()
 * @method static string path()
 * @method static string current()
 * @method static string to(string $path, array $extra=[], bool $secure=null)
 * @method static string secure(string $path, array $parameters=[])
 * @method static string asset(string $path, bool $secure=null)
 * @method static string assetFrom(string $root, string $path, bool $secure=null)
 * @method static string secureAsset(string $path)
 * @method static string route(string $name, array $parameters=[], array $queryParams=[])
 */
class Url extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'url';
    }
}