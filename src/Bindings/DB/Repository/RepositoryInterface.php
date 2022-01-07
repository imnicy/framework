<?php

namespace Nicy\Framework\Bindings\DB\Repository;

interface RepositoryInterface
{
    /**
     * Get all entry count from table
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
    public function update($attributes=[], $conditions=[]): bool ;

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
     * Get a query builder instance
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery();
}