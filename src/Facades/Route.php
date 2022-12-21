<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Router
 * @package Framework\Facades
 *
 * @method static \Slim\Interfaces\RouteInterface get(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface post(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface put(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface patch(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface delete(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface options(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface any(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface map(array $methods, string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteGroupInterface group(string $pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface redirect(string $from, string $to, int $status=302)
 *
 */
class Route extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'router';
    }
}