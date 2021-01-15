<?php

namespace Nicy\Framework\Bindings\Session;

use Nicy\Framework\Bindings\Session\Handlers\CacheSessionHandler;
use Nicy\Framework\Bindings\Session\Handlers\FileSessionHandler;
use Nicy\Framework\Bindings\Session\Handlers\NullSessionHandler;
use Nicy\Container\Contracts\Container;
use Nicy\Support\Manager;

class SessionManager extends Manager
{
    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->container['config']['session.driver'];
    }

    /**
     * @return Store
     */
    protected function createFileDriver()
    {
        $lifetime = $this->container['config']['session.lifetime'];

        return $this->buildSession(new FileSessionHandler(
            $this->container['config']['session.files'], $lifetime
        ));
    }

    /**
     * @return Store
     */
    protected function createCacheDriver()
    {
        $store = $this->container['config']['session.store'];

        return $this->buildSession(new CacheSessionHandler(
            $this->container['cache']->driver($store),
            $this->container['config']['session.lifetime']
        ));
    }

    /**
     * @return Store
     */
    protected function createNullDriver()
    {
        return $this->buildSession(new NullSessionHandler());
    }

    /**
     * Build the session instance.
     *
     * @param \SessionHandlerInterface $handler
     * @return Store
     */
    protected function buildSession($handler)
    {
        return new Store($this->container['config']['session.cookie'], $handler);
    }
}