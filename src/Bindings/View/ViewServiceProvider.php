<?php

namespace Nicy\Framework\Bindings\View;

use Nicy\Framework\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('view', function() {
            return new Factory($this->container);
        });
    }
}