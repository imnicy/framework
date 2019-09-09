<?php

namespace Nicy\Framework\Bindings\Events;

use League\Event\Emitter;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\Events\Contracts\Dispatcher as DispatcherContract;

class Dispatcher extends Emitter implements DispatcherContract
{
    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param string $event
     * @param mixed $listener
     *
     * @return Dispatcher|void
     */
    public function listen($event, $listener)
    {
        return $this->addListener($event, $listener);
    }

    /**
     * Dispatch an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     *
     * @return mixed
     */
    public function dispatch($event, $payload = [])
    {
        if (is_string($event) && class_exists($event)) {
            $event = $this->container->make($event);
        }

        return $this->emit($event, $payload);
    }

    /**
     * Flush a set of pushed events.
     *
     * @param string $event
     *
     * @return Dispatcher
     */
    public function flush($event)
    {
        return $this->removeAllListeners($event);
    }
}