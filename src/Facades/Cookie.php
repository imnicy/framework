<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

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