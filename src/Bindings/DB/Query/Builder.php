<?php

namespace Nicy\Framework\Bindings\DB\Query;

use Nicy\Framework\Exceptions\QueryException;
use Nicy\Framework\Bindings\DB\Repository\Base;
use Nicy\Framework\Main;
use Medoo\Medoo;

class Builder extends Medoo
{
    /**
     * Is simple repository mode?
     *
     * @var bool
     */
    public $simple = false;

    public static $errorThrowable = true;

    /**
     * @var \Framework\Bindings\DB\Repository\Base
     */
    protected $repository;

    public function select($table, $join, $columns = null, $where = null)
    {
        $result = parent::select($table, $join, $columns, $where);

        if (false === $this->isSimple() && is_array($result)) {
            return $this->hydrate($result);
        }

        return $result;
    }

    public function get($table, $join = null, $columns = null, $where = null)
    {
        $result = parent::get($table, $join, $columns, $where);

        if (false === $this->isSimple() && is_array($result)) {
            return $this->newRepositoryInstance($result)->with($this->repository->getWiths());
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isSimple()
    {
        return (bool) $this->simple;
    }

    /**
     * If statement execution fails, an exception will thrown
     *
     * @return void
     */
    protected function prepareQueryWithError()
    {
        if (($error = $this->error()) && $error[1] && static::$errorThrowable) {

            // Dispatch a query error event, if query has error info.
            Main::getInstance()->container('events')->dispatch('db.query.error', $error);

            throw new QueryException($error[2]);
        }
    }

    /**
     * Record execution statements, determine whether execution fails, and dispatch events
     *
     * @param string $query
     * @param array $map
     *
     * @return bool|\PDOStatement
     */
    public function exec($query, $map = [])
    {
        // Dispatch a query sql statements log, when sql running.
        Main::getInstance()->container('events')->dispatch('db.query.sql',$sql = parent::generate($query, $map));

        $statement = parent::exec($query, $map);

        $this->prepareQueryWithError();

        return $statement;
    }

    /**
     * Create a collection of repositories from plain arrays.
     *
     * @param array $items
     *
     * @return \Framework\Bindings\DB\Repository\Collection
     */
    public function hydrate(array $items)
    {
        return $this->repository->newCollection(array_map(function ($item) {
            return $this->newRepositoryInstance($item)->with($this->repository->getWiths());
        }, $items));
    }

    /**
     * Get the repository instance being queried.
     *
     * @return \Framework\Bindings\DB\Repository\Base
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Set a repository instance for the repository being queried.
     *
     * @param \Framework\Bindings\DB\Repository\Base $repository
     *
     * @return $this
     */
    public function setRepository(Base $repository)
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Set the simple to given state
     *
     * @param bool $state
     *
     * @return Builder
     */
    public function simpling($state = true)
    {
        $this->simple = $state;

        return $this;
    }

    /**
     * Create a new instance of the repository being queried.
     *
     * @param array $attributes
     *
     * @return \Framework\Bindings\DB\Repository\Base|static
     */
    public function newRepositoryInstance($attributes = [])
    {
        return $this->repository::unguarded(function() use($attributes) {

            return $this->repository->newInstance($attributes, true)->setConnection(
                $this->repository->connection()
            );
        });
    }
}