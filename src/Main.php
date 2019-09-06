<?php

namespace Nicy\Framework;

use DI\ContainerBuilder;
use Nicy\Container\Contracts\Container as ContainerContract;
use Nicy\Framework\Handlers\Strategies\RequestResponse;
use Nicy\Framework\Container as FrameworkContainer;
use Psr\Http\Server\MiddlewareInterface;
use Slim\App as SlimApplication;
use Slim\Factory\AppFactory;
use Nicy\Framework\Concerns\{FacadeTrait, RouterTrait, RegistersExceptionHandlers, RoutesRequests};
use Psr\Http\Message\ServerRequestInterface as Request;
use Nicy\Container\Manager;

class Main
{
    use RouterTrait,
        FacadeTrait,
        RegistersExceptionHandlers,
        RoutesRequests;

    /**
     * The base path of the application installation.
     *
     * @var string
     */
    protected $path;

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var FrameworkContainer
     */
    protected $container;

    /**
     * @var \Slim\App
     */
    protected $app;

    /**
     * @var Request
     */
    protected $request;

    /**
     * All of the loaded configuration files.
     *
     * @var array
     */
    protected $loadedConfigurations = [];

    /**
     * @var \Nicy\Framework\Main
     */
    protected static $instance;

    /**
     * Main constructor.
     */
    public function __construct($path)
    {
        date_default_timezone_set('Asia/Shanghai');

        $this->path = $path;

        $this->bootstrapContainer();

        $this->registerErrorHandling();

        $this->bootstrapSlimApp();
    }

    /**
     * Set the shared instance of the main.
     *
     * @param \Nicy\Framework\Main|null $main
     *
     * @return \Nicy\Framework\Main|static
     */
    public static function setInstance(Main $main = null)
    {
        return static::$instance = $main;
    }

    /**
     * Get the shared instance of the main
     *
     * @return \Nicy\Framework\Main
     */
    public static function getInstance()
    {
        return static::$instance;
    }

    /**
     * Bootstrap a container instance
     *
     * @return void
     */
    protected function bootstrapContainer()
    {
        static::setInstance($this);

        if ($this->container && $this->manager) {
            return ;
        }

        $this->manager = new Manager();
        $this->manager->extend('framework.container', function() {

            $builder = new ContainerBuilder($class = FrameworkContainer::class);
            $builder->enableCompilation($this->path('storage/cache/container/compile'), 'CompiledContainer', $class);

            $container = $builder->build();

            $container->set('container', $container);
            $container->set(ContainerContract::class, $container);

            return $container;
        });

        $this->container = $this->manager->driver('framework.container');
    }

    /**
     * @param MiddlewareInterface|string|callable $middleware
     * @param bool $shouldMake
     *
     * @return $this
     */
    public function middleware($middleware, $shouldMake = true)
    {
        if ($shouldMake && is_string($middleware) && class_exists($middleware)) {

            $middleware = $this->container->make($middleware);
        }

        $this->app->add($middleware);

        return $this;
    }

    /**
     * Builder a http error handler and request object on first
     *
     * @return void
     */
    protected function bootstrapSlimApp()
    {
        $this->registerApplication();

        $this->registerRoutingMiddleware();
    }

    /**
     * Create a application and register to container
     *
     * @return void
     */
    protected function registerApplication()
    {
        if ($this->app) {
            return ;
        }

        $this->app = AppFactory::create(null, $this->container);

        $this->container->singleton('app', $this->app);
        $this->container->singleton(SlimApplication::class, $this->app);

        $this->registerStrategyHandler();
    }

    /**
     * Register the default request strategy
     *
     * @return void
     */
    protected function registerStrategyHandler()
    {
        if (! $this->app) {
            return ;
        }

        $this->app->getRouteCollector()->setDefaultInvocationStrategy(new RequestResponse);
    }

    /**
     * Return the registered container
     *
     * @param string $name
     * @param array $parameters
     *
     * @return FrameworkContainer|mixed
     */
    public function container($name = null, $parameters = [])
    {
        if (is_null($name)) {
            return $this->container;
        }

        if (! $bound = $this->container->get($name)) {
            $bound = $this->container->make($name, $parameters);
        }

        return $bound;
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
        return $this->container->register($provider);
    }

    /**
     * Return the slim application instance
     *
     * @return SlimApplication
     */
    public function app()
    {
        if (! $this->app) {
            $this->bootstrapSlimApp();
        }

        return $this->app;
    }

    /**
     * Get the path to the application "app" directory.
     *
     * @return string
     */
    public function path($path = '')
    {
        return $this->path.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param string $name
     *
     * @return void
     */
    public function configure($name)
    {
        if (isset($this->loadedConfigurations[$name])) {
            return;
        }

        $this->loadedConfigurations[$name] = true;

        $path = $this->getConfigurationPath($name);

        if ($path) {
            $this->container->get('config')->set($name, require $path);
        }
    }

    /**
     * Configure and load the given component and provider.
     *
     * @param string $config
     * @param array|string $providers
     * @param string|null $return
     *
     * @return mixed
     */
    public function loadComponent($providers, $config = null, $return = null)
    {
        $config && $this->configure($config);

        foreach ((array) $providers as $provider) {
            $this->container->register($provider);
        }

        return $return ? $this->container->get($return) : null;
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param string|null $name
     *
     * @return string
     */
    public function getConfigurationPath($name = null)
    {
        if (! $name) {
            $appConfigDir = $this->path('config').'/';

            if (file_exists($appConfigDir)) {
                return $appConfigDir;
            } elseif (file_exists($path = __DIR__.'/../config/')) {
                return $path;
            }
        } else {
            $appConfigPath = $this->path('config').'/'.$name.'.php';

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            } elseif (file_exists($path = __DIR__.'/../config/'.$name.'.php')) {
                return $path;
            }
        }
    }

    /**
     * Dynamically call the container instance.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->container()->$method(...$parameters);
    }
}
