<?php

namespace Nicy\Framework\Support\Http\Resources\Json;

use Nicy\Support\Collection;
use Slim\Psr7\Response as SimHttpResponse;

class ResourceResponse
{
    /**
     * The underlying resource.
     *
     * @var mixed
     */
    public $resource;

    /**
     * Create a new resource response.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toResponse()
    {
        $response = new SimHttpResponse();

        $response->getBody()->write(
            json_encode(
                $this->wrap(
                    $this->resource->resolve(),
                    $this->resource->with(),
                    $this->resource->additional
                ),
                $this->resource->jsonOptions()
            ),
        );

        return tap($response, function ($response) {
            $response->original = $this->resource->resource;

            $this->resource->withResponse($response);
        });
    }

    /**
     * Wrap the given data if necessary.
     *
     * @param mixed $data
     * @param array $with
     * @param array $additional
     * @return array
     */
    protected function wrap($data, array $with=[], array $additional=[])
    {
        if ($data instanceof Collection) {
            $data = $data->all();
        }

        if ($this->haveDefaultWrapperAndDataIsUnwrapped($data)) {
            $data = [$this->wrapper() => $data];
        } elseif ($this->haveAdditionalInformationAndDataIsUnwrapped($data, $with, $additional)) {
            $data = [($this->wrapper() ?? 'data') => $data];
        }

        return array_merge_recursive($data, $with, $additional);
    }

    /**
     * Determine if we have a default wrapper and the given data is unwrapped.
     *
     * @param array $data
     * @return bool
     */
    protected function haveDefaultWrapperAndDataIsUnwrapped(array $data)
    {
        return $this->wrapper() && ! array_key_exists($this->wrapper(), $data);
    }

    /**
     * Determine if "with" data has been added and our data is unwrapped.
     *
     * @param array $data
     * @param array $with
     * @param array $additional
     * @return bool
     */
    protected function haveAdditionalInformationAndDataIsUnwrapped(array $data, array $with, array $additional)
    {
        return (! empty($with) || ! empty($additional)) &&
               (! $this->wrapper() ||
                ! array_key_exists($this->wrapper(), $data));
    }

    /**
     * Get the default data wrapper for the resource.
     *
     * @return string
     */
    protected function wrapper()
    {
        return get_class($this->resource)::$wrap;
    }
}
