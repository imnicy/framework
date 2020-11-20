<?php

namespace Nicy\Framework\Support\Traits;

use Dflydev\FigCookies\SetCookie;
use Nicy\Framework\Support\Helpers\ResponseHelper;

trait ForResponse
{
    /**
     * Return a response instance
     *
     * @param mixed $contents
     * @param array $headers
     * @param array $cookies
     * @param int|null $status
     * @param int|null $version
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response($contents, array $headers=[], array $cookies=[], int $status=null, int $version=null)
    {
        $response = ResponseHelper::prepare($contents);

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

            $response = ResponseHelper::responseWithCookie($response, $cookie);
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