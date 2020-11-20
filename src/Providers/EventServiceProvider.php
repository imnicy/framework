<?php

namespace Nicy\Framework\Providers;

use Closure;
use InvalidArgumentException;
use Nicy\Framework\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array
     */
    protected $listen = [];

    /**
     * {@inheritdoc}
     */
    public function register()
    {
        //
    }

    /**
     * Register the application's event listeners.
     *
     * @return void
     */
    public function boot()
    {
        $events = $this->container['events'];

        foreach ($this->listen as $event => $listeners) {
            foreach ($listeners as $listener) {
                $events->listen($event, $this->builderListener($event, $listener));
            }
        }
    }

    /**
     * @param string $event
     * @param mixed $listener
     *
     * @return mixed
     */
    protected function builderListener($event, $listener)
    {
        if ($listener instanceof Closure || is_callable($listener)) {
            return $listener;
        }
        else if (class_exists($listener)) {
            return new $listener;
        }

        throw new InvalidArgumentException(
            'A invalid listener for event '.$event.',  Listeners should be ListenerInterface, Closure or callable'
        );
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->listen;
    }
}