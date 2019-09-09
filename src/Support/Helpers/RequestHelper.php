<?php

namespace Nicy\Framework\Support\Helpers;

use Nicy\Framework\Main;
use Nicy\Support\Collection;
use Nicy\Support\Str;
use Psr\Http\Message\ServerRequestInterface;

class RequestHelper
{
    /**
     * @var \Nicy\Support\Collection
     */
    protected static $attributes;

    /**
     * @return \Nicy\Support\Collection
     */
    public static function all()
    {
        return static::newCollection(true, true);
    }

    /**
     * @return Collection
     */
    public static function queries()
    {
        return static::newCollection(true, false);
    }

    /**
     * @return Collection
     */
    public static function requests()
    {
        return static::newCollection(false, true);
    }

    /**
     * @param bool $withQueries
     * @param bool $withRequests
     *
     * @return \Nicy\Support\Collection
     */
    protected static function newCollection($withQueries = true, $withRequests = true)
    {
        if (static::$attributes) {
            return static::$attributes;
        }

        $request = Main::getInstance()->container('request');

        if (Str::contains($request->getHeaderLine('Content-Type'), ['+json', '/json'])) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        }

        $attributes = [];

        if ($withQueries) {
            $attributes = $attributes + (array) $request->getQueryParams();
        }

        if ($withRequests) {
            $attributes = $attributes + (array) $request->getParsedBody();
        }

        return static::$attributes = new Collection($attributes);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return static::newCollection(true, true)->get($name, $default);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public static function request(string $name, $default = null)
    {
        return static::newCollection(false, true)->get($name, $default);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public static function input(string $name, $default = null)
    {
        return static::newCollection(true, false)->get($name, $default);
    }
}