<?php

namespace Nicy\Framework\Bindings\Validation;

use Nicy\Framework\Bindings\Validation\Factory as ValidationFactory;
use Nicy\Framework\Support\ServiceProvider;

class ValidationServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('validation', function() {
            return new ValidationFactory;
        });
    }
}