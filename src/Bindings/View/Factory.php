<?php

namespace Nicy\Framework\Bindings\View;

use Latte\Engine;
use Nicy\Framework\Bindings\View\Contracts\Registerable;
use Nicy\Framework\Bindings\View\Extensions\CSRFToken;
use Nicy\Framework\Bindings\View\Extensions\Asset;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\View\Extensions\Uri;
use Nicy\Framework\Exceptions\ViewException;
use Nicy\Support\Str;

class Factory
{
    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * @var \Latte\Engine
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
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function config(string $name, $default=null)
    {
        return $this->container['config']->get('view.'.$name, $default);
    }

    /**
     * @return \Latte\Engine
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
        $this->engine = new Engine;

        $this->engine->setTempDirectory($this->config('temp_path', 'tmp/to/path'));

        $this->engine->setAutoRefresh($this->config('auto_refresh', true));
    }

    /**
     * Renders template to output.
     *
     * @param string $name
     * @param object|mixed[] $params
     * @param string $block
     * @return void
     */
    public function render(string $name, $params = [], string $block = null): void
    {
        $this->engine->render($this->getViewFile($name), $params, $block);
    }

    /**
     * Transform view file path
     *
     * @param string $name
     * @return string
     */
    protected function getViewFile(string $name)
    {
        if (! file_exists($file = $this->config('path', 'view/to/path') .'/'. ltrim($name, '/'))) {
            throw new ViewException('template not found.');
        }

        return $file;
    }

    /**
     * Register extension
     *
     * @param Registerable $extension
     * @return void
     */
    protected function load(Registerable $extension)
    {
        $extension->register($this->engine);
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
        $this->load(new Asset($this->container['config']['view.assets_path']));
    }

    /**
     * Load CSRF token extension
     *
     * @return void
     */
    protected function CSRFTokenExtension()
    {
        $this->load(new CSRFToken($this->container));
    }

    /**
     * Load UrlGenerate extension
     *
     * @return void
     */
    protected function uriExtension()
    {
        $this->load(new Uri($this->container));
    }
}