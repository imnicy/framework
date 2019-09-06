<?php

namespace Nicy\Framework\Handlers\Strategies;

use ErrorException;
use Nicy\Framework\Main;
use Nicy\Framework\Support\Helpers\ResponseHelper;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Support\Contracts\Jsonable;
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
     * @param array                  $routeArguments
     *
     * @return ResponseInterface
     * @throws ErrorException
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

        Main::getInstance()->container()->singleton('request', $request);

        $contents = container()->call($callable, [$request, $routeArguments]);

        return ResponseHelper::prepare($contents);
    }
}
