<?php

namespace Nicy\Framework\Bindings\JWT;

use Nicy\Framework\Bindings\JWT\Contracts\Providers\JWT as JWTContract;
use Nicy\Framework\Bindings\JWT\Support\CustomClaims;
use Nicy\Framework\Bindings\JWT\Support\RefreshFlow;

class Manager
{
    use CustomClaims, RefreshFlow;

    /**
     * The provider.
     *
     * @var JWTContract
     */
    protected $provider;

    /**
     * the payload factory.
     *
     * @var Factory
     */
    protected $payloadFactory;

    /**
     * the persistent claims.
     *
     * @var array
     */
    protected $persistentClaims = [];

    public function __construct(JWTContract $provider, Factory $payloadFactory)
    {
        $this->provider = $provider;
        $this->payloadFactory = $payloadFactory;
    }

    /**
     * Encode a Payload and return the Token.
     *
     * @param Payload $payload
     * @return Token
     * @throws Exceptions\TokenInvalidException
     */
    public function encode(Payload $payload)
    {
        $token = $this->provider->encode($payload->get());

        return new Token($token, $payload);
    }

    /**
     * Decode a Token and return the Payload.
     *
     * @param Token $token
     * @return Payload
     */
    public function decode(Token $token)
    {
        $payloadArray = $this->provider->decode($token->get());

        return $this->payloadFactory->setRefreshFlow($this->refreshFlow)->customClaims($payloadArray)->make();
    }

    /**
     * Refresh a Token and return a new Token.
     *
     * @param Token $token
     * @param bool $resetClaims
     * @return Token
     * @throws Exceptions\TokenInvalidException
     */
    public function refresh(Token $token, $resetClaims=false)
    {
        $this->setRefreshFlow();

        $claims = $this->buildRefreshClaims($this->decode($token));

        // Return the new token
        return $this->encode(
            $this->payloadFactory->customClaims($claims)->make($resetClaims)
        );
    }

    /**
     * Build the claims to go into the refreshed token.
     *
     * @param Payload $payload
     * @return array
     */
    protected function buildRefreshClaims(Payload $payload)
    {
        // Get the claims to be persisted from the payload
        $persistentClaims = collect($payload->toArray())
            ->only($this->persistentClaims)
            ->toArray();
        // persist the relevant claims
        return array_merge(
            $this->customClaims,
            $persistentClaims,
            [
                'iat' => $payload['iat'],
            ]
        );
    }

    /**
     * Get the Payload Factory instance.
     *
     * @return Factory
     */
    public function getPayloadFactory()
    {
        return $this->payloadFactory;
    }

    /**
     * Get the JWTProvider instance.
     *
     * @return JWTContract
     */
    public function getJWTProvider()
    {
        return $this->provider;
    }

    /**
     * Set the claims to be persisted when refreshing a token.
     *
     * @param  array  $claims
     * @return $this
     */
    public function setPersistentClaims($claims)
    {
        $this->persistentClaims = $claims;

        return $this;
    }
}
