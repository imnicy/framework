<?php

namespace Nicy\Framework\Bindings\DB\Repository;

interface RepositoryInterface
{
    /**
     * Get all entry count from table
     *
     * @param array $args
     * @return int
     */
    public function count(...$args);

    /**
     * Get all entries from table with conditions
     *
     * @param array $args
     * @return mixed
     */
    public function all(...$args);

    /**
     * Get a row from table with conditions
     *
     * @param array $args
     * @return mixed
     */
    public function one(...$args);

    /**
     * Create a new row
     *
     * @param array $attributes
     * @return RepositoryInterface
     */
    public function create($attributes=[]): RepositoryInterface ;

    /**
     * Delete the row with conditions
     *
     * @return bool
     */
    public function delete(): bool ;

    /**
     * Update table with conditions
     *
     * @param array $attributes
     * @param array $conditions
     * @return bool
     */
    public function update($attributes=[], $conditions=[]): bool ;

    /**
     * Save changes attributes
     *
     * @param array $options
     * @return bool
     */
    public function save($options=[]): bool ;

    /**
     * Destroy items from table
     *
     * @param array $ids
     * @return bool
     */
    public function destroy($ids=[]): bool ;

    /**
     * Fill attributes for save
     *
     * @param array $attributes
     * @return RepositoryInterface
     */
    public function fill($attributes=[]): RepositoryInterface ;

    /**
     * Get a query builder instance
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery();
}