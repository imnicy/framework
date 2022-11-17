<?php

namespace Nicy\Framework\Bindings\DB\Repository;

use RuntimeException;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Framework\Bindings\DB\Repository\Concerns\HasRelationships;

class Relationship implements Arrayable
{
    /**
     * @var Base
     */
    protected $repository;

    /**
     * @var Base
     */
    protected $relation;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $args;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var array
     */
    protected $conditions;

    /**
     * Relationship constructor.
     *
     * @param Base|HasRelationships $repository For IDE
     * @param Base $relation
     * @param string $type
     * @param array $args
     */
    public function __construct($repository, $relation, $type, $args)
    {
        $this->repository = $repository;
        $this->relation = $relation;
        $this->type = $type;
        $this->args = $args;
    }

    /**
     * @param Collection|Base $results
     * @param Collection $foreignResults
     * @param string $key
     * @param string $foreignKey
     * @return Collection|Base
     */
    protected function attachForeignResults($results, $foreignResults, $key, $foreignKey)
    {
        if ($results instanceof Collection) {
            return $results->each(function($item) use ($foreignResults, $key, $foreignKey) {
                $item->{$this->name} = $this->filterForeignResults(
                    $foreignResults, $foreignKey,  $item->{$key}
                );
            });
        }

        $results->{$this->name} = $this->filterForeignResults($foreignResults, $foreignKey, $results->{$key});

        return $results;
    }

    /**
     * @param Collection|Base $foreignResults
     * @param string $foreignKey
     * @param string|int $foreignValue
     * @return mixed
     */
    protected function filterForeignResults($foreignResults, $foreignKey, $foreignValue)
    {
        $foreignResults = $foreignResults->where($foreignKey, '=', $foreignValue);

        return $this->type == 'one' ? $foreignResults->first()->toArray() : $foreignResults->values();
    }

    /**
     * @param Collection|Base $results
     * @return Collection|Base
     */
    public function loadOneOrMany($results)
    {
        list($key, $foreignKey) = $this->args;

        $foreignResults = $this->relation->all(
            '*', array_merge($this->conditions, [
                $foreignKey => $this->getResultsValues($results, $key)
            ]
        ));

        return $this->attachForeignResults($results, $foreignResults, $key, $foreignKey);
    }

    /**
     * @param Collection|Base $results
     * @return Collection|Base
     */
    public function loadManyThrough($results)
    {
        list($through, $throughKey, $throughForeignKey, $key, $foreignKey) = $this->args;

        $throughTable = $this->newRepositoryWithoutRelationships($through)->table();

        $foreignResults = $this->relation->all(
            '*', array_merge($this->conditions, [
                $foreignKey => $this->repository->newQueryWith()->all($this->repository->table(), [
                    '[><]' . $throughTable => [
                        $key => $throughKey
                    ],
                ], [
                    $throughTable . '.' . $throughForeignKey
                ], [
                    $throughTable . '.' . $throughKey => $this->getResultsValues($results, $key)
                ])->pluck($throughForeignKey)->toArray()
            ]
        ));

        return $this->attachForeignResults($results, $foreignResults, $key, $foreignKey);
    }

    /**
     * @param Collection|Base $results
     * @param string $key
     * @return array
     */
    protected function getResultsValues($results, $key)
    {
        if ($results instanceof Collection) {
            return $results->pluck($key)->toArray();
        }

        return [$results->{$key}];
    }

    /**
     * Builder new repository object without relationship and extends attributes
     *
     * @param string $name
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
            throw new RuntimeException('invalid repository class: ' . (string) $name);
        }

        if (! $repository instanceof Base) {
            throw new RuntimeException('invalid repository object: ' . get_class($repository));
        }

        return $repository;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
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
     * @param array $conditions
     * @return $this
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;

        return $this;
    }

    /**
     * @return Base
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
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
    protected function parseArgsWithScopes($args)
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
     * @return Collection
     */
    public function toArray()
    {
        return $this->all();
    }
}