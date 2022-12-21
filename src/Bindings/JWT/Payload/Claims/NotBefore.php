<?php

namespace Nicy\Framework\Bindings\JWT\Payload\Claims;

use Nicy\Framework\Bindings\JWT\Exceptions\TokenInvalidException;
use Nicy\Framework\Bindings\JWT\Payload\Concerns\DatetimeTrait;

class NotBefore extends Claim
{
    use DatetimeTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'nbf';

    /**
     * {@inheritdoc}
     */
    public function validatePayload()
    {
        if ($this->isFuture($this->getValue())) {
            throw new TokenInvalidException('Not Before (nbf) timestamp cannot be in the future');
        }
    }
}
