<?php

namespace Nicy\Framework\Facades;

use Closure;
use Nicy\Framework\Support\Facade;

/**
 * Class Storage
 * @package Framework\Facades
 *
 * @method static \Nicy\Framework\Bindings\Filesystem\FilesystemManager extend(string $driver, Closure $callback)
 * @method static \League\Flysystem\FilesystemOperator disk(string $name=null)
 *
 */
class Storage extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'filesystem';
    }
}