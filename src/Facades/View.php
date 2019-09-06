<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class View
 * @package Framework\Facades
 *
 * @method static string render($name, array $context = [])
 * @method static void enableDebug()
 * @method static void disableDebug()
 *
 */
class View extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'view';
    }
}