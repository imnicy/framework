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
     * @return \Slim\App
     */
    public function getApp()
    {
        return $this->get('main')->app();
    }

    /**
     * Boot the given service provider.
     *
     * @param \Nicy\Framework\Support\ServiceProvider $provider
     * @return mixed|void
     */
    protected function bootProvider(ServiceProvider $provider)
    {
        if (method_exists($provider, 'boot')) {
            return $this->call([$provider, 'boot']);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function registerConfigBindings()
    {
        $this->singleton('config', function () {
            return new ConfigRepository([]);
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCookieBindings()
    {
        return $this->singleton('cookie', function() {
            return new Factory($this);
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function registerAppBindings()
    {
        $this->singleton('app', function() {
            return $this->getApp();
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function registerRoutingBindings()
    {
        $this->singleton('router', function() {
            return new Router($this->getApp());
        });

        $this->singleton('router.parser', function() {
            return $this->getApp()->getRouteCollector()->getRouteParser();
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function registerUrlBindings()
    {
        $this->singleton('url', function() {
            return new UrlGenerator($this);
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCacheBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\Cache\CacheServiceProvider', 'cache');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerDatabaseBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\DB\DatabaseServiceProvider', 'database');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerEventsBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\Events\EventServiceProvider');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerValidationBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\Validation\ValidationServiceProvider');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerLoggingBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\Log\LogServiceProvider', 'logging');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerViewBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\View\ViewServiceProvider', 'view');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerFilesystemBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\Filesystem\FilesystemServiceProvider', 'filesystem');
    }

    /**
     * {@inheritdoc}
     */
    protected function registerEncryptionBindings()
    {
        Main::instance()->loadComponent('Nicy\Framework\Bindings\Encryption\EncryptionServiceProvider', 'app');
    }

    /**
     * {@inheritdoc}
     */
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
        'encryption'         => 'registerEncryptionBindings',
        'request'           => 'getRequestAliasesBindings',

        'Nicy\Framework\Bindings\Events\Contracts\Dispatcher' => 'registerEventsBindings',
        'Nicy\Framework\Bindings\Encryption\Contracts\Encrypter' => 'registerEncryptionBindings',
        'Nicy\Framework\Bindings\Filesystem\Contracts\Factory' => 'registerFilesystemBindings',
    ];
}