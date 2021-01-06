<?php

namespace Nicy\Framework\Providers;

use Nicy\Support\Str;
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
                $events->listen($event, $this->makeListener($listener));
            }
        }
    }

    /**
     * Register an event listener with the dispatcher.
     *
     * @param  \Closure|string  $listener
     * @return \Closure
     */
    public function makeListener($listener)
    {
        if (is_string($listener)) {
            return $this->createClassListener($listener);
        }

        return function ($payload) use ($listener) {
            return $listener(...array_values($payload));
        };
    }

    /**
     * Create a class based listener using the IoC container.
     *
     * @param  string  $listener
     * @return \Closure
     */
    public function createClassListener($listener)
    {
        return function ($payload) use ($listener) {
            return call_user_func_array(
                $this->createClassCallable($listener), $payload
            );
        };
    }

    /**
     * Create the class based event callable.
     *
     * @param  string  $listener
     * @return callable
     */
    protected function createClassCallable($listener)
    {
        [$class, $method] = Str::parseCallback($listener, 'handle');;

        return [$this->container->make($class), $method];
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listen()
    {
        return $this->listen;
    }
}