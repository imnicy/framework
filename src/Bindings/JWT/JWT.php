<?php

namespace Nicy\Framework\Bindings\JWT;

use BadMethodCallException;
use Nicy\Framework\Bindings\JWT\Contracts\Subject;
use Nicy\Framework\Bindings\JWT\Exceptions\JWTException;
use Nicy\Framework\Bindings\JWT\Parser\Parser;
use Nicy\Framework\Bindings\JWT\Support\CustomClaims;
use Psr\Http\Message\RequestInterface;

class JWT
{
    use CustomClaims;

    /**
     * The authentication manager.
     *
     * @var Manager
     */
    protected $manager;

    /**
     * The HTTP parser.
     *
     * @var Parser
     */
    protected $parser;

    /**
     * The token.
     *
     * @var Token|null
     */
    protected $token;

    /**
     * Lock the subject.
     *
     * @var bool
     */
    protected $lockSubject = true;

    public function __construct(Manager $manager, Parser $parser)
    {
        $this->manager = $manager;
        $this->parser = $parser;
    }

    /**
     * Generate a token for a given subject.
     *
     * @param Subject $subject
     * @return Token
     * @throws Exceptions\TokenInvalidException
     */
    public function subject(Subject $subject)
    {
        $payload = $this->makePayload($subject);

        return $this->manager->encode($payload);
    }

    /**
     * Refresh an expired token.
     *
     * @param bool $resetClaims
     * @return Token
     * @throws JWTException
     */
    public function refresh($resetClaims=false)
    {
        $this->requireToken();
        return $this->manager
                ->customClaims($this->getCustomClaims())
                ->refresh($this->token, $resetClaims);
    }

    /**
     * Invalidate a token
     *
     * @return $this
     * @throws JWTException
     */
    public function invalidate()
    {
        $this->requireToken();

        return $this;
    }

    /**
     * Alias to get the payload, and as a result checks that
     * the token is valid i.e. not expired or blacklisted.
     *
     * @return Payload
     * @throws JWTException
     */
    public function checkOrFail()
    {
        return $this->getPayload();
    }

    /**
     * Check that the token is valid.
     *
     * @param bool $getPayload
     * @return Payload|bool
     */
    public function check($getPayload=false)
    {
        try {
            $payload = $this->checkOrFail();
        } catch (JWTException $e) {
            return false;
        }

        return $getPayload ? $payload : true;
    }

    /**
     * Get the token.
     *
     * @return Token|null
     */
    public function getToken()
    {
        if ($this->token === null) {
            try {
                $this->parseToken();
            } catch (JWTException $e) {
                $this->token = null;
            }
        }
        return $this->token;
    }

    /**
     * Parse the token from the request.
     *
     * @return $this
     * @throws JWTException
     */
    public function parseToken()
    {
        if (! $token = $this->parser->parseToken()) {
            throw new JWTException('The token could not be parsed from the request');
        }
        return $this->setToken($token);
    }

    /**
     * Get the raw Payload instance.
     *
     * @return Payload
     * @throws JWTException
     */
    public function getPayload()
    {
        $this->requireToken();

        return $this->manager->decode($this->token);
    }

    /**
     * Alias for getPayload().
     *
     * @return Payload
     * @throws JWTException
     */
    public function payload()
    {
        return $this->getPayload();
    }

    /**
     * Convenience method to get a claim value.
     *
     * @param string $claim
     * @return mixed
     * @throws JWTException
     */
    public function getClaim($claim)
    {
        return $this->payload()->get($claim);
    }

    /**
     * Create a Payload instance.
     *
     * @param Subject $subject
     * @return Payload
     */
    public function makePayload(Subject $subject)
    {
        return $this->factory()->customClaims($this->getClaimsArray($subject))->make();
    }

    /**
     * Build the claims array and return it.
     *
     * @param Subject $subject
     * @return array
     */
    protected function getClaimsArray(Subject $subject)
    {
        return array_merge(
            $subject->getCustomClaims(), // custom claims from Subject method
            $this->customClaims // custom claims from inline setter
        );
    }

    /**
     * Set the token.
     *
     * @param Token|string $token
     * @return $this
     * @throws Exceptions\TokenInvalidException
     */
    public function setToken($token)
    {
        $this->token = $token instanceof Token ? $token : new Token($token);

        return $this;
    }

    /**
     * Unset the current token.
     *
     * @return $this
     */
    public function unsetToken()
    {
        $this->token = null;

        return $this;
    }

    /**
     * Ensure that a token is available.
     *
     * @return void
     * @throws JWTException
     */
    protected function requireToken()
    {
        if (! $this->token) {
            throw new JWTException('A token is required');
        }
    }

    /**
     * Set the request instance.
     *
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->parser->setRequest($request);

        return $this;
    }

    /**
     * Set whether the subject should be "locked".
     *
     * @param bool $lock
     * @return $this
     */
    public function lockSubject($lock)
    {
        $this->lockSubject = $lock;

        return $this;
    }

    /**
     * Get the Manager instance.
     *
     * @return Manager
     */
    public function manager()
    {
        return $this->manager;
    }

    /**
     * Get the Parser instance.
     *
     * @return Parser
     */
    public function parser()
    {
        return $this->parser;
    }

    /**
     * Get the Payload Factory.
     *
     * @return Factory
     */
    public function factory()
    {
        return $this->manager->getPayloadFactory();
    }

    /**
     * Magically call the JWT Manager.
     *
     * @param string $method
     * @param array $parameters
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->manager, $method)) {
            return call_user_func_array([$this->manager, $method], $parameters);
        }
        throw new BadMethodCallException("Method [$method] does not exist.");
    }
}
