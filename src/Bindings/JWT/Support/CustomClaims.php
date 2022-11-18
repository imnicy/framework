<?php

namespace Nicy\Framework\Bindings\JWT\Support;

trait CustomClaims
{
    /**
     * Custom claims.
     *
     * @var array
     */
    protected $customClaims = [];

    /**
     * Set the custom claims.
     *
     * @param array $customClaims
     * @return $this
     */
    public function customClaims($customClaims)
    {
        $this->customClaims = $customClaims;
        return $this;
    }

    /**
     * Alias to set the custom claims.
     *
     * @param array $customClaims
     * @return $this
     */
    public function claims($customClaims)
    {
        return $this->customClaims($customClaims);
    }

    /**
     * Get the custom claims.
     *
     * @return array
     */
    public function getCustomClaims()
    {
        return $this->customClaims;
    }
}
