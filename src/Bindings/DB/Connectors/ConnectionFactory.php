<?php

namespace Nicy\Framework\Bindings\DB\Connectors;

use Nicy\Support\Arr;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\DB\Query\Builder;

class ConnectionFactory
{
    /**
     * The container instance.
     *
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Establish a PDO connection based on the configuration.
     *
     * @param array $config
     * @param string|null $name
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function make(array $config, string $name=null)
    {
        $config = $this->parseConfig($config, $name);

        $builder = $this->createSingleConnection($config);

        $this->container['events']->dispatch('db.connected', $config);

        return $builder;
    }

    /**
     * Parse and prepare the database configuration.
     *
     * @param array $config
     * @param string $name
     * @return array
     */
    protected function parseConfig(array $config, $name)
    {
        return Arr::add(Arr::add($config, 'prefix', ''), 'name', $name);
    }

    /**
     * Create a single database connection instance.
     *
     * @param array$config
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    protected function createSingleConnection(array $config)
    {
        return new Builder($config);
    }
}