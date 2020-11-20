<?php

namespace Nicy\Framework\Concerns;

use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface as PsrRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

trait RoutesRequests
{
    protected function dispatch(PsrRequestInterface $request=null)
    {
        // Run App & Emit Response
        if (! $request) {
            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
        }

        $this->container->singleton('Psr\Http\Message\ServerRequestInterface', $request);
        $this->container->boot();

        return $this->app->handle($request);
    }

    /**
     * Run the application and send the response.
     *
     * @param PsrRequestInterface|null $request
     *
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