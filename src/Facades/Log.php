<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Log
 * @package Framework\Facades
 *
 * @method static void debug($message, array $context=[])
 * @method static void warning($message, array $context=[])
 * @method static void info($message, array $context=[])
 * @method static void error($message, array $context=[])
 * @method static void notice($message, array $context=[])
 * @method static void log($level, $message, array $context=[])
 * @method static void write($level, $message, array $context=[]).
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