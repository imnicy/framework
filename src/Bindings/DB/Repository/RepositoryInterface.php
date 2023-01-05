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
    public function count(...$args): int ;

    /**
     * Get all entries from table with conditions
     *
     * @param array $args
     * @return Collection
     */
    public function all(...$args): Collection ;

    /**
     * Get a row from table with conditions
     *
     * @param array $args
     * @return RepositoryInterface
     */
    public function one(...$args): ?RepositoryInterface ;

    /**
     * Find a row from table with primary key
     *
     * @param string|int $id
     * @param string|array $columns
     * @return RepositoryInterface
     */
    public function find($id, $columns=null): ?RepositoryInterface ;

    /**
     * Create a new row
     *
     * @param array $attributes
     * @return RepositoryInterface
     */
    public function create(array $attributes=[]): RepositoryInterface ;

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
    public function update(array $attributes=[], array $conditions=[]): bool ;

    /**
     * Save changes attributes
     *
     * @param array $options
     * @return bool
     */
    public function save(array $options=[]): bool ;

    /**
     * Destroy items from table
     *
     * @param array $ids
     * @return bool
     */
    public function destroy(array $ids=[]): bool ;

    /**
     * Fill attributes for save
     *
     * @param array $attributes
     * @return RepositoryInterface
     */
    public function fill(array $attributes=[]): RepositoryInterface ;

    /**
     * With any attributes
     *
     * @param array|string $attributes
     * @return RepositoryInterface
     */
    public function with($attributes): RepositoryInterface ;

    /**
     * Load any relationships
     *
     * @param string|array $relations
     * @return RepositoryInterface
     */
    public function load($relations): RepositoryInterface ;

    /**
     * Get a query builder instance
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery();
}