<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use Twig\Environment;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\View\Contracts\FunctionInterface;
use Twig\TwigFunction;

class Url implements FunctionInterface
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
     * @param Environment $engine
     * @return void
     */
    public function register(Environment $engine) :void
    {
        $engine->addFunction(new TwigFunction('url_to', [$this, 'to']));
        $engine->addFunction(new TwigFunction('route_to', [$this, 'routeTo']));
        $engine->addFunction(new TwigFunction('current_url', [$this, 'current']));
    }

    /**
     * @param string $path
     * @param array $extra
     * @param bool $secure
     * @return string
     */
    public function to($path, $extra=[], $secure=false)
    {
        return $this->container['url']->to($path, $extra, $secure);
    }

    /**
     * @param string $name
     * @param array $data
     * @param array $parameters
     * @return string
     */
    public function routeTo($name, $data=[], $parameters=[])
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