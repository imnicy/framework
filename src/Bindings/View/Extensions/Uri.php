<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use Latte\Engine;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\View\Contracts\FunctionInterface;

class Uri implements FunctionInterface
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
     * @param Engine $engine
     * @return void
     */
    public function register(Engine $engine) :void
    {
        $engine->addFunction('url_to', [$this, 'to']);
        $engine->addFunction('route_to', [$this, 'routeTo']);
        $engine->addFunction('current_url', [$this, 'current']);
    }

    /**
     * @param string $path
     * @param array $extra
     * @param bool $secure
     *
     * @return string
     */
    public function to(string $path, array $extra=[], bool $secure=false)
    {
        return $this->container['url']->to($path, $extra, $secure);
    }

    /**
     * @param string $name
     * @param array $data
     * @param array $parameters
     *
     * @return string
     */
    public function routeTo(string $name, array $data=[], array $parameters=[])
    {
        return $this->container['url']->route($name, $data, $parameters);
    }

    /**
     * @return string
     */
    public function current()
    {
        return $this->container['url']->current();
    }
}