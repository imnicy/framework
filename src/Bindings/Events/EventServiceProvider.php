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
            return new Dispatcher();
        });
    }
}