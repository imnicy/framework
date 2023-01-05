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
    public function setPerPage(int $perPage)
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * @param string $pattern
     * @return $this
     */
    public function setUrlPattern(string $pattern)
    {
        $this->urlPattern = $pattern;

        return $this;
    }

    /**
     * Get items for pagination
     *
     * @param array $args
     * @param int $page
     * @param int|null $perPage
     * @param string|null $urlPattern
     * @return Paginator
     */
    public function paginate(array $args=[], int $page=1, int $perPage=null, string $urlPattern=null)
    {
        if ($urlPattern) {
            $this->setUrlPattern($urlPattern);
        }
        if ($perPage) {
            $this->setPerPage($perPage);
        }
        $perPage = $this->getPerPage();

        list($args, $conditions) = $this->parseArgs($args);

        $total = $this->count(...$this->getBuilderArgs($args, $conditions));
        if ($total) {
            $conditions['LIMIT'] = [($page - 1) * $perPage, $perPage];
            $items = $this->all(...$this->getBuilderArgs($args, $conditions));
        }
        else {
            $items = new Collection();
        }

        return new Paginator($items, $total, $page, $this->perPage, $this->urlPattern);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function parseArgs(array $args): array
    {
        if (! isset($args[1])) {
            $conditions = [];
            $args = [
               $args[0] ?? '*'
            ];
        }
        else if (! isset($args[2])) {
            $conditions = $args[1];
            $args = [
                $args[0]
            ];
        }
        else {
            $conditions = $args[2];
            $args = [
                $args[0], $args[1]
            ];
        }
        return [$args, $conditions];
    }

    /**
     * @param array $args
     * @param array $conditions
     * @return array
     */
    protected function getBuilderArgs(array $args, array $conditions): array
    {
        return array_merge($args, [$conditions]);
    }
}