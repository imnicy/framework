<?php

namespace Nicy\Framework\Bindings\View;

use Nicy\Container\Contracts\Container;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Factory
{
    protected $container;

    protected $engine;

    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->registerEngine(
            $this->container['config']->get('view')
        );
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function registerEngine(array $options)
    {
        $this->engine = $this->setEngineEnvironment($options);
    }

    protected function getEngineLoader()
    {
        return new FilesystemLoader(
            $this->container['config']['view.path']
        );
    }

    protected function setEngineEnvironment($options)
    {
        $loader = $this->getEngineLoader();

        $environment = new Environment($loader, $options);

        return $environment;
    }


}