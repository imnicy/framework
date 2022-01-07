<?php

namespace Nicy\Framework\Bindings\Session;

use Nicy\Framework\Bindings\Session\Middleware\StartSession;
use Nicy\Framework\Bindings\Session\Middleware\VerifyCsrfToken;
use Nicy\Framework\Main;
use Nicy\Framework\Support\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        Main::instance()->configure('session');

        $this->registerSessionManager();
        $this->registerSessionDriver();

        if ($this->container['config']['session.csrf_enable']) {
            $this->container['app']->add(new VerifyCsrfToken($this->container));
        }

        $this->container['app']->add(new StartSession($this->container));
    }

    /**
     * Register the session manager instance.
     *
     * @return void
     */
    protected function registerSessionManager()
    {
        $this->container->singleton('session', function () {
            return new SessionManager($this->container);
        });
    }

    /**
     * Register the session driver instance.
     *
     * @return void
     */
    protected function registerSessionDriver()
    {
        $this->container->singleton('session.store', function () {
            return $this->container->get('session')->driver();
        });
    }
}