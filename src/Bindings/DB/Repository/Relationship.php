<?php

namespace Nicy\Framework\Bindings\DB\Repository;

use RuntimeException;
use InvalidArgumentException;
use Nicy\Support\Str;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Framework\Bindings\DB\Repository\Concerns\HasRelationships;

class Relationship implements Arrayable
{
    /**
     * Parent repository
     *
     * @var Base
     */
    protected $repository;

    /**
     * Relation repository
     *
     * @var Base
     */
    protected $relation;

    /**
     * Relation types: many | one | manyThrough
     *
     * @var string
     */
    protected $type;

    /**
     * Relation args
     *
     * @var array
     */
    protected $args;

    /**
     * Relationship name
     *
     * @var string
     */
    protected $name;

    /**
     * Relation query conditions
     *
     * @var array
     */
    protected $conditions;

    /**
     * Indicates whether relationship name are snake cased on arrays.
     *
     * @var bool
     */
    public static $snakeNames = false;

    /**
     * Relationship constructor.
     *
     * @param Base|HasRelationships $repository For IDE
     * @param Base $relation
     * @param string $type
     * @param array $args
     */
    public function __construct($repository, $relation, string $type, array $args)
    {
        $this->repository = $repository;
        $this->relation = $relation;
        $this->type = $type;
        $this->args = $this->parseRelationArgs($args);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function parseRelationArgs(array $args)
    {
        $parsed = array_filter($args, function($arg) {
            return is_string($arg) || is_null($arg);
        });

        if (count($parsed) != count($args)) {
            throw new InvalidArgumentException(
                sprintf('has invalid relationship arguments wth relationship class [%s]', get_class($this->relation))
            );
        }

        return $parsed;
    }

    /**
     * @param Collection $relationResults
     * @param Collection $throughResults
     * @param string $throughKey
     * @param string $throughForeignKey
     * @return callable
     */
    protected function buildRelationPivotClosure(
        $relationResults, $throughResults, string $throughKey, string $throughForeignKey
    ): callable
    {
        return function($filter, string $foreignKey=null)
        use ($relationResults, $throughResults, $throughKey, $throughForeignKey)
        {
            if (is_null($foreignKey)) {
                $foreignKey = $relationResults->first()->getPrimaryKey();
            }
            $through = $throughResults->where($throughForeignKey, '=', $filter);

            return $relationResults->whereIn($foreignKey, $through->pluck($throughKey));
        };
    }

    /**
     * @param Collection|Base $results
     * @param Collection|callable $relationResults
     * @param string $key
     * @param string $foreignKey
     * @return Collection|Base
     */
    protected function attachRelationResults($results, $relationResults, string $key, string $foreignKey=null)
    {
        $name = lcfirst(static::$snakeNames ? Str::snake($this->name) : $this->name);

        if (is_null($foreignKey)) {
            $foreignKey = $this->repository->getPrimaryKey();
        }

        if ($results instanceof Collection) {
            return $results->each(function($item) use ($name, $relationResults, $key, $foreignKey) {
                if (is_callable($relationResults)) {
                    $item->{$name} = $relationResults($item->{$foreignKey});
                }
                else {
                    $item->{$name} = $this->filterRelationResults(
                        $relationResults, $key, $item->{$foreignKey}
                    );
                }
            });
        }

        $results->{$name} = $this->filterRelationResults($relationResults, $key, $results->{$foreignKey});

        return $results;
    }

    /**
     * @param Collection|Base $relationResults
     * @param string $key
     * @param string|int|null $value
     * @return mixed
     */
    protected function filterRelationResults($relationResults, string $key, string $value=null)
    {
        if (is_null($value)) {
            return null;
        }

        $relationResults = $relationResults->where($key, '=', $value);

        return $this->type == 'one' ? $relationResults->first() : $relationResults->values();
    }

    /**
     * Load relation data to results, type of 'one' or 'many' relationship
     *
     * @param Collection|Base $results
     * @return Collection|Base
     */
    public function loadOneOrMany($results)
    {
        list($key, $foreignKey) = $this->args;

        $relationResults = $this->relation->all(
            '*', array_merge($this->conditions, [
                $key => $this->getResultsValues($results, $foreignKey)
            ]
        ));

        return $this->attachRelationResults($results, $relationResults, $key, $foreignKey);
    }

    /**
     * Load relation data to results, type of 'many-though' relationship
     *
     * @param Collection|Base $results
     * @return Collection|Base
     */
    public function loadManyThrough($results)
    {
        list($through, $throughKey, $throughForeignKey, $key, $foreignKey) = $this->args;

        if (is_null($foreignKey)) {
            $foreignKey = $this->repository->getPrimaryKey();
        }

        $throughRepository = $this->newRepositoryWithoutRelationships($through);
        $throughTable = $throughRepository->table();

        $throughResults = $this->repository->newQuery()->all($this->repository->table(), [
            '[><]' . $throughTable => [
                $foreignKey => $throughForeignKey
            ],
        ], [
            $throughTable . '.' . $throughKey,
            $throughTable . '.' . $throughForeignKey
        ], [
            $throughTable . '.' . $throughForeignKey => $this->getResultsValues($results, $foreignKey)
        ]);

        $relationResults = $this->relation->all(
            '*', array_filter(array_merge($this->conditions, [
                $foreignKey => $throughResults->pluck($throughKey)->toArray()
            ]), function($val) {
                if (is_array($val) && empty($val)) {
                    return false;
                }
                return true;
            })
        );

        return $this->attachRelationResults(
            $results,
            $this->buildRelationPivotClosure($relationResults, $throughResults, $throughKey, $throughForeignKey),
            $key, $foreignKey
        );
    }

    /**
     * @param Collection|Base $results
     * @param string|null $foreignKey
     * @return array
     */
    protected function getResultsValues($results, string $foreignKey=null)
    {
        if (is_null($foreignKey)) {
            $foreignKey = $this->repository->getPrimaryKey();
        }

        if ($results instanceof Collection) {
            return $results->pluck($foreignKey)->toArray();
        }

        return [$results->{$foreignKey}];
    }

    /**
     * Builder new repository object without relationship and extends attributes
     *
     * @param mixed $name
     * @return Base
     */
    protected function newRepositoryWithoutRelationships($name)
    {
        if (is_string($name) && class_exists($name)) {
            $repository = new $name;
        }
        else if (is_object($name)) {
            $repository = $name;
        }
        else {
            throw new RuntimeException('invalid repository class: ' . $name);
        }

        if (! $repository instanceof Base) {
            throw new RuntimeException('invalid repository object: ' . get_class($repository));
        }

        return $repository;
    }

    /**
     * Set relationship nam
     *
     * @param string|null $name
     * @return $this
     */
    public function setName(?string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set relation query conditions
     *
     * @param array $conditions
     * @return $this
     */
    public function setConditions(array $conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * Get parent repository
     *
     * @return Base
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Get relation repository
     *
     * @return Base
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Call repository fetch method with global relationship scopes
     *
     * @param array $args
     * @return Collection
     */
    public function all(...$args)
    {
        list($columns, $conditions) = $this->parseArgsWithScopes($args);

        return $this->repository->all($columns, $conditions);
    }

    /**
     * Call repository fetch method with global relationship scopes
     *
     * @param array $args
     * @return Base
     */
    public function one(...$args)
    {
        list($columns, $conditions) = $this->parseArgsWithScopes($args);

        return $this->relation->one($columns, $conditions);
    }

    /**
     * @param array $args
     * @return array
     */
    protected function parseArgsWithScopes(array $args)
    {
        if (count($args) > 2) {
            throw new RuntimeException('relationship repository cant join tables');
        }

        if (! isset($args[1])) {
            $columns = $args[0] ?? '*';
            $conditions = [];
        }
        else {
            $columns = $args[0];
            $conditions = $args[1];
        }

        $conditions = array_merge($conditions, [
            $this->getForeignKeyFromArgs() => $this->repository->{$this->getKeyFromArgs()},
        ]);

        return [$columns, $conditions];
    }

    /**
     * @return string
     */
    protected function getForeignKeyFromArgs()
    {
        return $this->type == 'manyThrough' ? $this->args[3] : $this->args[1];
    }

    /**
     * @return string
     */
    protected function getKeyFromArgs()
    {
        return $this->type == 'manyThrough' ? $this->args[2] : $this->args[0];
    }

    /**
     * Relation query result to array
     *
     * @return Collection
     */
    public function toArray()
    {
        return $this->all();
    }
}