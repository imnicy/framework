<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Disk
 * @package Framework\Facades
 *
 * @mixin \League\Flysystem\Filesystem
 *
 */
class Disk extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'filesystem.disk';
    }
}