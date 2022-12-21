<?php

namespace Nicy\Framework\Bindings\Encryption\Contracts;

interface Encrypter
{
    /**
     * Encrypt the given value.
     *
     * @param  mixed  $value
     * @param  bool  $serialize
     * @return mixed
     */
    public function encrypt($value, bool $serialize=true);

    /**
     * Decrypt the given value.
     *
     * @param  mixed  $payload
     * @param  bool  $unserialize
     * @return mixed
     */
    public function decrypt($payload, bool $unserialize=true);
}
