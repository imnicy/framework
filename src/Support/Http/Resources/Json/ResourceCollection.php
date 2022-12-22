<?php

namespace Nicy\Framework\Support\Http\Resources\Json;

use Countable;
use IteratorAggregate;
use Nicy\Framework\Support\Http\Resources\CollectsResources;

class ResourceCollection extends JsonResource implements Countable, IteratorAggregate
{
    use CollectsResources;

    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects;

    /**
     * The mapped collection instance.
     *
     * @var \Nicy\Support\Collection
     */
    public $collection;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param array $options
     * @return void
     */
    public function __construct($resource, array $options=[])
    {
        parent::__construct($resource, $options);

        $this->resource = $this->collectResource($resource);
    }

    /**
     * Return the count of items in the resource collection.
     *
     * @return int
     */
    public function count()
    {
        return $this->collection->count();
    }

    /**
     * Transform the resource into a JSON array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->collection->toArray();
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toResponse()
    {
        return parent::toResponse();
    }
}
