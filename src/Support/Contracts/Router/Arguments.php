<?php

namespace Nicy\Framework\Support\Contracts\Router;

use Countable;
use ArrayAccess;

interface Arguments extends ArrayAccess, Countable
{
    public function get(string $name, $default=null);

    public function all();
}