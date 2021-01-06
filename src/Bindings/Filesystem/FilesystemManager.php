<?php

namespace Nicy\Framework\Bindings\Filesystem;

use League\Flysystem\UnixVisibility\PortableVisibilityConverter;
use Nicy\Support\Arr;
use League\Flysystem\Local\LocalFilesystemAdapter as LocalAdapter;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;
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
     * @return \League\Flysystem\FilesystemOperator
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
     * @return \League\Flysystem\FilesystemOperator
     */
    protected function createLocalDriver()
    {
        $config = $this->config;

        $links = ($config['links'] ?? null) === 'skip'
            ? LocalAdapter::SKIP_LINKS
            : LocalAdapter::DISALLOW_LINKS;

        // Customize how visibility is converted to unix permissions
        $permissions = PortableVisibilityConverter::fromArray([
            'file' => [
                'public' => 0640,
                'private' => 0604,
            ],
            'dir' => [
                'public' => 0740,
                'private' => 7604,
            ],
        ]);

        return $this->createFlysystem(new LocalAdapter(
            $config['root'], $permissions, $config['lock'] ?? LOCK_EX, $links
        ), $config);
    }

    /**
     * Create a Flysystem instance with the given adapter.
     *
     * @param \League\Flysystem\FilesystemAdapter $adapter
     * @param array $config
     * @return \League\Flysystem\FilesystemOperator
     */
    protected function createFlysystem(FilesystemAdapter $adapter, array $config)
    {
        $inMemory = Arr::pull($config, 'in_memory');

        $config = Arr::only($config, ['visibility', 'disable_asserts', 'url']);

        if ($inMemory) {
            $adapter = new InMemoryFilesystemAdapter();
        }

        return new Filesystem($adapter, count($config) > 0 ? $config : []);
    }

    /**
     * Get the filesystem connection configuration.
     *
     * @param string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->container['config']["filesystem.disks.{$name}"];
    }
}