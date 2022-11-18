<?php

namespace Nicy\Framework\Bindings\Bus;

use Nicy\Container\Contracts\Container;
use Nicy\Framework\Support\ServiceProvider;

class BusServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton(Dispatcher::class, function () {
            return new Dispatcher($this->container);
        });
    }
}
