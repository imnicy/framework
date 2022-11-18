<?php

namespace Nicy\Framework\Bindings\JWT\Parser;

use Psr\Http\Message\RequestInterface;
use Nicy\Framework\Bindings\JWT\Contracts\Parser as ParserContract;

class AuthHeaders implements ParserContract
{
    /**
     * The header name.
     *
     * @var string
     */
    protected $header = 'authorization';

    /**
     * Try to parse the token from the request header.
     *
     * @param RequestInterface $request
     * @return null|string
     */
    public function parse(RequestInterface $request)
    {
        $header = $request->getHeader($this->header);
        if ($header) {
            return trim($header[0]);
        }
        return null;
    }

    /**
     * Set the header name.
     *
     * @param string $name
     * @return $this
     */
    public function setHeaderName(string $name)
    {
        $this->header = $name;
        return $this;
    }
}
