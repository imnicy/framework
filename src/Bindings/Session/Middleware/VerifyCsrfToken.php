<?php

namespace Nicy\Framework\Bindings\Session\Middleware;

use Dflydev\FigCookies\FigResponseCookies;
use Dflydev\FigCookies\SetCookie;
use Nicy\Framework\Exceptions\TokenMismatchException;
use Nicy\Container\Contracts\Container;
use Nicy\Support\Str;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class VerifyCsrfToken
{
    /**
     * The application instance.
     *
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * The encrypter implementation.
     *
     * @var \Nicy\Framework\Bindings\Encryption\Contracts\Encrypter
     */
    protected $encrypter;

    /**
     * Indicates whether the XSRF-TOKEN cookie should be set on the response.
     *
     * @var bool
     */
    protected $addHttpCookie = true;

    /**
     * Create a new middleware instance.
     *
     * @param \Nicy\Container\Contracts\Container $container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $this->encrypter = $this->container['encrypter'];
    }

    /**
     * Called when middleware needs to be executed.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler PSR7 handler
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if (
            $this->isReading($request) ||
            $this->inExceptArray() ||
            $this->tokensMatch($request)
        ) {
            return tap($handler->handle($request), function ($response) use ($request) {
                if ($this->shouldAddXsrfTokenCookie()) {
                    return $this->addCookieToResponse($response);
                }

                return $response;
            });
        }

        throw new TokenMismatchException;
    }

    /**
     * Determine if the HTTP request uses a ‘read’ verb.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isReading($request)
    {
        return in_array($request->getMethod(), ['HEAD', 'GET', 'OPTIONS']);
    }

    /**
     * Determine if the request has a URI that should pass through CSRF verification.
     *
     * @return bool
     */
    protected function inExceptArray()
    {
        foreach ($this->container['config']['session.csrf_except'] as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($this->container['url']->full() == $except
                || Str::is($except, trim($this->container['url']->path(), '/'))) {

                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the session and input CSRF tokens match.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function tokensMatch($request)
    {
        $token = $this->getTokenFromRequest($request);

        return is_string($this->container['session']->token()) &&
            is_string($token) &&
            hash_equals($this->container['session']->token(), $token);
    }

    /**
     * Get the CSRF token from the request.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     */
    protected function getTokenFromRequest($request)
    {
        $requestToken = $request->getParsedBody()['_token'] ?? '';

        $requestToken = $requestToken ?: ($request->getQueryParams()['_token'] ?? '');

        if (Str::contains($request->getHeaderLine('Content-Type'), ['+json', '/json'])) {
            $contents = $request->getBody()->getContents();
            $parsed = json_encode($contents, true);

            $requestToken = $parsed['_token'] ?? '';
        }

        $token = $requestToken ?: $request->getHeaderLine('X-CSRF-TOKEN');

        if (! $token && $header = $request->getHeaderLine('X-XSRF-TOKEN')) {
            $token = $this->encrypter->decrypt($header, false);
        }

        return $token;
    }

    /**
     * Determine if the cookie should be added to the response.
     *
     * @return bool
     */
    public function shouldAddXsrfTokenCookie()
    {
        return $this->addHttpCookie;
    }

    /**
     * Add the CSRF token to the response cookies.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse($response)
    {
        $config = $this->container['config']['session'];

        $sessionStore = $this->container['session'];

        $response = FigResponseCookies::set($response, SetCookie::create(
                $sessionStore->getName(), $sessionStore->getId()
            )
            ->withDomain($config['domain'])
            ->withSecure($config['secure'])
            ->withHttpOnly($config['http_only'])
        );

        return $response;
    }
}
