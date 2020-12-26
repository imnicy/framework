<?php

namespace Nicy\Framework\Bindings\View\Contracts;

use Twig\Environment;

interface Registerable
{
    /**
     * @param Environment $engine
     * @return void
     */
    public function register(Environment $engine) :void ;
}