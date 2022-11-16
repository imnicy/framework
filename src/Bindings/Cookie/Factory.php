<?php

namespace Nicy\Framework\Bindings\Cookie;

use Dflydev\FigCookies\FigRequestCookies;
use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nicy\Container\Contracts\Container;

class Factory
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
     * Get cookie value from request
     *
     * @param string $name
     * @param string $default
     * @return string
     */
    public function get($name, $default=null)
    {
        return FigRequestCookies::get($this->container['request'], $name, $default)->getValue();
    }

    /**
     * @param Request $request
     * @param string $name
     * @param string $value
     * @return string
     */
    public function getFromRequest(Request $request, $name, $value=null)
    {
        return FigRequestCookies::get($request, $name, $value)->getValue();
    }

    /**
     * Make A SetCookie instance
     *
     * @param string $name
     * @param string $value
     * @return SetCookie
     */
    public function make($name, $value=null) :SetCookie
    {
        return static::setCookie($name, $value);
    }

    /**
     * Set cookie value to response
     *
     * @param Response $response
     * @param SetCookie $cookie
     * @return Response
     */
    public function setOnResponse(Response $response, SetCookie $cookie) :Response
    {
        return FigResponseCookies::set($response, $cookie);
    }

    /**
     * @param string $name
     * @param string $value
     * @return SetCookie
     */
    public static function setCookie($name, $value=null) :SetCookie
    {
        return SetCookie::create($name, $value);
    }
}