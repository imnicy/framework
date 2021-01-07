<?php

namespace Nicy\Framework\Bindings\Session\Middleware;

use Nicy\Framework\Bindings\Session\Store;
use Nicy\Container\Contracts\Container;
use Nicy\Framework\Bindings\Cookie\Factory as CookieFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class StartSession
{
    /**
     * @var array
     */
    protected $settings;

    /**
     * @var \Nicy\Container\Contracts\Container
     */
    protected $container;

    /**
     * Constructor
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        $defaults = [
            'lifetime'      => '20 minutes',
            'path'          => '/',
            'domain'        => null,
            'secure'        => false,
            'http_only'     => false,
            'name'          => 'slim_session',
            'auto_refresh'  => false
        ];

        if ($this->sessionConfigured()) {
            $this->settings = array_merge($defaults, $this->container['config']['session']);

            if (is_string($lifetime = $this->settings['lifetime'])) {
                $settings['lifetime'] = strtotime($lifetime) - time();
            }
        }
    }

    /**
     * Determine if a session driver has been configured.
     *
     * @return bool
     */
    protected function sessionConfigured()
    {
        return ! is_null($this->container['config']['session.driver'] ?? null);
    }

    /**
     * Get the session implementation from the container.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return \Nicy\Framework\Bindings\Session\Store
     */
    public function getSession(Request $request)
    {
        return tap($this->container['session.store'], function (Store $session) use ($request) {
            $session->setId($this->container['cookie']->getFromRequest($request, $session->getName()));
        });
    }

    /**
     * Called when middleware needs to be executed.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param \Psr\Http\Server\RequestHandlerInterface $handler PSR7 handler
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        if ($this->sessionConfigured()) {
            $this->startSession($request);

            $sessionStore = $this->container['session.store'];

            // Start session store
            if (! $sessionStore->isStarted()) {
                $sessionStore->start();
            }
        }

        $response = $handler->handle($request);

        if ($this->sessionConfigured()) {
            $this->storeCurrentUrl($request, $sessionStore);

            $response = $this->addCookieToResponse($response, $sessionStore);
            $sessionStore->save();
        }

        return $response;
    }

    /**
     * Store the current URL for the request if necessary.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request PSR7 request
     * @param \Nicy\Framework\Bindings\Session\Store $session
     * @return void
     */
    protected function storeCurrentUrl($request, $session)
    {
        if ($request->getMethod() === 'GET' &&
            ! 'XMLHttpRequest' == $request->getHeaderLine('X-Requested-With')) {

            $isPrefetch = strcasecmp($request->getServerParams()['HTTP_X_MOZ'] ?? null, 'prefetch') === 0
                || strcasecmp($request->getHeaderLine('Purpose'), 'prefetch') === 0;

            $isPrefetch || $session->setPreviousUrl($this->container['url']->full());
        }
    }

    /**
     * Add the session cookie to the application response.
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Nicy\Framework\Bindings\Session\Store $session
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function addCookieToResponse(Response $response, Store $session)
    {
        if ($this->sessionIsPersistent($config = $this->container['config']['session'])) {
            $response = $this->container['cookie']->setOnResponse($response, CookieFactory::setCookie(
                $session->getName(), $session->getId()
            )
                ->withDomain($this->settings['domain'])
                ->withSecure($this->settings['secure'])
                ->withHttpOnly($this->settings['http_only'])
            );
        }

        return $response;
    }

    /**
     * Determine if the configured session driver is persistent.
     *
     * @param array|null $config
     * @return bool
     */
    protected function sessionIsPersistent(array $config)
    {
        return ! in_array($config['driver'], [null]);
    }

    /**
     * Start session
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return void
     */
    protected function startSession(Request $request)
    {
        return tap($this->getSession($request), function (Store $session) {
            $session->start();
        });
    }
}