<?php

namespace Nicy\Framework\Bindings\Cache;

use Nicy\Framework\Support\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('cache', function() {
            return new CacheManager($this->container);
        });

        $this->container->singleton('cache.store', function() {
            return $this->container['cache']->driver();
        });
    }
}