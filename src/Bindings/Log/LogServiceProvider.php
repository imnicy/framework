<?php

namespace Nicy\Framework\Bindings\Log;

use Nicy\Framework\Support\ServiceProvider;

class LogServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('logger', function () {
            return new LogManager($this->container);
        });
    }
}