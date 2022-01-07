<?php

namespace Nicy\Framework\Bindings\DB;

use Nicy\Framework\Bindings\DB\Repository\Base;
use Nicy\Framework\Support\ServiceProvider;

class DatabaseServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('db', function() {
            return new DatabaseManager($this->container);
        });
    }

    /**
     * Boot the service provider
     *
     * @return void
     */
    public function boot()
    {
        Base::setEventDispatcher($this->container['events']);
    }
}