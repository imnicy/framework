<?php

namespace Nicy\Framework\Bindings\JWT\Contracts\Providers;

interface JWT
{
    /**
     * @param array $payload
     * @return string
     */
    public function encode($payload);

    /**
     * @param string $token
     * @return array
     */
    public function decode($token);
}
