<?php

namespace Nicy\Framework\Exceptions;

use RuntimeException;

class JsonEncodingException extends RuntimeException
{
    /**
     * Create a new JSON encoding exception for the resource.
     *
     * @param \Nicy\Framework\Support\Http\Resources\Json\JsonResource $resource
     * @param string $message
     * @return static
     */
    public static function forResource($resource, $message)
    {
        return new static('Error encoding resource ['.get_class($resource).'] to JSON: '.$message);
    }
}
