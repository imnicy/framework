<?php

namespace Nicy\Framework\Bindings\JWT\Middleware;

use Nicy\Framework\Bindings\JWT\Exceptions\AuthenticationException;
use Nicy\Framework\Bindings\JWT\Exceptions\JWTException;
use Nicy\Framework\Main;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Authorization implements MiddlewareInterface
{
    /**
     * Jwt Authorization middleware
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws AuthenticationException
     * @throws JWTException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $jwt = Main::instance()->container('jwt');

        $jwt->setRequest($request);

        if ($jwt->getToken() && $jwt->check(true)) {
            return $handler->handle($request);
        }

        throw new AuthenticationException('authentication failed');
    }
}