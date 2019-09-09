<?php

namespace Nicy\Framework\Bindings\Filesystem;

use Nicy\Framework\Support\ServiceProvider;

class FilesystemServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerManager();

        $this->container->singleton('filesystem.disk', function () {
            return $this->container['filesystem']->disk();
        });

        $this->container->singleton('Nicy\Framework\Bindings\Filesystem\Contracts\Factory', function() {
            return $this->container['filesystem.disk'];
        });
    }

    /**
     * Register the filesystem manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->container->singleton('filesystem', function () {
            return new FilesystemManager($this->container);
        });
    }
}