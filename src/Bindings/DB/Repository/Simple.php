<?php

namespace Nicy\Framework\Bindings\DB\Repository;

use Nicy\Framework\Bindings\DB\Query\Builder;
use Nicy\Framework\Bindings\DB\Repository\Concerns\ForPaginate;
use Nicy\Framework\Main;

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
     * @param array $args
     * @return int
     */
    public function count(...$args)
    {
        return static::query()->count($this->table, ...$args);
    }

    /**
     * @param array $args
     * @return array
     */
    public function all(...$args)
    {
        return static::query()->select($this->table, ...$args);
    }

    /**
     * @param array $args
     * @return array
     */
    public function one(...$args)
    {
        return static::query()->get($this->table, ...$args);
    }

    /**
     * @return Builder
     */
    public function newQuery()
    {
        return Main::instance()->container('db')->connection($this->connection)->simpling();
    }

    /**
     * @param array $rows
     * @return bool
     */
    public function insert(array $rows=[]): bool
    {
        static::query()->insert($this->table, $rows);

        return true;
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return static::query()->id();
    }

    /**
     * @param array $conditions
     * @return bool
     */
    public function delete(array $conditions=[]): bool
    {
        static::query()->delete($this->table, $conditions);

        return true;
    }

    /**
     * @param array $attributes
     * @param array $conditions
     * @return bool
     */
    public function update(array $attributes=[], array $conditions=[]): bool
    {
        static::query()->update($this->table, $attributes, $conditions);

        return true;
    }

    /**
     * @return Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }
}