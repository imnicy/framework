<?php

namespace Nicy\Framework\Support\Helpers;

use Slim\Psr7\Response as SimHttpResponse;
use Nicy\Framework\Main;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Support\Contracts\Jsonable;
use Dflydev\FigCookies\SetCookie;
use Psr\Http\Message\ResponseInterface;

class Response
{
    /**
     * @param mixed $contents
     * @return ResponseInterface
     */
    public static function prepare($contents)
    {
        if ($contents instanceof ResponseInterface) {
            $response = $contents;
        }
        else {
            $response = new SimHttpResponse();
        }

        $responseBody = $response->getBody();

        if ($contents instanceof Jsonable) {
            $response = static::shouldBeJson($response, $contents->toJson());
        }
        else if (is_array($contents) || $contents instanceof Arrayable) {
            $response = static::shouldBeJson($response, json_encode(is_array($contents) ? $contents : $contents->toArray()));
        }
        else if (is_string($contents) || is_bool($contents) || is_int($contents) || method_exists($contents, '__toString')) {
            $responseBody->write((string) $contents);
        }
        else if (is_null($contents)) {
            // invalid contents, will return empty response instance
        }
        else {
            // invalid contents,
        }

        if ($responseBody->isSeekable()) {
            $responseBody->rewind();
        }

        return $response;
    }

    /**
     * @param ResponseInterface $response
     * @param string $contents
     * @return ResponseInterface
     */
    public static function shouldBeJson(ResponseInterface $response, $contents=null)
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
     * @return ResponseInterface
     */
    public static function responseWithCookie($contents, SetCookie $cookie)
    {
        return Main::getInstance()->container('cookie')->setOnResponse(static::prepare($contents), $cookie);
    }
}