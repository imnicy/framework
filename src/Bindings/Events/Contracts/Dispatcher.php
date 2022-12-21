<?php

namespace Nicy\Framework\Bindings\Events\Contracts;

interface Dispatcher
{
    /**
     * Register an event listener with the dispatcher.
     *
     * @param string $event
     * @param string $listener
     * @return mixed
     */
    public function listen(string $event, string $listener);

    /**
     * Dispatch an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     * @return mixed
     */
    public function dispatch($event, $payload=null);

    /**
     * Determine if a given event has listeners.
     *
     * @param string $event
     * @return bool
     */
    public function has(string $event);

    /**
     * Flush a set of pushed events.
     *
     * @param string $event
     * @return mixed
     */
    public function flush(string $event);
}