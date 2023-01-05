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
    public function count(...$args): int ;

    /**
     * Get all entries from table with conditions
     *
     * @param array $args
     * @return array
     */
    public function all(...$args): array ;

    /**
     * Get a row from table with conditions
     *
     * @param array $args
     * @return array
     */
    public function one(...$args): ?array ;

    /**
     * Insert rows
     *
     * @param array $rows
     * @return bool
     */
    public function insert(array $rows=[]): bool ;

    /**
     * Get insert id
     *
     * @return string
     */
    public function id(): ?string ;

    /**
     * Delete the row with conditions
     *
     * @param array $conditions
     * @return bool
     */
    public function delete(array $conditions=[]): bool ;

    /**
     * Update table with conditions
     *
     * @param array $attributes
     * @param array $conditions
     * @return bool
     */
    public function update(array $attributes=[], array $conditions=[]): bool ;

    /**
     * Get a query builder instance
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery();
}