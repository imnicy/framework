<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Disk
 * @package Framework\Facades
 *
 * @mixin \League\Flysystem\Filesystem
 *
 * @method static void write(string $location, string $contents, array $config = [])
 * @method static void delete(string $location)
 * @method static void createDirectory(string $location, array $config = [])
 * @method static void move(string $source, string $destination, array $config = [])
 * @method static void copy(string $source, string $destination, array $config = [])
 * @method static string read(string $location)
 * @method static bool fileExists(string $location)
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