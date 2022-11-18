<?php

namespace Nicy\Framework\Bindings\JWT\Contracts;

use Psr\Http\Message\RequestInterface;

interface Parser
{
    /**
     * Parse the request.
     *
     * @param RequestInterface $request
     * @return null|string
     */
    public function parse(RequestInterface $request);
}
