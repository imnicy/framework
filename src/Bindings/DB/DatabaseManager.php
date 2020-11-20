<?php

namespace Nicy\Framework\Bindings\DB;

use Nicy\Support\Arr;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\DB\Connectors\ConnectionFactory;

class DatabaseManager
{
    /**
     * The container instance.
     *
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * The active connection instances.
     *
     * @var array
     */
    protected $connections = [];

    /**
     * The database connection factory instance.
     *
     * @var ConnectionFactory
     */
    protected $factory;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->builderConnectionFactory();
    }

    /**
     * Return the connection factory
     *
     * @return void
     */
    protected function builderConnectionFactory()
    {
        $this->factory = new ConnectionFactory($this->container);
    }

    /**
     * Get a database connection instance.
     *
     * @param string|null $name
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function connection($name=null)
    {
        $name = $name ?: $this->getDefaultConnection();

        // If we haven't created this connection, we'll create it based on the config
        if (! isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($name);
        }

        return $this->connections[$name];
    }

    /**
     * Make the database connection instance.
     *
     * @param string $name
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        return $this->factory->make($config, $name);
    }

    /**
     * Get the configuration for a connection.
     *
     * @param string $name
     * @return array
     *
     * @throws \InvalidArgumentException
     */
    protected function configuration($name)
    {
        $name = $name ?: $this->getDefaultConnection();
        $connections = $this->container['config']['database.connections'];

        if (is_null($config = Arr::get($connections, $name))) {
            throw new \InvalidArgumentException("Database [{$name}] not configured.");
        }

        return $config;
    }

    /**
     * Get the default connection name.
     *
     * @return string
     */
    public function getDefaultConnection()
    {
        return $this->container['config']['database.default'];
    }

    /**
     * Dynamically call the default connection instance.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}