<?php

namespace Nicy\Framework\Bindings\DB\Repository\Concerns;

use Nicy\Framework\Bindings\DB\Pagination\Paginator;
use Nicy\Framework\Bindings\DB\Repository\Collection;

trait ForPaginate
{
    /**
     * The number of models to return for pagination.
     *
     * @var int
     */
    protected $perPage = 15;

    /**
     * @var string
     */
    protected $urlPattern = '?page=(:num)';

    /**
     * Get the number of models to return per page.
     *
     * @return int
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * Set the number of models to return per page.
     *
     * @param int $perPage
     * @return $this
     */
    public function setPerPage($perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @param string $pattern
     * @return $this
     */
    public function setUrlPattern($pattern)
    {
        $this->urlPattern = $pattern;

        return $this;
    }

    /**
     * Get items for pagination
     *
     * @param int $page
     * @param array $conditions
     * @param string $columns
     * @param int|null $perPage
     * @return Paginator
     */
    public function paginate(int $page = 1, array $conditions=[], $columns='*', int $perPage=null)
    {
        unset($conditions['LIMIT']);

        $perPage = $perPage ?: $this->getPerPage();

        $total = $this->count($conditions, $columns);

        if ($total) {
            $items = $this->all($conditions + ['LIMIT' => [($page - 1) * $perPage, $perPage]], $columns);
        }
        else {
            $items = new Collection();
        }

        return new Paginator($items, $total, $perPage, $page, $this->urlPattern);
    }
}