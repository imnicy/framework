<?php

namespace Nicy\Framework\Bindings\View;

use League\Plates\Engine;
use League\Plates\Extension\URI;
use Nicy\Framework\Bindings\View\Extensions\CSRFToken;
use Nicy\Framework\Bindings\View\Extensions\Asset;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\View\Extensions\Url;
use Nicy\Support\Str;

class Factory
{
    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * @var \League\Plates\Engine
     */
    protected $engine;

    /**
     * @var array
     */
    protected $registeredExtensions = [];

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->registerEngine();

        $this->registerAvailableExtensions();
    }

    /**
     * @return \League\Plates\Engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Set a plates engine instance
     *
     * @return void
     */
    public function registerEngine()
    {
        $this->engine = new Engine(
            $this->container['config']['view.path'],
            $this->container['config']['view.extension']
        );
    }

    /**
     * Register any available extensions
     *
     * @return void
     */
    protected function registerAvailableExtensions()
    {
        foreach (get_class_methods($class = static::class) as $method) {

            if (Str::endsWith($method, 'Extension') && ! in_array($method, $this->registeredExtensions)) {

                forward_static_call([$class, $method]);

                $this->registeredExtensions[] = $method;
            }
        }
    }

    /**
     * Load asset extension
     *
     * @return void
     */
    protected function assetExtension()
    {
        $this->engine->loadExtension(new Asset($this->container['config']['view.assets_path']));
    }

    /**
     * Load CSRF token extension
     *
     * @return void
     */
    protected function CSRFTokenExtension()
    {
        $this->engine->loadExtension(new CSRFToken($this->container));
    }

    /**
     * Load UrlGenerate extension
     *
     * @return void
     */
    protected function urlExtension()
    {
        $this->engine->loadExtension(new Url($this->container));
    }

    /**
     * Load uri extension
     *
     * @return void
     */
    protected function uriExtension()
    {
        $this->engine->loadExtension(new URI($this->container['url']->path()));
    }
}