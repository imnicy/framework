<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Support\Facade;

/**
 * Class Events
 * @package Framework\Facades
 *
 * @method static \Nicy\Framework\Bindings\Events\Contracts\Dispatcher|void listen(string $event, $listener)
 * @method static mixed dispatch(string $event, array $payload=[])
 *
 */
class Events extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return 'events';
    }
}