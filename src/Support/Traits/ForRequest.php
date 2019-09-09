<?php

namespace Nicy\Framework\Support\Traits;

use Nicy\Framework\Support\Helpers\RequestHelper;

trait ForRequest
{
    /**
     * What is Mode?
     *      1: for queries,
     *      2: for requests,
     *      1 || 2: for queries and requests
     *
     * @param int $mode
     * @param null $key
     * @param null $default
     *
     * @return mixed|void|\Nicy\Support\Collection
     */
    protected function request($mode = 1 || 2, $key = null, $default = null)
    {
        if ($mode == (1 || 2)) {
            if (! $key) {
                return RequestHelper::all();
            }
            else {
                return RequestHelper::input($key, $default);
            }
        }
        else if ($mode & 1) {
            if (! $key) {
                return RequestHelper::queries();
            }
            else {
                return RequestHelper::get($key, $default);
            }
        }
        else if ($mode & 2) {
            if (! $key) {
                return RequestHelper::requests();
            }
            else {
                return RequestHelper::request($key, $default);
            }
        }

        return ;
    }
}