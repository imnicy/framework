<?php

namespace Nicy\Framework\Bindings\JWT\Payload\Claims;

use Nicy\Framework\Bindings\JWT\Exceptions\ClaimException;
use Nicy\Framework\Bindings\JWT\Exceptions\TokenExpiredException;
use Nicy\Framework\Bindings\JWT\Exceptions\TokenInvalidException;
use Nicy\Framework\Bindings\JWT\Payload\Concerns\DatetimeTrait;

class IssuedAt extends Claim
{
    use DatetimeTrait {
        validateCreate as commonValidateCreate;
    }

    /**
     * {@inheritdoc}
     */
    protected $name = 'iat';

    /**
     * {@inheritdoc}
     */
    public function validateCreate($value)
    {
        $this->commonValidateCreate($value);

        if ($this->isFuture($value)) {
            throw new ClaimException($this);
        }
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function validatePayload()
    {
        if ($this->isFuture($this->getValue())) {
            throw new TokenInvalidException('Issued At (iat) timestamp cannot be in the future');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateRefresh($refreshTTL)
    {
        if ($this->isPast($this->getValue() + $refreshTTL * 60)) {
            throw new TokenExpiredException('Token has expired and can no longer be refreshed');
        }
    }
}
