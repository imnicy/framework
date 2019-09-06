<?php

namespace Nicy\Framework\Concerns;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

trait RoutesRequests
{
    protected function dispatch(PsrRequestInterface $request = null)
    {
        // Run App & Emit Response

        if (! $request) {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
        }

        $this->container->singleton('request', $request);

        $this->container->boot();

        $response = $this->app->handle($request);

        return $response;
    }

    /**
     * Run the application and send the response.
     *
     * @param PsrRequestInterface|null $request
     *
     * @return void
     */
    public function run($request = null)
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
        if ($this->container->has('Framework\Support\Contracts\ResponseEmitter')) {
            return $this->container->make('Framework\Support\Contracts\ResponseEmitter');
        } else {
            return $this->container->make('Framework\Support\Emitters\SlimResponseEmitter');
        }
    }
}