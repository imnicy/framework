<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Session
 * @package Framework\Facades
 *
 * @mixin \Framework\Bindings\Session\Store
 *
 */
class Session extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'session.store';
    }
}