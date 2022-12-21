<?php

namespace Nicy\Framework\Bindings\JWT;

use Nicy\Framework\Bindings\JWT\Validators\TokenValidator;

class Token
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var Payload
     */
    private $payload;

    /**
     * Create a new JSON Web Token.
     *
     * @param string $value
     * @param Payload $payload
     * @return void
     * @throws Exceptions\TokenInvalidException
     */
    public function __construct($value, $payload=null)
    {
        $this->value = (string) (new TokenValidator)->check($value);
        $this->payload = $payload;
    }

    /**
     * @return Payload
     */
    public function payload()
    {
        return $this->payload;
    }

    /**
     * Get the token.
     *
     * @return string
     */
    public function get(): string
    {
        return $this->value;
    }

    /**
     * Get the token when casting to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->get();
    }
}
