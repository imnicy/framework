<?php

namespace Nicy\Framework\Bindings\DB\Repository;

use Nicy\Framework\Bindings\DB\Query\Builder;
use Nicy\Framework\Bindings\DB\Repository\Concerns\ForPaginate;

abstract class Simple implements SimpleRepositoryInterface
{
    use ForPaginate;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @return \Nicy\Framework\Bindings\DB\Repository\Simple
     */
    public static function instance()
    {
        return new static;
    }

    /**
     * @param array $conditions
     * @param string $columns
     *
     * @return array|bool|mixed
     */
    public function all(array $conditions = [], $columns = '*')
    {
        return static::query()->select($this->table, $columns, $conditions);
    }

    /**
     * @param array $conditions
     * @param string $columns
     *
     * @return mixed
     */
    public function one(array $conditions = [], $columns = '*')
    {
        return static::query()->get($this->table, $columns, $conditions);
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return container('db')->connection($this->connection)->simpling();
    }

    /**
     * @param array $attributes
     *
     * @return bool
     */
    public function create(array $attributes = []) :bool
    {
        return static::query()->create($this->table, $attributes);
    }

    /**
     * @param array $condition
     *
     * @return bool
     */
    public function delete($condition = []): bool
    {
        return static::query()->delete($this->table, $condition);
    }

    /**
     * @param array $attributes
     * @param array $conditions
     *
     * @return bool
     */
    public function update($attributes = [], $conditions = []): bool
    {
        return static::query()->update($this->table, $conditions);
    }

    /**
     * @return Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }
}