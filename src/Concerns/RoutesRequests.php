<?php

namespace Nicy\Framework\Concerns;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

trait RoutesRequests
{
    /**
     * Handle a request
     *
     * This method traverses the application middleware stack and then returns the
     * resultant Response object.
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request) : ResponseInterface
    {
        // Boot container
        $this->container->boot();

        return $this->app()->handle($request);
    }

    /**
     * Dispatch server request handler
     *
     * @param PsrRequestInterface|null $request
     * @return PsrResponseInterface
     */
    protected function dispatch(PsrRequestInterface $request=null)
    {
        // Run App & Emit Response
        if (! $request) {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
        }

        return $this->handle($request);
    }

    /**
     * Run the application and send the response.
     *
     * @param PsrRequestInterface|null $request
     * @return void
     */
    public function run($request=null)
    {
        $response = $this->dispatch($request);

        if ($response instanceof PsrResponseInterface) {
            $this->resolveResponseEmitter()->emit($response);
        }
        else {
            echo (string) $response;
        }
    }

    /**
     * @return \Nicy\Framework\Support\Contracts\ResponseEmitter
     */
    protected function resolveResponseEmitter()
    {
        if ($this->container->has('Nicy\Framework\Support\Contracts\ResponseEmitter')) {
            return $this->container->make('Nicy\Framework\Support\Contracts\ResponseEmitter');
        } else {
            return $this->container->make('Nicy\Framework\Support\Emitters\SlimResponseEmitter');
        }
    }
}