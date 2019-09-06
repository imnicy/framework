<?php

namespace Nicy\Framework\Bindings\Filesystem;

use Nicy\Support\Arr;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\Cached\Storage\Memory as MemoryStore;
use Nicy\Container\Contracts\Container;
use Nicy\Support\Manager;
use Nicy\Framework\Bindings\Filesystem\Contracts\Factory as FilesystemFactory;

class FilesystemManager extends Manager implements FilesystemFactory
{
    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * Custom disk configs
     *
     * @var array
     */
    protected $config = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param null|string $name
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    public function disk($name = null)
    {
        return $this->driver($name);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        $disk = $this->container['config']['filesystem.default'];

        $this->config = $this->getConfig($disk);

        return $this->getConfig($disk)['driver'];
    }

    /**
     * Create an instance of the local driver.
     *
     * @param array $config
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function createLocalDriver()
    {
        $config = $this->config;

        $permissions = $config['permissions'] ?? [];

        $links = ($config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;

        return $this->createFlysystem(new LocalAdapter(
            $config['root'], $config['lock'] ?? LOCK_EX, $links, $permissions
        ), $config);
    }

    /**
     * Create a Flysystem instance with the given adapter.
     *
     * @param \League\Flysystem\AdapterInterface $adapter
     * @param array $config
     *
     * @return \League\Flysystem\FilesystemInterface
     */
    protected function createFlysystem(AdapterInterface $adapter, array $config)
    {
        $cache = Arr::pull($config, 'cache');

        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url']);

        if ($cache) {
            $adapter = new CachedAdapter($adapter, $this->createCacheStore($cache));
        }

        return new Filesystem($adapter, count($config) > 0 ? $config : null);
    }

    /**
     * Create a cache store instance.
     *
     * @param  mixed  $config
     * @return \League\Flysystem\Cached\CacheInterface
     *
     * @throws \InvalidArgumentException
     */
    protected function createCacheStore($config)
    {
        if ($config === true) {
            return new MemoryStore;
        }

        return $this->container['cache']->set(
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->container['config']["filesystem.disks.{$name}"];
    }
}