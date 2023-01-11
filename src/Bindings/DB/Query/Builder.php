<?php

namespace Nicy\Framework\Bindings\DB\Query;

use PDOStatement;
use Nicy\Framework\Bindings\DB\Repository\RepositoryInterface;
use Nicy\Framework\Exceptions\QueryException;
use Nicy\Framework\Main;
use Medoo\Medoo;

class Builder extends Medoo
{
    /**
     * Is simple repository mode?
     *
     * @var bool
     */
    public $simple = true;

    /**
     * @var bool
     */
    public static $errorThrowable = true;

    /**
     * @var \Nicy\Framework\Bindings\DB\Repository\Base
     */
    protected $repository;

    /**
     * @var string
     */
    protected $connection;

    /**
     * @param string $name
     * @return $this
     */
    public function setConnection(string $name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Query handler
     *
     * @param string $table
     * @param string|array $join
     * @param array $columns
     * @param array $where
     * @return array|\Nicy\Framework\Bindings\DB\Repository\Collection
     */
    public function all($table, $join=null, $columns=null, $where=null)
    {
        $result = parent::select($table, $join ?: '*', $columns, $where);

        if (false === $this->isSimple() && is_array($result)) {
            return $this->hydrate($result);
        }

        return $result;
    }

    /**
     * Result from query handler
     *
     * @param string $table
     * @param string|array $join
     * @param array $columns
     * @param array $where
     * @return array|\Nicy\Framework\Bindings\DB\Repository\RepositoryInterface
     */
    public function one($table, $join=null, $columns=null, $where=null)
    {
        $result = parent::get($table, $join ?: '*', $columns, $where);

        if (false === $this->isSimple() && is_array($result)) {
            return $this->newRepositoryInstance($result)->with($this->repository->getWiths());
        }

        return $result;
    }

    /**
     * @param string $table
     * @param string|array $join
     * @param array $column
     * @param array $where
     * @return int
     */
    public function count($table, $join=null, $column=null, $where=null): int
    {
        return parent::count($table, $join, parent::isJoin($join) ? '*' : $column, $where);
    }

    /**
     * @return bool
     */
    public function isSimple()
    {
        return $this->simple;
    }

    /**
     * If statement execution fails, an exception will throw
     *
     * @param string|null $sql
     * @return void
     */
    protected function prepareQueryWithError(string $sql=null)
    {
        if (($error = $this->error) && $error[1] && static::$errorThrowable) {
            // Dispatch a query error event, if query has error info.
            Main::instance()->container('events')->dispatch('db.query.error', $error);

            throw new QueryException($error[2]);
        }

        throw new QueryException($sql);
    }

    /**
     * Record execution statements, determine whether execution fails, and dispatch events
     *
     * @param string $statement
     * @param array $map
     * @param callable|null $callback
     * @return bool|PDOStatement
     */
    public function exec($statement, $map=[], callable $callback=null) : ?PDOStatement
    {
        $sql = $statement;
        $start = microtime(true);

        try {
            $statement = parent::exec($statement, $map);
        }
        catch (\PDOException $e) {
            $this->prepareQueryWithError(parent::generate($sql, $map));
        }

        if ($statement) {
            // Dispatch a query sql statements log, when sql running.
            Main::instance()->container('events')->dispatch(
                'db.query.sql', new QueryExecuted(
                    parent::generate($sql, $map), $map, $this->getElapsedTime($start), $this->connection
                )
            );
        }

        return $statement;
    }

    /**
     * Get the elapsed time since a given starting point.
     *
     * @param int $start
     * @return float
     */
    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    /**
     * Create a collection of repositories from plain arrays.
     *
     * @param array $items
     * @return \Nicy\Framework\Bindings\DB\Repository\Collection
     */
    public function hydrate(array $items)
    {
        $repository = $this->repository->newInstance();

        return $repository->newCollection(array_map(function ($item) {
            return $this->newRepositoryInstance($item)->with($this->repository->getWiths());
        }, $items));
    }

    /**
     * Get the repository instance being queried.
     *
     * @return \Nicy\Framework\Bindings\DB\Repository\RepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set a repository instance for the repository being queried.
     *
     * @param \Nicy\Framework\Bindings\DB\Repository\RepositoryInterface $repository
     * @return Builder
     */
    public function setRepository(RepositoryInterface $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set the simple to given state
     *
     * @param bool $state
     * @return Builder
     */
    public function simpling(bool $state=true)
    {
        $this->simple = $state;

        return $this;
    }

    /**
     * Create a new instance of the repository being queried.
     *
     * @param array $attributes
     * @return \Nicy\Framework\Bindings\DB\Repository\RepositoryInterface|static
     */
    public function newRepositoryInstance(array $attributes=[])
    {
        return $this->repository::unguarded(function() use($attributes) {
            return $this->repository->newInstance($attributes, true)->setConnection(
                $this->repository->connection()
            );
        });
    }
}