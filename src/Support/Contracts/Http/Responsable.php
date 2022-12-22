<?php

namespace Nicy\Framework\Support\Contracts\Http;

interface Responsable
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toResponse();

    /**
     * HTTP response should be a json
     *
     * @return bool
     */
    public function shouldBeJson(): bool ;
}