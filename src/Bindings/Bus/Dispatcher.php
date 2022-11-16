<?php

namespace Nicy\Framework\Bindings\Bus;

use Nicy\Container\Contracts\Container;
use Nicy\Framework\Support\Pipeline;

class Dispatcher
{
    /**
     * The fallback mapping callback.
     *
     * @var callable|null
     */
    protected $mapper;

    /**
     * The container implementation.
     *
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * The pipeline instance for the bus.
     *
     * @var \Nicy\Framework\Support\Pipeline
     */
    protected $pipeline;

    /**
     * The pipes to send commands through before dispatching.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The command to handler mapping for non-self-handling events.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * Create a new command dispatcher instance.
     *
     * @param \Nicy\Container\Contracts\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->pipeline = new Pipeline($container);
    }

    /**
     * Dispatch a command to its appropriate handler in the current process.
     *
     * @param mixed $command
     * @param mixed $handler
     * @return mixed
     */
    public function dispatch($command, $handler=null)
    {
        if ($handler || $handler = $this->getCommandHandler($command)) {
            $callback = function ($command) use ($handler) {
                return $handler->handle($command);
            };
        } else {
            $callback = function ($command) {
                return $this->container->call([$command, 'handle']);
            };
        }

        return $this->pipeline->send($command)->through($this->throughMiddleware($command))->then($callback);
    }

    /**
     * Set middleware pipes on command before execute
     *
     * @param mixed $command
     * @return array
     */
    protected function throughMiddleware($command)
    {
        if (property_exists($command, 'middleware') && is_array($command->middleware)) {
            return array_reverse(array_merge($this->pipes, $command->middleware));
        }

        return $this->pipes;
    }

    /**
     * Determine if the given command has a handler.
     *
     * @param mixed $command
     * @return bool
     */
    public function hasCommandHandler($command)
    {
        $class = get_class($command);

        if (isset($this->handlers[$class])) {
            return true;
        }

        $callback = $this->mapper;

        if ($callback === null || method_exists($command, 'handle')) {
            return false;
        }

        $this->handlers[$class] = $callback($command);

        return true;
    }

    /**
     * Retrieve the handler for a command.
     *
     * @param mixed $command
     * @return bool|mixed
     */
    public function getCommandHandler($command)
    {
        if ($this->hasCommandHandler($command)) {
            return $this->container->make($this->handlers[get_class($command)]);
        }

        return false;
    }

    /**
     * Set the pipes through which commands should be piped before dispatching.
     *
     * @param array $pipes
     * @return $this
     */
    public function pipeThrough($pipes)
    {
        $this->pipes = $pipes;

        return $this;
    }

    /**
     * Map a command to a handler.
     *
     * @param array $map
     * @return $this
     */
    public function map($map)
    {
        $this->handlers = array_merge($this->handlers, $map);

        return $this;
    }

    /**
     * Register a fallback mapper callback.
     *
     * @param callable|null $mapper
     * @return void
     */
    public function mapUsing(callable $mapper=null)
    {
        $this->mapper = $mapper;
    }

    /**
     * Map the command to a handler within a given root namespace.
     *
     * @param object $command
     * @param string $commandNamespace
     * @param string $handlerNamespace
     * @return string
     */
    public static function simpleMapping($command, $commandNamespace, $handlerNamespace)
    {
        $command = str_replace($commandNamespace, '', get_class($command));

        return $handlerNamespace.'\\'.trim($command, '\\').'Handler';
    }
}
