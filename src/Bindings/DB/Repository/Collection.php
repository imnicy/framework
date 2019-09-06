<?php

namespace Nicy\Framework\Bindings\DB\Repository;

use Nicy\Support\Arr;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Support\Collection as SupportCollection;

class Collection extends SupportCollection
{
    /**
     * Find a repository in the collection by key.
     *
     * @param mixed $key
     * @param mixed $default
     *
     * @return \Nicy\Framework\Bindings\DB\Repository\Base|static|null
     */
    public function find($key, $default = null)
    {
        if ($key instanceof Base) {
            $key = $key->getKey();
        }

        if ($key instanceof Arrayable) {
            $key = $key->toArray();
        }

        if (is_array($key)) {
            if ($this->isEmpty()) {
                return new static;
            }

            return $this->whereIn($this->first()->getKeyName(), $key);
        }

        return Arr::first($this->items, function ($repository) use ($key) {
            return $repository->getPrimary() == $key;
        }, $default);
    }

    /**
     * Determine if a key exists in the collection.
     *
     * @param mixed $key
     * @param mixed $operator
     * @param mixed $value
     *
     * @return bool
     */
    public function contains($key, $operator = null, $value = null)
    {
        if (func_num_args() > 1 || $this->useAsCallable($key)) {
            return parent::contains(...func_get_args());
        }

        if ($key instanceof Base) {
            return parent::contains(function ($repository) use ($key) {
                return $repository->is($key);
            });
        }

        return parent::contains(function ($repository) use ($key) {
            return $repository->getPrimary() == $key;
        });
    }

    /**
     * Get the array of primary keys.
     *
     * @return array
     */
    public function repositoriesKeys()
    {
        return array_map(function ($repository) {
            return $repository->getPrimary();
        }, $this->items);
    }

    /**
     * Merge the collection with the given items.
     *
     * @param \ArrayAccess|array $items
     *
     * @return static
     */
    public function merge($items)
    {
        $dictionary = $this->getDictionary();

        foreach ($items as $item) {
            $dictionary[$item->getKey()] = $item;
        }

        return new static(array_values($dictionary));
    }

    /**
     * Run a map over each of the items.
     *
     * @param callable $callback
     *
     * @return \Nicy\Support\Collection|static
     */
    public function map(callable $callback)
    {
        $result = parent::map($callback);

        return $result->contains(function ($item) {
            return ! $item instanceof Base;
        }) ? $result->toBase() : $result;
    }

    /**
     * Diff the collection with the given items.
     *
     * @param \ArrayAccess|array $items
     *
     * @return static
     */
    public function diff($items)
    {
        $diff = new static;

        $dictionary = $this->getDictionary($items);

        foreach ($this->items as $item) {
            if (! isset($dictionary[$item->getPrimary()])) {
                $diff->add($item);
            }
        }

        return $diff;
    }

    /**
     * Intersect the collection with the given items.
     *
     * @param \ArrayAccess|array $items
     *
     * @return static
     */
    public function intersect($items)
    {
        $intersect = new static;

        $dictionary = $this->getDictionary($items);

        foreach ($this->items as $item) {
            if (isset($dictionary[$item->getPrimary()])) {
                $intersect->add($item);
            }
        }

        return $intersect;
    }

    /**
     * Return only unique items from the collection.
     *
     * @param string|callable|null $key
     * @param bool $strict
     *
     * @return static|\Nicy\Support\Collection
     */
    public function unique($key = null, $strict = false)
    {
        if (! is_null($key)) {
            return parent::unique($key, $strict);
        }

        return new static(array_values($this->getDictionary()));
    }

    /**
     * Returns only the repositories from the collection with the specified keys.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function only($keys)
    {
        if (is_null($keys)) {
            return new static($this->items);
        }

        $dictionary = Arr::only($this->getDictionary(), $keys);

        return new static(array_values($dictionary));
    }

    /**
     * Returns all repositories in the collection except the repositories with specified keys.
     *
     * @param mixed $keys
     *
     * @return static
     */
    public function except($keys)
    {
        $dictionary = Arr::except($this->getDictionary(), $keys);

        return new static(array_values($dictionary));
    }

    /**
     * Make the given, typically visible, attributes hidden across the entire collection.
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function makeHidden($attributes)
    {
        return $this->each->addHidden($attributes);
    }

    /**
     * Make the given, typically hidden, attributes visible across the entire collection.
     *
     * @param array|string $attributes
     *
     * @return $this
     */
    public function makeVisible($attributes)
    {
        return $this->each->makeVisible($attributes);
    }

    /**
     * Get a dictionary keyed by primary keys.
     *
     * @param \ArrayAccess|array|null $items
     *
     * @return array
     */
    public function getDictionary($items = null)
    {
        $items = is_null($items) ? $this->items : $items;

        $dictionary = [];

        foreach ($items as $value) {
            $dictionary[$value->getKey()] = $value;
        }

        return $dictionary;
    }

    /**
     * Get an array with the values of a given key.
     *
     * @param string $value
     * @param string|null $key
     *
     * @return \Nicy\Support\Collection
     */
    public function pluck($value, $key = null)
    {
        return $this->toBase()->pluck($value, $key);
    }

    /**
     * Get the keys of the collection items.
     *
     * @return \Nicy\Support\Collection
     */
    public function keys()
    {
        return $this->toBase()->keys();
    }

    /**
     * Zip the collection together with one or more arrays.
     *
     * @param mixed ...$items
     *
     * @return \Nicy\Support\Collection
     */
    public function zip($items)
    {
        return call_user_func_array([$this->toBase(), 'zip'], func_get_args());
    }

    /**
     * Collapse the collection of items into a single array.
     *
     * @return \Nicy\Support\Collection
     */
    public function collapse()
    {
        return $this->toBase()->collapse();
    }

    /**
     * Get a flattened array of the items in the collection.
     *
     * @param int $depth
     *
     * @return \Nicy\Support\Collection
     */
    public function flatten($depth = INF)
    {
        return $this->toBase()->flatten($depth);
    }

    /**
     * Flip the items in the collection.
     *
     * @return \Nicy\Support\Collection
     */
    public function flip()
    {
        return $this->toBase()->flip();
    }

    /**
     * Pad collection to the specified length with a value.
     *
     * @param int $size
     * @param mixed $value
     *
     * @return \Nicy\Support\Collection
     */
    public function pad($size, $value)
    {
        return $this->toBase()->pad($size, $value);
    }

    /**
     * Get the comparison function to detect duplicates.
     *
     * @param bool $strict
     *
     * @return \Closure
     */
    protected function duplicateComparator($strict)
    {
        return function ($a, $b) {
            return $a->is($b);
        };
    }
}