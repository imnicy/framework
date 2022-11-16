<?php

namespace Nicy\Framework\Bindings\DB\Repository;

interface SimpleRepositoryInterface
{
    /**
     * Get all counts from table with conditions
     *
     * @param array $args
     * @return int
     */
    public function count(...$args);

    /**
     * Get all entries from table with conditions
     *
     * @param array $args
     * @return array
     */
    public function all(...$args);

    /**
     * Get a row from table with conditions
     *
     * @param array $args
     * @return array
     */
    public function one(...$args);

    /**
     * Insert rows
     *
     * @param array $rows
     * @return bool
     */
    public function insert($rows=[]): bool ;

    /**
     * Get insert id
     *
     * @return int
     */
    public function id(): int ;

    /**
     * Delete the row with conditions
     *
     * @param array $conditions
     * @return bool
     */
    public function delete($conditions=[]): bool ;

    /**
     * Update table with conditions
     *
     * @param array $attributes
     * @param array $conditions
     * @return bool
     */
    public function update($attributes=[], $conditions=[]): bool ;

    /**
     * Get a query builder instance
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery();
}