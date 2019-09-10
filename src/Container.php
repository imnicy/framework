<?php

namespace Nicy\Framework;

use Nicy\Framework\Bindings\Cookie\Factory;
use Nicy\Framework\Support\ServiceProvider;
use Nicy\Framework\Bindings\Config\Repository as ConfigRepository;
use Nicy\Framework\Bindings\Routing\Router;
use Nicy\Framework\Bindings\Routing\UrlGenerator;
use Nicy\Container\Drivers\DiContainer;

class Container extends DiContainer
{
    /**
     * The service binding methods that have been executed.
     *
     * @var array
     */
    protected $ranServiceBinders = [];

    /**
     * Indicates if the manager has "booted".
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * The loaded service providers.
     *
     * @var array
     */
    protected $loadedProviders = [];

    /**
     * Returns an entry of the container by its name.
     *
     * @param string $name Entry name or a class name.
     *
     * @return mixed
     */
    public function get($name)
    {
        $this->isAvailableBindings($name);

        return parent::get($name);
    }

    /**
     * Build an entry of the container by its name.
     *
     * This method behave like get() except resolves the entry again every time.
     * For example if the entry is a class then a new instance will be created each time.
     *
     * This method makes the container behave like a factory.
     *
     * @param string $name
     * @param array $parameters
     *
     * @return mixed
     */
    public function make($name, array $parameters = [])
    {
        $this->isAvailableBindings($name);

        return parent::make($name, $parameters);
    }

    /**
     * If its name in available bindings, register that
     *
     * @param string $name
     *
     * @return void
     */
    protected function isAvailableBindings($name)
    {
        if (! $this->has($name) &&
            array_key_exists($name, $this->availableBindings) &&
            ! array_key_exists($this->availableBindings[$name], $this->ranServiceBinders)) {
            $this->{$method = $this->availableBindings[$name]}();

            $this->ranServiceBinders[$method] = true;
        }
    }

    /**
     * Register a service provider with the container.
     *
     * @param \Nicy\Framework\Support\ServiceProvider|string $provider
     *
     * @return \Nicy\Framework\Support\ServiceProvider|void
     */
    public function register($provider)
    {
        if (! $provider instanceof ServiceProvider) {
            $provider = new $provider($this);
        }

        if (array_key_exists($providerName = get_class($provider), $this->loadedProviders)) {
            return;
        }

        $this->loadedProviders[$providerName] = $provider;

        if (method_exists($provider, 'register')) {
            $provider->register();
        }

        if ($this->booted) {
            $this->bootProvider($provider);
        }
    }

    /**
     * Boots the registered providers.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->booted) {
            return ;
        }

        array_walk($this->loadedProviders, function ($p) {
            $this->bootProvider($p);
        });

        $this->booted = true;
    }

    /**
     * Boot the given service provider.
     *
     * @param \Nicy\Framework\Support\ServiceProvider $provider
     *
     * @return mixed|void
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    protected function registerConfigBindings()
    {
        $this->singleton('config', function () {
            return new ConfigRepository([]);
        });
    }

    protected function registerCookieBindings()
    {
        return $this->singleton('cookie', function() {
            return new Factory($this);
        });
    }

    protected function registerAppBindings()
    {
        $this->singleton('app', function() {
            return $this->app;
        });
    }

    protected function registerRoutingBindings()
    {
        $this->singleton('router', function() {
            return new Router(Main::getInstance()->app());
        });

        $this->singleton('router.parser', function() {
            return Main::getInstance()->app()->getRouteCollector()->getRouteParser();
        });
    }

    protected function registerUrlBindings()
    {
        $this->singleton('url', function() {
            return new UrlGenerator($this->app);
        });
    }

    protected function registerCacheBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\Cache\CacheServiceProvider', 'cache');
    }

    protected function registerDatabaseBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\DB\DatabaseServiceProvider', 'database');
    }

    protected function registerEventsBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\Events\EventServiceProvider');
    }

    protected function registerValidationBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\Validation\ValidationServiceProvider');
    }

    protected function registerLoggingBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\Log\LogServiceProvider', 'logging');
    }

    protected function registerViewBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\View\ViewServiceProvider', 'view');
    }

    protected function registerFilesystemBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\Filesystem\FilesystemServiceProvider', 'filesystem');
    }

    protected function registerEncryptionBindings()
    {
        Main::getInstance()->loadComponent('Nicy\Framework\Bindings\Encryption\EncryptionServiceProvider', 'app');
    }

    protected function getRequestAliasesBindings()
    {
        $this->singleton('request', function() {
            return $this->get('Psr\Http\Message\ServerRequestInterface');
        });
    }

    /**
     * The available container bindings and their respective load methods.
     *
     * @var array
     */
    public $availableBindings = [
        'app'               => 'registerAppBindings',
        'config'            => 'registerConfigBindings',
        'cookie'            => 'registerCookieBindings',
        'router'            => 'registerRoutingBindings',
        'router.parser'     => 'registerRoutingBindings',
        'url'               => 'registerUrlBindings',
        'cache'             => 'registerCacheBindings',
        'cache.store'       => 'registerCacheBindings',
        'db'                => 'registerDatabaseBindings',
        'events'            => 'registerEventsBindings',
        'validation'        => 'registerValidationBindings',
        'logger'            => 'registerLoggingBindings',
        'view'              => 'registerViewBindings',
        'filesystem'        => 'registerFilesystemBindings',
        'filesystem.disk'   => 'registerFilesystemBindings',
        'encrypter'         => 'registerEncryptionBindings',
        'request'           => 'getRequestAliasesBindings',

        'Nicy\Framework\Bindings\Events\Contracts\Dispatcher' => 'registerEventsBindings',
        'Nicy\Framework\Bindings\Encryption\Contracts\Encrypter' => 'registerEncryptionBindings',
        'Nicy\Framework\Bindings\Filesystem\Contracts\Factory' => 'registerFilesystemBindings',
    ];
}