<?php

namespace Nicy\Framework\Bindings\JWT\Contracts;

interface Subject
{
    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return string
     */
    public function getIdentifier() :string ;

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getCustomClaims() :array ;
}
