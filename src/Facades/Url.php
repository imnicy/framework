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
 * @method static string to($path, $extra=[], $secure=null)
 * @method static string secure($path, $parameters=[])
 * @method static string asset($path, $secure=null)
 * @method static string assetFrom($root, $path, $secure=null)
 * @method static string secureAsset($path)
 * @method static string route($name, $parameters=[], $queryParams=[])
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