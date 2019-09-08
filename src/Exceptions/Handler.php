<?php

namespace Nicy\Framework\Exceptions;

use Exception;
use Nicy\Framework\Main;
use Slim\Error\Renderers\HtmlErrorRenderer;
use Slim\Exception\HttpException;
use Slim\Exception\HttpMethodNotAllowedException;
use Psr\Http\Message\ResponseInterface;

class Handler implements ExceptionHandler
{
    /**
     * @var string
     */
    protected $defaultRenderer = HtmlErrorRenderer::class;

    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [];

    /**
     * Report or log an exception.
     *
     * @param Exception $e
     * @throws Exception
     *
     * @return void
     */
    public function report(Exception $e)
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        try {
            $logger = Main::getInstance()->container('logger');
        } catch (Exception $ex) {
            throw $e; // throw the original exception
        }

        $logger->error($e);
    }

    /**
     * Determine if the exception should be reported.
     *
     * @param Exception $e
     *
     * @return bool
     */
    public function shouldReport(Exception $e)
    {
        return ! $this->shouldntReport($e);
    }

    /**
     * Determine if the exception is in the "do not report" list.
     *
     * @param Exception $e
     *
     * @return bool
     */
    protected function shouldntReport(Exception $e)
    {
        foreach ($this->dontReport as $type) {
            if ($e instanceof $type) {
                return true;
            }
        }

        return false;
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param Exception $e
     *
     * @return ResponseInterface
     */
    public function render(Exception $e)
    {
        $response = $this->getResponse($e);

        $response = $response->withHeader('Content-type', 'text/html');

        if ($e instanceof HttpMethodNotAllowedException) {
            $allowedMethods = implode(', ', $e->getAllowedMethods());
            $response = $response->withHeader('Allow', $allowedMethods);
        }

        $renderer = $this->getRenderer();

        $body = call_user_func($renderer, $e, Main::getInstance()->container('config')->get('app.debug', false));

        $response->getBody()->write($body);

        return $response;
    }

    /**
     * @param Exception $e
     *
     * @return ResponseInterface
     */
    protected function getResponse(Exception $e)
    {
        $code = 500;

        if ($e instanceof HttpException) {
            $code = $e->getCode();
        }

        return Main::getInstance()->app()->getResponseFactory()->createResponse($code);
    }

    /**
     * @return callable
     */
    protected function getRenderer()
    {
        return Main::getInstance()->app()->getCallableResolver()->resolve(
            $this->defaultRenderer
        );
    }
}