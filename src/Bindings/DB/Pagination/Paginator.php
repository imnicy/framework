<?php

namespace Nicy\Framework\Bindings\DB\Pagination;

use Countable;
use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;
use Nicy\Support\Contracts\Jsonable;
use Nicy\Support\Contracts\Arrayable;
use JasonGrimes\Paginator as PaginatorBuilder;
use Nicy\Framework\Bindings\DB\Repository\Collection;

class Paginator extends PaginatorBuilder implements Arrayable, Jsonable, JsonSerializable, IteratorAggregate, Countable, ArrayAccess
{
    /**
     * @var \Nicy\Framework\Bindings\DB\Repository\Collection
     */
    protected $items;

    /**
     * Paginator constructor.
     *
     * @param array|\ArrayAccess $items
     * @param int $total
     * @param int $page
     * @param int $perPage
     * @param string $urlPattern
     */
    public function __construct($items, int $total, int $page, int $perPage, string $urlPattern='')
    {
        parent::__construct($total, $perPage, $page, $urlPattern);

        $this->setItems($items);
    }

    /**
     * @return Collection
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Render the paginator using the given view.
     *
     * @return string
     */
    public function links()
    {
        return $this->toHtml();
    }

    /**
     * Set the items for the paginator.
     *
     * @param array|\ArrayAccess $items
     * @return void
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->items = $this->items->slice(0, $this->itemsPerPage);
    }

    /**
     * Get the number of the first item in the slice.
     *
     * @return int
     */
    public function firstItem()
    {
        return count($this->items) > 0 ? ($this->currentPage - 1) * $this->itemsPerPage + 1 : null;
    }

    /**
     * Get the number of the last item in the slice.
     *
     * @return int
     */
    public function lastItem()
    {
        return count($this->items) > 0 ? $this->firstItem() + $this->count() - 1 : null;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'total'             => $this->getTotalItems(),
            'current_page'      => $this->getCurrentPage(),
            'data'              => $this->items->toArray(),
            'first_page_url'    => $this->getPageUrl(1),
            'from'              => $this->firstItem(),
            'next_page_url'     => $this->getNextUrl(),
            'per_page'          => $this->getItemsPerPage(),
            'prev_page_url'     => $this->getPrevUrl(),
            'to'                => $this->lastItem(),
        ];
    }

    /**
     * Get the number of items for the current page.
     *
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return $this->items->getIterator();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options=0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Determine if the given item exists.
     *
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->items->has($offset);
    }

    /**
     * Get the item at the given offset.
     *
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items->get($offset);
    }

    /**
     * Set the item at the given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->items->put($offset, $value);
    }

    /**
     * Unset the item at the given key.
     *
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->items->forget($offset);
    }
}