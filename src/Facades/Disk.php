<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Disk
 * @package Framework\Facades
 *
 * @mixin \League\Flysystem\Filesystem
 *
 * @method static void write($location, $contents, $config=[])
 * @method static void delete($location)
 * @method static void createDirectory($location, $config=[])
 * @method static void move($source, $destination, $config=[])
 * @method static void copy($source, $destination, $config=[])
 * @method static string read($location)
 * @method static bool fileExists($location)
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