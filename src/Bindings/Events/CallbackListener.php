<?php

namespace Nicy\Framework\Bindings\Events;

final class CallbackListener
{
    /**
     * @var callable
     */
    protected $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    /**
     * @param callable $callback
     * @return static
     */
    public static function fromCallable(callable $callback)
    {
        return new CallbackListener($callback);
    }

    /**
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        call_user_func_array($this->callback, func_get_args());
    }
}