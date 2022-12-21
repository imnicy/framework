<?php

namespace Nicy\Framework\Bindings\JWT\Contracts;

interface Claim
{
    /**
     * Set the claim value, and call a validate method.
     *
     * @param mixed $value
     * @return mixed
     */
    public function setValue($value);

    /**
     * Get the claim value.
     *
     * @return mixed
     */
    public function getValue();

    /**
     * Set the claim name.
     *
     * @param string $name
     * @return mixed
     */
    public function setName($name);

    /**
     * Get the claim name.
     *
     * @return string
     */
    public function getName();

    /**
     * Validation the Claim value.
     *
     * @param mixed $value
     * @return bool
     */
    public function validateCreate($value);
}
