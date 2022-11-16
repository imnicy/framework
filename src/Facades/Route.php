<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Router
 * @package Framework\Facades
 *
 * @method static \Slim\Interfaces\RouteInterface get($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface post($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface put($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface patch($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface delete($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface options($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface any($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface map($methods, $pattern, $callable)
 * @method static \Slim\Interfaces\RouteGroupInterface group($pattern, $callable)
 * @method static \Slim\Interfaces\RouteInterface redirect($from, $to, $status=302)
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