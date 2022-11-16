<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Log
 * @package Framework\Facades
 *
 * @method static void debug($message, $context=[])
 * @method static void warning($message, $context=[])
 * @method static void info($message, $context=[])
 * @method static void error($message, $context=[])
 * @method static void notice($message, $context=[])
 * @method static void log($level, $message, $context=[])
 * @method static void write($level, $message, $context=[]).
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