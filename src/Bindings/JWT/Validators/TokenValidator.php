<?php

namespace Nicy\Framework\Bindings\JWT\Validators;

use Nicy\Framework\Bindings\JWT\Exceptions\TokenInvalidException;

class TokenValidator extends Validator
{
    /**
     * Check the structure of the token.
     *
     * @param string $value
     * @return string
     * @throws TokenInvalidException
     */
    public function check($value)
    {
        return $this->validateStructure($value);
    }

    /**
     * @param string $token
     * @return string
     *@throws TokenInvalidException
     */
    protected function validateStructure($token)
    {
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            throw new TokenInvalidException('Wrong number of segments');
        }

        $parts = array_filter(array_map('trim', $parts));

        if (count($parts) !== 3 || implode('.', $parts) !== $token) {
            throw new TokenInvalidException('Malformed token');
        }

        return $token;
    }
}
