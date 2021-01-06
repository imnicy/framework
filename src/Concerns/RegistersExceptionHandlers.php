<?php

namespace Nicy\Framework\Concerns;

use Error;
use Throwable;
use ErrorException;
use Nicy\Framework\Exceptions\ExceptionHandler;
use Nicy\Framework\Exceptions\Debug\FatalThrowableError;
use Nicy\Framework\Exceptions\Debug\FatalErrorException;
use Nicy\Framework\Exceptions\Handler;
use Psr\Http\Message\ResponseInterface;
use Slim\Middleware\ErrorMiddleware;
use Slim\ResponseEmitter;

trait RegistersExceptionHandlers
{
    /**
     * @var ErrorMiddleware
     */
    protected $errorHandlerMiddleware;

    /**
     * @var ExceptionHandler
     */
    protected $errorHandler = Handler::class;

    /**
     * Set the error handling for the application.
     *
     * @return void
     */
    protected function registerErrorHandling()
    {
        error_reporting(-1);

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            if (error_reporting() & $level) {
                throw new ErrorException($message, 0, $level, $file, $line);
            }
        });

        set_exception_handler(function ($e) {
            $this->handleUncaughtException($e);
        });

        register_shutdown_function(function () {
            $this->handleShutdown();
        });
    }

    /**
     * Handle the application shutdown routine.
     *
     * @return void
     */
    protected function handleShutdown()
    {
        if (! is_null($error = error_get_last()) && $this->isFatalError($error['type'])) {
            $this->handleUncaughtException(new FatalErrorException(
                $error['message'], $error['type'], 0, $error['file'], $error['line']
            ));
        }
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param int $type
     * @return bool
     */
    protected function isFatalError($type)
    {
        $errorCodes = [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE];

        if (defined('FATAL_ERROR')) {
            $errorCodes[] = FATAL_ERROR;
        }

        return in_array($type, $errorCodes);
    }

    /**
     * Handle an uncaught exception instance.
     *
     * @param Throwable $e
     * @return void
     */
    protected function handleUncaughtException($e)
    {
        $handler = $this->resolveExceptionHandler();

        if ($e instanceof Error) {
            $e = new FatalThrowableError($e);
        }

        $handler->report($e);

        $this->emitterResponse($handler->render($e));
    }

    /**
     * @param ResponseInterface $response
     * @return void
     */
    protected function emitterResponse($response)
    {
        $responseEmitter = new ResponseEmitter();

        $responseEmitter->emit($response);
    }

    /**
     * Get the exception handler from the container.
     *
     * @return mixed
     */
    protected function resolveExceptionHandler()
    {
        if ($this->container->has('Nicy\Framework\Exceptions\ExceptionHandler')) {
            return $this->container->get('Nicy\Framework\Exceptions\ExceptionHandler');
        } else {
            return $this->container->make($this->errorHandler);
        }
    }

    /**
     * Set custom error handler
     *
     * @param string $handler
     * @return void
     */
    public function setErrorHandler(string $handler)
    {
        if (! class_exists($handler)) {
            return ;
        }

        $this->errorHandler = $handler;
    }
}
