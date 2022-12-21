<?php

namespace Nicy\Framework\Bindings\Events;

use InvalidArgumentException;
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
     * Ensure the input is a listener.
     */
    protected function ensureListener($listener)
    {
        if (is_string($listener) && class_exists($listener)) {
            return $this->container->make($listener);
        }

        if (is_callable($listener)) {
            return CallbackListener::fromCallable($listener);
        }

        throw new InvalidArgumentException('invalid event listener, should be an object or closure');
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param string $event
     * @param string|array $listener
     * @return $this
     */
    public function listen(string $event, $listener)
    {
        return $this->addListener($event, $listener);
    }

    /**
     * Ensure event input is of type EventInterface or convert it.
     *
     * @param string|object $event
     * @return EventWarp
     */
    protected function ensureEvent($event)
    {
        return new EventWarp($event);
    }

    /**
     * Prepare an event for emitting.
     *
     * @param string|object $event
     * @return array
     */
    protected function prepareEvent($event)
    {
        $event = $this->ensureEvent($event);

        $name = $event->getName();
        $event->setEmitter($this);

        return [$name, $event];
    }

    /**
     * Invoke the listeners for an event.
     *
     * @param string $name
     * @param EventWarp $event
     * @param array $arguments
     *
     * @return void
     */
    protected function invokeListeners($name, $event, array $arguments)
    {
        $listeners = $this->getListeners($name);

        foreach ($listeners as $listener) {
            if ($event->isPropagationStopped()) {
                break;
            }

            call_user_func_array([$listener, 'handle'], $arguments);
        }
    }

    /**
     * Dispatch an event and call the listeners.
     *
     * @param string|object $event
     * @param mixed $payload
     * @return $this
     */
    public function dispatch($event, $payload=null) :object
    {
        return $this->emit($event, $payload);
    }

    /**
     * @param string|object $event
     * @param mixed $payload
     * @return mixed
     */
    public function emit($event, $payload=null)
    {
        list($name, $event) = $this->prepareEvent($event);

        $arguments = [$event->event(), $payload] + func_get_args();

        $this->invokeListeners($name, $event, $arguments);
        $this->invokeListeners('*', $event, $arguments);

        return $event;
    }

    /**
     * Check an event exists ?
     *
     * @param string $event
     * @return bool
     */
    public function has(string $event)
    {
        return $this->hasListeners($event);
    }

    /**
     * Flush a set of pushed events.
     *
     * @param string $event
     * @return $this
     */
    public function flush($event)
    {
        return $this->removeAllListeners($event);
    }
}