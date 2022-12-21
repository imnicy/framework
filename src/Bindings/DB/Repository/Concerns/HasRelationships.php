<?php

namespace Nicy\Framework\Bindings\DB\Repository\Concerns;

use InvalidArgumentException;
use Nicy\Framework\Bindings\DB\Repository\Base;
use Nicy\Framework\Bindings\DB\Repository\Collection;

trait HasRelationships
{
    /**
     * @var array
     */
    protected $relations = [];

    /**
     * @var array
     */
    protected $loaded = [];

    /**
     * @var array
     */
    protected $aliases = [];

    /**
     * @var Collection
     */
    protected $results;

    /**
     * @param string|array|Base $repository
     * @param string $key
     * @param string $foreignKey
     * @param string|null $name
     * @return $this
     */
    public function loadMany($repository, string $key, string $foreignKey, string $name=null)
    {
        return $this->addToRelations($name, $repository, 'many', $key, $foreignKey);
    }

    /**
     * @param string|array|Base $repository
     * @param string $key
     * @param string $foreignKey
     * @param string|null $name
     * @return $this
     */
    public function loadOne($repository, string $key, string $foreignKey, string $name=null)
    {
        return $this->addToRelations($name, $repository, 'one', $key, $foreignKey);
    }

    /**
     * @param string|array|Base $repository
     * @param string $through
     * @param string $throughKey
     * @param string $throughForeignKey
     * @param string $key
     * @param string $foreignKey
     * @param string|null $name
     * @return $this
     */
    public function loadManyThrough(
        $repository, string $through, string $throughKey, string $throughForeignKey, string $key, string $foreignKey, string $name=null
    )
    {
        return $this->addToRelations(
            $name, $repository, 'manyThrough', $through, $throughKey, $throughForeignKey, $key, $foreignKey
        );
    }

    /**
     * @param string|null $name
     * @param string|array|object $repository
     * @param string $type
     * @param array $args
     * @return $this
     */
    protected function addToRelations(?string $name, $repository, string $type, ...$args)
    {
        list($repository, $conditions) = $this->getLoadRepository($repository);

        $class = get_class($repository);

        $this->relations[$class] = [$type, [...$args], $conditions];
        $this->loaded[$class] = [$repository, $conditions];

        if ($name) {
            $this->aliases[$class] = $name;
        }

        return $this;
    }

    /**
     * @param string|array|object $repository
     * @return array
     */
    protected function getLoadRepository($repository)
    {
        if (is_array($repository) && count($repository) == 2) {
            list($repository, $conditions) = $repository;
        }
        else {
            $conditions = [];
        }
        if (is_string($repository)) {
            if (! array_key_exists($repository, $this->loaded) && ! class_exists($repository)) {
                throw new InvalidArgumentException(sprintf('invalid relationship class [%s]', $repository));
            }
            return $this->loaded[$repository] ?? [new $repository, $conditions];
        }
        else if (is_object($repository)) {
            if (! $repository instanceof Base) {
                throw new InvalidArgumentException(
                    sprintf('invalid repository instance [%s]', is_object($repository) ? get_class($repository) : 'unknown class')
                );
            }
        }
        else {
            throw new InvalidArgumentException('invalid repository to relation');
        }

        return [$repository, $conditions];
    }

    /**
     * @param Collection $results
     * @return Collection
     */
    protected function loadingRelationships(Collection $results)
    {
        if (! $this->relations) {
            return $results;
        }

        $this->setResults($results);

        foreach ($this->relations as $name => $relation) {
            list($type, $args, ) = $relation;
            switch ($type) {
                case 'one':
                case 'many':
                    $loadMethod = 'loadOneOrManyFromConditions';
                    break;
                case 'manyThrough':
                    $loadMethod = 'loadManyThroughFromConditions';
                    break;
                default:
                    return $results;
            }

            // Attach one condition to loading method
            $args[] = $type == 'one';

            list($repository, $conditions) = $this->getLoadRepository($name);

            $results = tap($this->{$loadMethod}($repository, $conditions, ...$args), function($results) {
                return $this->setResults($results);
            });
        }
        return $results;
    }

    /**
     * @param Collection $results
     * @return $this
     */
    protected function setResults(Collection $results)
    {
        $this->results = $results;

        return $this;
    }

    /**
     * @return Collection
     */
    protected function getResults()
    {
        return $this->results;
    }

    /**
     * @param Base $repository
     * @param array $conditions
     * @param string $key
     * @param string $foreignKey
     * @param bool $one
     * @return Collection
     */
    private function loadOneOrManyFromConditions(
        Base $repository, array $conditions, string $key, string $foreignKey, bool $one=false
    )
    {
        list($results, $foreignResults) = $this->getOneOrManyResults(
            $repository, $conditions, $key, $foreignKey
        );

        return $this->attachForeignResults(
            get_class($repository), $results, $foreignResults, $key, $foreignKey, $one
        );
    }

    /**
     * @param Base $repository
     * @param array $conditions
     * @param string $through
     * @param string $throughKey
     * @param string $throughForeignKey
     * @param string $key
     * @param string $foreignKey
     * @return Collection
     */
    private function loadManyThroughFromConditions(
        Base $repository, array $conditions, string $through, string $throughKey, string $throughForeignKey, string $key, string $foreignKey
    )
    {
        $results = $this->getResults();

        [$throughRepository, ] = $this->getLoadRepository($through);

        $throughTable = $throughRepository->table();

        $foreignResults = $repository->all('*', array_merge($conditions, [
            $foreignKey => $this->newQuery()->select($this->table(), [
                '[><]' . $throughTable => [
                    $key => $throughKey
                ],
            ], [
                $throughTable . '.' . $throughForeignKey
            ], [
                $throughTable . '.' . $throughKey => $results->pluck($key)->toArray()
            ])->pluck($throughForeignKey)->toArray()
        ]));

        return $this->attachForeignResults(
            get_class($repository), $results, $foreignResults, $key, $foreignKey
        );
    }

    /**
     * @param string $class
     * @param Collection $results
     * @param Collection $foreignResults
     * @param string $key
     * @param string $foreignKey
     * @param bool $one
     * @return Collection
     */
    protected function attachForeignResults(
        string $class, Collection $results, Collection $foreignResults, string $key, string $foreignKey, bool $one=false
    )
    {
        return $results->each(function($item) use (
            $class, $foreignResults, $key, $foreignKey, $one
        ) {
            $foreignResults = $foreignResults->where($foreignKey, '=', $item->{$key});

            $item->{$this->getLoadedKey($key, $class)} = $one
                ? $foreignResults->first()->toArray()
                : $foreignResults->values();
        });
    }

    /**
     * @param string $key
     * @param string $class
     * @return string
     */
    protected function getLoadedKey(string $key, string $class)
    {
        return $this->aliases[$class] ?? $this->getDefaultLoadedKey($key);
    }

    /**
     * @param Base $repository
     * @param array $conditions
     * @param string $key
     * @param string $foreignKey
     * @return array
     */
    protected function getOneOrManyResults(Base $repository, array $conditions, string $key, string $foreignKey)
    {
        $results = $this->getResults();

        $foreignResults = $repository->all('*', array_merge($conditions, [
            $foreignKey => $results->pluck($key)->toArray()
        ]));

        return [$results, $foreignResults];
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getDefaultLoadedKey(string $key)
    {
        return 'loaded_' . $key;
    }
}