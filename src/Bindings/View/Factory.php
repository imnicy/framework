<?php

namespace Nicy\Framework\Bindings\View;

use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Nicy\Framework\Bindings\View\Contracts\Registerable;
use Nicy\Framework\Bindings\View\Extensions\CSRFToken;
use Nicy\Framework\Bindings\View\Extensions\Asset;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\View\Extensions\Url;
use Nicy\Framework\Exceptions\ViewException;
use Nicy\Support\Str;
use Twig\TemplateWrapper;

class Factory
{
    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * @var \Twig\Environment
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
     * @return \Twig\Environment
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
        $loader = new FilesystemLoader($this->config('path', '/path/to/templates'));

        $this->engine = new Environment($loader, [
            // Cache
            'cache' => $this->config('cache', false),   // /path/to/compilation_cache

            // Options
            'debug' => $this->config('debug', true),
            'auto_reload' => $this->config('auto_reload', true),
            'autoescape' => $this->config('autoescape ', 'html'),
            'optimizations' => $this->config('optimizations', 1)    // set to 0 to disable
        ]);
    }

    /**
     * Renders template to output.
     *
     * @param string|TemplateWrapper $name
     * @param array $context
     * @return string
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(string $name, array $context = []): string
    {
        return $this->engine->render($name, $context);
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
        $this->load(new Asset($this->config('assets_path')));
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
        $this->load(new Url($this->container));
    }
}