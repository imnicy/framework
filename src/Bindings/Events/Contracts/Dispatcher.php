<?php

namespace Nicy\Framework\Bindings\Events\Contracts;

use League\Event\EmitterInterface;

interface Dispatcher extends EmitterInterface
{
    /**
     * Register an event listener with the dispatcher.
     *
     * @param string $event
     * @param mixed $listener
     * @return mixed
     */
    public function listen($event, $listener);

    /**
     * Dispatch an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     * @return mixed
     */
    public function dispatch($event, $payload=[]);

    /**
     * Determine if a given event has listeners.
     *
     * @param string $event
     * @return bool
     */
    public function hasListeners($event);

    /**
     * Flush a set of pushed events.
     *
     * @param string $event
     * @return mixed
     */
    public function flush($event);
}