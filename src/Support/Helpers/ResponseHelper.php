<?php

namespace Nicy\Framework\Support\Helpers;

use Dflydev\FigCookies\SetCookie;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Support\Contracts\Jsonable;
use Psr\Http\Message\ResponseInterface;
use Slim\Psr7\Response;

class ResponseHelper
{
    /**
     * @param mixed $contents
     *
     * @return ResponseInterface|Response
     */
    public static function prepare($contents)
    {
        if ($contents instanceof ResponseInterface) {
            $response = $contents;
        }
        else {
            $response = new Response();
        }

        if ($contents instanceof Jsonable) {
            $response = static::shouldBeJson($response, $contents->toJson());
        }
        else if (is_array($contents) || $contents instanceof Arrayable) {
            $response = static::shouldBeJson($response, json_encode(is_array($contents) ? $contents : $contents->toArray()));
        }
        else if (is_string($contents) || is_bool($contents) || is_int($contents) || method_exists($contents, '__toString')) {
            $response->getBody()->write((string) $contents);
        }
        else if (is_null($contents)) {
            // invalid contents, will return empty response instance
        }
        else {
            // invalid contents,
        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @param string $contents
     *
     * @return ResponseInterface
     */
    public static function shouldBeJson(ResponseInterface $response, $contents = null)
    {
        $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');

        if ($contents) {
            $response->getBody()->write($contents);
        }

        return $response;
    }

    /**
     * @param $contents
     * @param SetCookie $cookie
     *
     * @return Response
     */
    public static function responseWithCookie($contents, SetCookie $cookie)
    {
        return container('cookie')->setOnResponse(static::prepare($contents), $cookie);
    }
}