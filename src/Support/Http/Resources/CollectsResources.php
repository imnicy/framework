<?php

namespace Nicy\Framework\Support\Http\Resources;

use ReflectionClass;
use Nicy\Support\Collection;
use Nicy\Support\Str;

trait CollectsResources
{
    /**
     * Map the given collection resource into its individual resources.
     *
     * @param  mixed  $resource
     * @return \Nicy\Support\Collection|\Nicy\Framework\Support\Http\Resources\MissingValue
     */
    protected function collectResource($resource)
    {
        if ($resource instanceof MissingValue) {
            return $resource;
        }

        if (is_array($resource)) {
            $resource = new Collection($resource);
        }

        $collects = $this->collects();

        $this->collection = $collects && ! $resource->first() instanceof $collects
            ? $resource->map(function($value) use ($collects) {
                return new $collects($value, $this->options);
            })
            : $resource->toBase();

        return $this->collection;
    }

    /**
     * Get the resource that this resource collects.
     *
     * @return string|null
     */
    protected function collects()
    {
        if ($this->collects) {
            return $this->collects;
        }

        if (Str::endsWith(class_basename($this), 'Collection') &&
            (class_exists($class = Str::replaceLast('Collection', '', get_class($this))) ||
             class_exists($class = Str::replaceLast('Collection', 'Resource', get_class($this))))) {
            return $class;
        }

        return null;
    }

    /**
     * Get the JSON serialization options that should be applied to the resource response.
     *
     * @return int
     * @throws \ReflectionException
     */
    public function jsonOptions()
    {
        $collects = $this->collects();

        if (! $collects) {
            return 0;
        }

        return (new ReflectionClass($collects))
                  ->newInstanceWithoutConstructor()
                  ->jsonOptions();
    }

    /**
     * Get an iterator for the resource collection.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return $this->collection->getIterator();
    }
}
