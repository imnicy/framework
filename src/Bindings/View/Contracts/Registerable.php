<?php

namespace Nicy\Framework\Bindings\View\Contracts;

use Latte\Engine;

interface Registerable
{
    /**
     * @param Engine $engine
     * @return void
     */
    public function register(Engine $engine) :void ;
}