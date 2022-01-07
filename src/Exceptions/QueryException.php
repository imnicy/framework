<?php

namespace Nicy\Framework\Exceptions;

use PDOException;
use Nicy\Support\Str;

class QueryException extends PDOException
{
    /**
     * The SQL for the query.
     *
     * @var string
     */
    protected $sql;

    /**
     * The bindings for the query.
     *
     * @var array
     */
    protected $bindings;

    /**
     * Create a new query exception instance.
     *
     * @param string $sql
     * @param array $bindings
     * @return void
     */
    public function __construct($sql, array $bindings=[])
    {
        parent::__construct('', 0);

        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->code = 0;
        $this->message = $this->formatMessage($sql, $bindings);
    }

    /**
     * Format the SQL error message.
     *
     * @param string $sql
     * @param array $bindings
     * @return string
     */
    protected function formatMessage($sql, $bindings)
    {
        return ' SQL: '.Str::replaceArray('?', $bindings, $sql);
    }

    /**
     * Get the SQL for the query.
     *
     * @return string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * Get the bindings for the query.
     *
     * @return array
     */
    public function getBindings()
    {
        return $this->bindings;
    }
}
