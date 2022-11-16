<?php

namespace Nicy\Framework\Bindings\DB\Repository\Concerns;

use Closure;
use Nicy\Framework\Bindings\Events\Contracts\Dispatcher as DispatcherContract;

trait HasEvents
{
    /**
     * The event map for the repository.
     *
     * Allows for object-based events for native Eloquent events.
     *
     * @var array
     */
    protected $dispatchesEvents = [];

    /**
     * The event dispatcher instance.
     *
     * @var \Nicy\Framework\Bindings\Events\Contracts\Dispatcher
     */
    protected static $dispatcher;

    /**
     * Fire the given event for the repository.
     *
     * @param string $event
     * @return mixed
     */
    protected function dispatchRepositoryEvent($event)
    {
        if (! isset(static::$dispatcher)) {
            return true;
        }

        $result = $this->filterRepositoryEventResults(
            $this->dispatchCustomrepositoryEvent($event)
        );

        if ($result === false) {
            return false;
        }

        return ! empty($result) ? $result : static::$dispatcher->dispatch(
            "db.repository.{$event}: ".static::class, $this
        );
    }

    /**
     * Fire a custom repository event for the given event.
     *
     * @param string $event
     * @return mixed|void
     */
    protected function dispatchCustomRepositoryEvent($event)
    {
        if (! isset($this->dispatchesEvents[$event])) {
            return;
        }

        $result = static::$dispatcher->dispatch(new $this->dispatchesEvents[$event]($this));

        if (! is_null($result)) {
            return $result;
        }
    }

    /**
     * Filter the repository event results.
     *
     * @param mixed $result
     * @return mixed
     */
    protected function filterRepositoryEventResults($result)
    {
        if (is_array($result)) {
            $result = array_filter($result, function ($response) {
                return ! is_null($response);
            });
        }

        return $result;
    }

    /**
     * Register a repository event with the dispatcher.
     *
     * @param string $event
     * @param Closure|string $callback
     * @return void
     */
    protected static function registerRepositoryEvent($event, $callback)
    {
        if (isset(static::$dispatcher)) {
            $name = static::class;

            static::$dispatcher->listen("db.repository.{$event}: {$name}", $callback);
        }
    }

    /**
     * Set the event dispatcher instance.
     *
     * @param \Nicy\Framework\Bindings\Events\Contracts\Dispatcher $dispatcher
     * @return void
     */
    public static function setEventDispatcher(DispatcherContract $dispatcher)
    {
        static::$dispatcher = $dispatcher;
    }

    /**
     * Register a saving repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function saving($callback)
    {
        static::registerRepositoryEvent('saving', $callback);
    }

    /**
     * Register a saved repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function saved($callback)
    {
        static::registerRepositoryEvent('saved', $callback);
    }

    /**
     * Register an updating repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function updating($callback)
    {
        static::registerRepositoryEvent('updating', $callback);
    }

    /**
     * Register an updated repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function updated($callback)
    {
        static::registerRepositoryEvent('updated', $callback);
    }

    /**
     * Register a creating repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function creating($callback)
    {
        static::registerRepositoryEvent('creating', $callback);
    }

    /**
     * Register a created repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function created($callback)
    {
        static::registerRepositoryEvent('created', $callback);
    }

    /**
     * Register a deleting repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function deleting($callback)
    {
        static::registerRepositoryEvent('deleting', $callback);
    }

    /**
     * Register a deleted repository event with the dispatcher.
     *
     * @param Closure|string $callback
     * @return void
     */
    public static function deleted($callback)
    {
        static::registerRepositoryEvent('deleted', $callback);
    }
}