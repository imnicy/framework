<?php

namespace Nicy\Framework\Bindings\JWT\Exceptions;

use Exception;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Claim;

class ClaimException extends JWTException
{
    /**
     * Constructor.
     *
     * @param Claim $claim
     * @param int $code
     * @param Exception|null $previous
     * @return void
     */
    public function __construct(Claim $claim, $code = 0, Exception $previous = null)
    {
        parent::__construct('Invalid value provided for claim ['.$claim->getName().']', $code, $previous);
    }
}
