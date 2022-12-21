<?php

namespace Nicy\Framework\Bindings\JWT\Payload\Claims;

use Nicy\Framework\Bindings\JWT\Exceptions\TokenExpiredException;
use Nicy\Framework\Bindings\JWT\Payload\Concerns\DatetimeTrait;

class Expiration extends Claim
{
    use DatetimeTrait;

    /**
     * {@inheritdoc}
     */
    protected $name = 'exp';

    /**
     * {@inheritdoc}
     */
    public function validatePayload()
    {
        if ($this->isPast($this->getValue())) {
            throw new TokenExpiredException('Token has expired');
        }
    }
}
