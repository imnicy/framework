<?php

namespace Nicy\Framework\Support\Http\Resources\Json;

class AnonymousResourceCollection extends ResourceCollection
{
    /**
     * The name of the resource being collected.
     *
     * @var string
     */
    public $collects;

    /**
     * Create a new anonymous resource collection.
     *
     * @param mixed $resource
     * @param string $collects
     * @param array $options
     * @return void
     */
    public function __construct($resource, string $collects, array $options=[])
    {
        $this->collects = $collects;

        parent::__construct($resource, $options);
    }
}
