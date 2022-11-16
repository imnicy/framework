<?php

namespace Nicy\Framework\Support\Traits;

use Nicy\Framework\Support\Helpers\Request;

trait ForRequest
{
    /**
     * Get http request handler
     *
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    protected function request()
    {
        return Request::getRequest();
    }

    /**
     * Get Http message uri handler
     *
     * @return \Psr\Http\Message\UriInterface
     */
    protected function uri()
    {
        return Request::getUri();
    }

    /**
     * What is Mode?
     *      1: for queries,
     *      2: for requests,
     *      4: for params
     *
     *      1 | 2: for queries and requests
     *
     * @param string $key
     * @param mixed $default
     * @param int $mode
     * @return mixed|void|\Nicy\Support\Collection
     */
    protected function input($key=null, $default=null, $mode=1|2|4)
    {
        if ($mode == (1|2|4)) {
            if (! $key) {
                return Request::all();
            }

            return Request::input($key, $default);
        }
        else if ($mode & 1) {
            if (! $key) {
                return Request::queries();
            }

            return Request::get($key, $default);
        }
        else if ($mode & 2) {
            if (! $key) {
                return Request::requests();
            }

            return Request::request($key, $default);
        }
        else if ($mode & 4) {
            if (! $key) {
                return Request::files();
            }

            return Request::file($key);
        }
    }

    /**
     * @param string $key
     * @param bool $unique
     * @param string $disk
     *
     * @return false|string
     */
    protected function upload($key, $unique=false, $disk=null)
    {
        return Request::upload($key, $unique, $disk);
    }
}