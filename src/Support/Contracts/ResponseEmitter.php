<?php

namespace Nicy\Framework\Support\Contracts;

use Psr\Http\Message\ResponseInterface;

interface ResponseEmitter
{
    public function emit(ResponseInterface $response) :void ;
}