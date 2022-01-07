<?php

namespace Nicy\Framework\Exceptions;

use Exception;
use Psr\Http\Message\ResponseInterface;

interface ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param \Exception $e
     * @return void
     */
    public function report(Exception $e);

    /**
     * Determine if the exception should be reported.
     *
     * @param \Exception $e
     * @return bool
     */
    public function shouldReport(Exception $e);

    /**
     * Render an exception into an HTTP response.
     *
     * @param \Exception $e
     * @return ResponseInterface
     */
    public function render(Exception $e);

}
