<?php

namespace Nicy\Framework\Handlers\Strategies;

use Nicy\Framework\Main;
use Nicy\Framework\Bindings\Routing\RouterArguments;
use Nicy\Framework\Support\Helpers\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\InvocationStrategyInterface;

/**
 * Default route callback strategy with route parameters as an array of arguments.
 */
class RequestResponse implements InvocationStrategyInterface
{
    /**
     * Invoke a route callable with request, response, and all route parameters
     * as an array of arguments.
     *
     * @param callable               $callable
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param array $routeArguments
     * @return ResponseInterface
     */
    public function __invoke(
        callable $callable,
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $routeArguments
    ): ResponseInterface {

        foreach ($routeArguments as $k => $v) {
            $request = $request->withAttribute($k, $v);
        }

        $container = Main::getInstance()->container();

        $container->singleton('Psr\Http\Message\ServerRequestInterface', $request);
        $container->singleton('Nicy\Framework\Support\Contracts\Router\Arguments', function() use($routeArguments) {
            return new RouterArguments($routeArguments);
        });

        $contents = $container->call($callable);

        return Response::prepare($contents);
    }
}
