<?php

namespace Nicy\Framework;

use DI\ContainerBuilder;
use Nicy\Support\Str;
use Nicy\Container\Manager;
use Nicy\Framework\Container as FrameworkContainer;
use Nicy\Container\Contracts\Container as ContainerContract;
use Slim\Factory\AppFactory;
use Slim\App as SlimApplication;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nicy\Framework\Concerns\{FacadeTrait, RouterTrait, RegistersExceptionHandlers, RoutesRequests};

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
     * The configures path
     *
     * @var string
     */
    protected $configurePath;

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
    protected Request $request;

    /**
     * All the loaded configuration files.
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
     *
     * @param string $path
     * @param string|null $configurePath
     */
    public function __construct(string $path, string $configurePath=null)
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

        $this->path = $path;

        $this->configurePath = file_exists($configurePath)
            ? $configurePath
            : $this->path($configurePath ?: '');

        $this->bootstrapContainer();
        $this->configure('app');

        $this->registerErrorHandling();
        $this->bootstrapSlimApp();
    }

    /**
     * Set the shared instance of the main.
     *
     * @param \Nicy\Framework\Main|null $main
     * @return \Nicy\Framework\Main|static
     */
    public static function setInstance(Main $main=null)
    {
        return static::$instance = $main;
    }

    /**
     * Get the shared instance of the main
     *
     * @return \Nicy\Framework\Main
     */
    public static function instance()
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

            $container = $builder->build();

            $container->set('container', $container);
            $container->set(ContainerContract::class, $container);

            return $container;
        });

        $this->container = $this->manager->driver('framework.container');

        $this->container->singleton('main', $this);
        $this->container->singleton(self::class, $this);
        $this->container->singleton('env', $this->environment());
    }

    /**
     * @param MiddlewareInterface|string|callable $middleware
     * @param bool $shouldMake
     * @return $this
     */
    public function middleware($middleware, $shouldMake=true)
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
     * Create an application and register to container
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

        if ($this->container->has('Nicy\Framework\Support\Contracts\Strategy')) {
            $handler = $this->container->make('Nicy\Framework\Support\Contracts\Strategy');
        }
        else {
            $handler = $this->container->make('Nicy\Framework\Handlers\Strategies\RequestResponse');
        }

        $this->app->getRouteCollector()->setDefaultInvocationStrategy($handler);
    }

    /**
     * Return the registered container
     *
     * @param string|null $name
     * @param array $parameters
     * @return FrameworkContainer|mixed
     */
    public function container(string $name=null, array $parameters=[])
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
     * Get or check the current application environment.
     *
     * @return string
     */
    public function environment()
    {
        $env = $this->container['config']->get('env', 'production');

        if (func_num_args() > 0) {
            $patterns = is_array(func_get_arg(0)) ? func_get_arg(0) : func_get_args();

            foreach ($patterns as $pattern) {
                if (Str::is($pattern, $env)) {
                    return true;
                }
            }

            return false;
        }

        return $env;
    }

    /**
     * Register a service provider with the container.
     *
     * @param \Nicy\Framework\Support\ServiceProvider|string|array $provider
     * @return \Nicy\Framework\Support\ServiceProvider|void
     */
    public function register($provider)
    {
        if (is_array($provider)) {
            foreach ($provider as $item) {
                $this->register($item);
            }
        }
        else {
            return $this->container->register($provider);
        }
    }

    /**
     * Define an object or a value in the container.
     *
     * @param string $name
     * @param mixed $value
     */
    public function singleton(string $name, $value=null)
    {
        $this->container->singleton($name, $value);
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
     * Get the path to the application directory.
     *
     * @param string $path
     * @return string
     */
    public function path(string $path='')
    {
        return $this->path.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Load a configuration file into the application.
     *
     * @param string $name
     * @return void
     */
    public function configure(string $name)
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
     * @param array|string $providers
     * @param string|null $config
     * @param string|null $return
     * @return mixed
     */
    public function loadComponent($providers, string $config=null, string $return=null)
    {
        $config && $this->configure($config);

        foreach ((array) $providers as $provider) {
            $this->container->register($provider);
        }

        return $return ? $this->container->get($return) : null;
    }

    /**
     * @param string|null $name
     * @return string
     */
    protected function getDefaultConfigurePath(string $name=null)
    {
        $path = $this->path('config') . '/';

        if ($name) {
            $configurePath = $path . $name . '.php';
            if (! file_exists($configurePath)) {
                return '';
            }
            return $configurePath;
        }

        return $path;
    }

    /**
     * Get the path to the given configuration file.
     *
     * If no name is provided, then we'll return the path to the config folder.
     *
     * @param string|null $name
     * @return string
     */
    public function getConfigurationPath(string $name=null)
    {
        $appConfigPath = $this->configurePath;

        if ($appConfigPath) {
            if ($name) {
                $appConfigPath = $appConfigPath . '/' . $name . '.php';
            }

            if (file_exists($appConfigPath)) {
                return $appConfigPath;
            }
        }

        return $this->getDefaultConfigurePath($name);
    }

    /**
     * Dynamically call the container instance.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->container->$method(...$parameters);
    }
}