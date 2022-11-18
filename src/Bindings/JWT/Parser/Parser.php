<?php

namespace Nicy\Framework\Bindings\JWT\Parser;

use Psr\Http\Message\RequestInterface;

class Parser
{
    /**
     * The chain.
     *
     * @var array
     */
    private $chain;

    /**
     * The request.
     *
     * @var RequestInterface
     */
    protected $request;

    public function __construct(RequestInterface $request, $chain = [])
    {
        $this->request = $request;
        $this->chain = $chain;
    }

    /**
     * Get the parser chain.
     *
     * @return array
     */
    public function getChain()
    {
        return $this->chain;
    }

    /**
     * Add a new parser to the chain.
     *
     * @param array|\Nicy\Framework\Bindings\JWT\Contracts\Parser $parsers
     * @return $this
     */
    public function addParser($parsers)
    {
        $this->chain = array_merge($this->chain, is_array($parsers) ? $parsers : [$parsers]);
        return $this;
    }

    /**
     * Set the order of the parser chain.
     *
     * @param array $chain
     * @return $this
     */
    public function setChain($chain)
    {
        $this->chain = $chain;
        return $this;
    }

    /**
     * Alias for setting the order of the chain.
     *
     * @param array $chain
     * @return $this
     */
    public function setChainOrder($chain)
    {
        return $this->setChain($chain);
    }

    /**
     * Iterate through the parsers and attempt to retrieve
     * a value, otherwise return null.
     *
     * @return string|null
     */
    public function parseToken()
    {
        foreach ($this->chain as $parser) {
            if ($response = $parser->parse($this->request)) {
                return $response;
            }
        }
        return null;
    }

    /**
     * Check whether a token exists in the chain.
     *
     * @return bool
     */
    public function hasToken()
    {
        return $this->parseToken() !== null;
    }

    /**
     * Set the request instance.
     *
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
        return $this;
    }
}
