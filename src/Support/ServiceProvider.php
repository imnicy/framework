<?php

namespace Nicy\Framework\Support;

use Nicy\Framework\Main;
use Nicy\Framework\Support\Contracts\Provider;

abstract class ServiceProvider implements Provider
{
    /**
     * The manager instance being facaded.
     *
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * ServiceProvider constructor.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $name
     * @param string $key
     * @return void
     */
    protected function mergeConfigFrom($name, $key)
    {
        Main::instance()->configure($name);

        $config = $this->container['config']->get($key, []);

        $this->container['config']->set($key, array_merge($this->container['config']->get($name, []), $config));
    }
}