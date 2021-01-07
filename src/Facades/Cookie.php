<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Cookie
 * @package Nicy\Framework\Facades
 *
 * @method static string get(string $name, ? string $default=null)
 * @method static \Dflydev\FigCookies\SetCookie make(string $name, ?string $value=null)
 */
class Cookie extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'cookie';
    }
}