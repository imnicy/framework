<?php

namespace Nicy\Framework\Support\Contracts;

interface Provider
{
    /**
     * Register the provider
     *
     * @return void
     */
    public function register();
}