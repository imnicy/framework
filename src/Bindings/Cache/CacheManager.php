<?php

namespace Nicy\Framework\Bindings\Cache;

use Nicy\Support\Arr;
use Nicy\Support\Manager;
use Nicy\Container\Contracts\Container;
use Phpfastcache\Config\Config;
use Phpfastcache\Drivers\Files\Config as FilesConfig;
use Phpfastcache\Helper\Psr16Adapter;

class CacheManager extends Manager
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * CacheManager constructor.
     *
     * @param \Nicy\Container\Contracts\Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['cache.default'];
    }

    /**
     * Set the default cache driver name
     *
     * @param string $name
     */
    public function setDefaultDriver($name)
    {
        $this->container['config']['cache.default'] = $name;
    }

    /**
     * Create an instance of the file cache driver.
     *
     * @return \Phpfastcache\Helper\Psr16Adapter
     */
    protected function createFilesDriver()
    {
        return new Psr16Adapter('files', (new FilesConfig(
            Arr::only($this->container['config']['cache.stores.files'], ['path'])
        ))->setSecurityKey('domain'));
    }

    /**
     * Create an instance of the APC cache driver.
     *
     * @return \Phpfastcache\Helper\Psr16Adapter
     */
    protected function createApcDriver()
    {
        return new Psr16Adapter('apc');
    }

    /**
     * Create an instance of the redis cache driver.
     *
     * @return \Phpfastcache\Helper\Psr16Adapter
     */
    protected function createRedisDriver()
    {
        $config = $this->container['config']['cache.stores.redis'];

        $driver = Arr::get($config, 'client', 'predis') == 'predis' ? 'predis': 'redis';

        return new Psr16Adapter($driver, new Config(
            Arr::only($config, ['host', 'port', 'password', 'database'])
        ));
    }

    /**
     * Create an instance of the memcache cache driver.
     *
     * @return \Phpfastcache\Helper\Psr16Adapter
     */
    protected function createMemcacheDriver()
    {
        return new Psr16Adapter('memcache', new Config(
            Arr::only($this->container['config']['cache.stores.memcache'], ['host', 'port'])
        ));
    }

    /**
     * Dynamically call the default driver instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->driver()->$method(...$parameters);
    }
}