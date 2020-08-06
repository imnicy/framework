<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;
use Slim\Interfaces\RouteGroupInterface;
use Slim\Interfaces\RouteInterface;

/**
 * Class Router
 * @package Framework\Facades
 *
 * @method static RouteInterface get(string $pattern, $callable)
 * @method static RouteInterface post(string $pattern, $callable)
 * @method static RouteInterface put(string $pattern, $callable)
 * @method static RouteInterface patch(string $pattern, $callable)
 * @method static RouteInterface delete(string $pattern, $callable)
 * @method static RouteInterface options(string $pattern, $callable)
 * @method static RouteInterface any(string $pattern, $callable)
 * @method static RouteInterface map(array $methods, string $pattern, $callable)
 * @method static RouteGroupInterface group(string $pattern, $callable)
 * @method static RouteInterface redirect(string $from, $to, int $status = 302)
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