<?php

namespace Nicy\Framework\Bindings\DB\Repository;

interface SimpleRepositoryInterface
{
    /**
     * Get all counts from table with conditions
     *
     * @param array $conditions
     * @param string $columns
     * @return int
     */
    public function count(array $conditions=[], $columns='*');

    /**
     * Get all entries from table with conditions
     *
     * @param array $conditions
     * @param string $columns
     * @return mixed
     */
    public function all(array $conditions=[], $columns='*');

    /**
     * Get a row from table with conditions
     *
     * @param array $conditions
     * @param string $columns
     * @return mixed
     */
    public function one(array $conditions=[], $columns='*');

    /**
     * Create a new row
     *
     * @param array $attributes
     * @return bool
     */
    public function create(array $attributes=[]) :bool ;

    /**
     * Delete the row with conditions
     *
     * @param array $conditions
     * @return bool
     */
    public function delete($conditions =[]) :bool ;

    /**
     * Update table with conditions
     *
     * @param array $attributes
     * @param array $conditions
     * @return bool
     */
    public function update($attributes=[], $conditions=[]) :bool ;

    /**
     * Get a query builder instance
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery();
}