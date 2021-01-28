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
     * @param string|array $join
     * @param string|array $columns
     * @param array $conditions
     * @param int $page
     * @param int $perPage
     * @param string $urlPattern
     * @return Paginator
     */
    public function paginate($join=null, $columns=null, $conditions=null, int $page=1, int $perPage=null, $urlPattern=null)
    {
        unset($conditions['LIMIT']);

        if ($urlPattern) {
            $this->setUrlPattern($urlPattern);
        }

        if ($perPage) {
            $this->setPerPage($perPage);
        }

        if ($conditions == null) {
            $total = $this->count('*', $columns);
        }
        else {
            $total = $this->count($join, '*', $conditions);
        }

        if ($total) {
            $limit = ['LIMIT' => [($page - 1) * $perPage, $perPage]];

            if ($conditions == null) {
                $columns = $columns + $limit;
            }
            else {
                $conditions = $conditions + $limit;
            }

            $items = $this->all($join, $columns, $conditions);
        }
        else {
            $items = new Collection();
        }

        return new Paginator($items, $total, $page, $this->perPage, $this->urlPattern);
    }
}