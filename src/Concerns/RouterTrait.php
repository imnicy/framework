<?php

namespace Nicy\Framework\Concerns;

trait RouterTrait
{
    /**
     * Add Routing Middleware
     *
     * @return void
     */
    protected function registerRoutingMiddleware()
    {
        $this->app->addRoutingMiddleware();
    }
}