<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use Twig\Environment;
use Nicy\Framework\Bindings\View\Contracts\FunctionInterface;
use Nicy\Container\Contracts\Container;
use Nicy\Support\HtmlString;
use Twig\TwigFunction;

class CSRFToken implements FunctionInterface
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
        $engine->addFunction(new TwigFunction('csrf_token', [$this, 'buildCSRFToken']));
        $engine->addFunction(new TwigFunction('csrf_field', [$this, 'buildCSRFField']));
    }

    /**
     * Build CSRF token string
     *
     * @return string|null
     */
    public function buildCSRFToken()
    {
        if ($this->container->has('session')) {
            return $this->container['session']->token();
        }

        return null;
    }

    /**
     * Build CSRF token field
     *
     * @return HtmlString|string
     */
    public function buildCSRFField()
    {
        return new HtmlString('<input type="hidden" name="_token" value="'.$this->buildCsrfToken().'">');
    }
}