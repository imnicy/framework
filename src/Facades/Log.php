<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Log
 * @package Framework\Facades
 *
 * @method static void debug(string $message, array $context=[])
 * @method static void warning(string $message, array $context=[])
 * @method static void info(string $message, array $context=[])
 * @method static void error(string $message, array $context=[])
 * @method static void notice(string $message, array $context=[])
 * @method static void log(int $level, string $message, array $context=[])
 * @method static void write(int $level, string $message, array $context=[]).
 *
 */
class Log extends Facade
{
    /**
     * Get LoggerInterface instance from container
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'logger';
    }
}