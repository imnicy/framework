<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;
use Nicy\Container\Contracts\Container;
use Nicy\Support\HtmlString;

class CSRFToken implements ExtensionInterface
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
     */
    public function register(Engine $engine)
    {
        $engine->registerFunction('csrf_token', [$this, 'buildCSRFToken']);

        $engine->registerFunction('csrf_field', [$this, 'buildCSRFField']);
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