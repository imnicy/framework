<?php

namespace Nicy\Framework\Bindings\Events;

use Nicy\Framework\Bindings\Events\Contracts\Dispatcher;

final class EventWarp
{
    /**
     * @var string|object
     */
    protected $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get event object
     *
     * @return string|object
     */
    public function event()
    {
        return $this->event;
    }

    /**
     * Has propagation stopped?
     *
     * @var bool
     */
    protected $propagationStopped = false;

    /**
     * The emitter instance.
     *
     * @var EventWarp|null
     */
    protected $emitter;

    /**
     * @param Dispatcher $emitter
     * @return $this
     */
    public function setEmitter(Dispatcher $emitter)
    {
        $this->emitter = $emitter;

        return $this;
    }

    /**
     * @return EventWarp
     */
    public function getEmitter()
    {
        return $this->emitter;
    }

    /**
     * @return $this
     */
    public function stopPropagation()
    {
        $this->propagationStopped = true;

        return $this;
    }

    /**
     * @return bool
     */
    public function isPropagationStopped()
    {
        return $this->propagationStopped;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return is_string($this->event) ? $this->event : get_class($this->event);
    }

    /**
     * Dynamically call the event properties
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->event->{$name} ?? null;
    }

    /**
     * Dynamically call the event method
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return method_exists($this->event, $method) ? $this->event->{$method}($parameters) : null;
    }
}