<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use Latte\Engine;
use Nicy\Framework\Bindings\View\Contracts\FunctionInterface;
use Nicy\Container\Contracts\Container;
use Nicy\Support\HtmlString;

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
     * @param Engine $engine
     * @return void
     */
    public function register(Engine $engine) :void
    {
        $engine->addFunction('csrf_token', [$this, 'buildCSRFToken']);
        $engine->addFunction('csrf_field', [$this, 'buildCSRFField']);
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