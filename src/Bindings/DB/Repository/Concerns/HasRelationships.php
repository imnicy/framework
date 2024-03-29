<?php

namespace Nicy\Framework\Bindings\DB\Repository\Concerns;

use InvalidArgumentException;
use Nicy\Framework\Bindings\DB\Repository\Base;
use Nicy\Framework\Bindings\DB\Repository\Collection;
use Nicy\Framework\Bindings\DB\Repository\Relationship;
use Nicy\Framework\Bindings\DB\Repository\RepositoryInterface;
use Nicy\Framework\Exceptions\AttributeError;

trait HasRelationships
{
    /**
     * Relationship for this repository
     *
     * @var array|Relationship[]
     */
    protected $relations = [];

    /**
     * @var array
     */
    protected $load = [];

    /**
     * Load any has defined relationships
     *
     * @param array|string $relations
     * @return RepositoryInterface|Base|$this
     */
    public function load($relations) :RepositoryInterface
    {
        if (! is_array($relations)) {
            if (! is_string($relations)) {
                return $this;
            }

            $relations = [$relations];
        }

        $this->load = array_merge($this->load, $relations);

        foreach ($relations as $relation => $conditions) {
            if (is_int($relation)) {
                $relation = $conditions;
                $conditions = [];
            }

            if (! is_string($relation)) {
                continue;
            }

            if (str_contains($relation, '.')) {
                $this->hasEagerRelationship($relation, $conditions);
            }

            if (method_exists($this, $relation)) {
                $this->hasRelationship($relation, $conditions);
            }
        }

        return $this;
    }

    /**
     * @param string $relation
     * @param array $conditions
     */
    protected function hasEagerRelationship(string $relation, array $conditions)
    {
        $finder = explode('.', $relation);

        $related = $this->getRelatedRepository(array_slice($finder, 0, -1));

        if ($related && $related !== $this) {
            $related->load([end($finder) => $conditions]);
        }
    }

    /**
     * @param string $relation
     * @param array $conditions
     */
    protected function hasRelationship(string $relation, array $conditions)
    {
        $relationship = $this->{$relation}();
        if (! $relationship instanceof Relationship) {
            throw new AttributeError('invalid relationships: ' . $relation);
        }

        $relationship->setConditions($conditions)->setName($relation);

        $this->addToRelations($relationship);
    }

    /**
     * @param array|string $eagerRelations
     * @return Base
     */
    protected function getRelatedRepository($eagerRelations)
    {
        if (is_string($eagerRelations)) {
            $eagerRelations = [$eagerRelations];
        }

        $related = $this;

        foreach ($eagerRelations as $item) {
            if (! array_key_exists($item, $related->relations)) {
                return null;
            }
            $related = $related->relations[$item]->getRelation();
        }
        return $related;
    }

    /**
     * @param string|array|Base $relation
     * @param string $key
     * @param string|null $foreignKey
     * @return Relationship
     */
    protected function loadMany($relation, string $key, string $foreignKey=null)
    {
        return $this->loadRelation('many', null, $relation, $key, $foreignKey);
    }

    /**
     * @param string|array|Base $relation
     * @param string $key
     * @param string|null $foreignKey
     * @return Relationship
     */
    protected function loadOne($relation, string $key, string $foreignKey=null)
    {
        return $this->loadRelation('one', null, $relation, $key, $foreignKey);
    }

    /**
     * @param string|array|Base $relation
     * @param string $through
     * @param string $throughKey
     * @param string $throughForeignKey
     * @param string $key
     * @param string|null $foreignKey
     * @return Relationship
     */
    protected function loadManyThrough(
        $relation, string $through, string $throughKey, string $throughForeignKey, string $key, string $foreignKey=null
    )
    {
        return $this->loadRelation(
            'manyThrough', null, $relation, $through, $throughKey, $throughForeignKey, $key, $foreignKey
        );
    }

    /**
     * @param string $type
     * @param string|null $name
     * @param string|array|Base $relation
     * @param array $args
     * @return Relationship
     */
    protected function loadRelation(string $type, ?string $name, $relation, ...$args)
    {
        list($relation, $conditions) = $this->parseRelationFormat($relation);

        $relationship = new Relationship($this, $relation, $type, $args);

        $relationship->setConditions($conditions)->setName($name);

        return $relationship;
    }

    /**
     * @param Relationship $relationship
     * @return $this
     */
    protected function addToRelations(Relationship $relationship)
    {
        $name = $relationship->getName();

        if ($name) {
            $this->relations[$name] = $relationship;
        }

        return $this;
    }

    /**
     * @param array $relations
     * @return $this
     */
    public function setRelations(array $relations=[])
    {
        $this->relations = $relations;

        return $this;
    }

    /**
     * @return array|Relationship[]
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * @param string|array|object $relation
     * @return array
     */
    protected function parseRelationFormat($relation)
    {
        if (is_array($relation) && count($relation) == 2) {
            list($relation, $conditions) = $relation;
        }
        else {
            $conditions = [];
        }

        $conditions = is_array($conditions) ? $conditions : [];

        if (is_string($relation)) {
            if (! class_exists($relation)) {
                throw new InvalidArgumentException(sprintf('invalid relationship class [%s]', $relation));
            }
            return [new $relation, $conditions];
        }
        else if (is_object($relation)) {
            if (! $relation instanceof Base) {
                throw new InvalidArgumentException(
                    sprintf('invalid repository instance [%s]', is_object($relation) ? get_class($relation) : 'unknown class')
                );
            }
        }
        else {
            throw new InvalidArgumentException('invalid repository to relation');
        }

        return [$relation, $conditions];
    }

    /**
     * @param Collection|Base $results
     * @return Collection|Base
     */
    protected function hydrateRelationships($results)
    {
        if (! $this->relations) {
            return $results;
        }

        foreach ($this->relations as $name => $relationship) {
            $type = $relationship->getType();
            switch ($type) {
                case 'one':
                case 'many':
                    $loadMethod = 'loadOneOrMany';
                    break;
                case 'manyThrough':
                    $loadMethod = 'loadManyThrough';
                    break;
                default:
                    return $results;
            }

            $results = $relationship->{$loadMethod}($results);
        }
        return $results;
    }

    /**
     * @return array
     */
    public function getLoads()
    {
        return $this->load;
    }
}