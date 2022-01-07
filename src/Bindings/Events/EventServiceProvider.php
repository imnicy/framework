<?php

namespace Nicy\Framework\Bindings\Events;

use Nicy\Framework\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('events', function() {
            return new Dispatcher($this->container);
        });

        $this->container->singleton('Nicy\Framework\Bindings\Events\Contracts\Dispatcher', function() {
            return $this->container->get('events');
        });
    }
}