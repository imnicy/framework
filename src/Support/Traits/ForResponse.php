<?php

namespace Nicy\Framework\Support\Traits;

use Dflydev\FigCookies\SetCookie;
use Nicy\Framework\Support\Helpers\Response;

trait ForResponse
{
    /**
     * Return a response instance
     *
     * @param mixed $contents
     * @param array $headers
     * @param array $cookies
     * @param int $status
     * @param int $version
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response($contents, $headers=[], $cookies=[], $status=null, $version=null)
    {
        $response = Response::prepare($contents);

        foreach ($headers as $name => $value) {
            $response = $response->withHeader($name, $value);
        }

        foreach ($cookies as $name => $value) {
            if ($value instanceof SetCookie) {
                $cookie = $value;
            }
            else {
                $cookie = set_cookie($name, $value);
            }

            $response = Response::responseWithCookie($response, $cookie);
        }

        if ($status) {
            $response = $response->withStatus($status);
        }

        if ($version) {
            $response = $response->withProtocolVersion($version);
        }

        return $response;
    }
}