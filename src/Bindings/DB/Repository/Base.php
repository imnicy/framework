<?php

namespace Nicy\Framework\Bindings\DB\Repository;

use ArrayAccess;
use Nicy\Framework\Main;
use Nicy\Support\Str;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Support\Contracts\Jsonable;
use Nicy\Framework\Bindings\DB\Query\Builder;
use Nicy\Framework\Bindings\DB\Repository\Concerns\{HasAttributes, GuardsAttributes, HidesAttributes, HasEvents, ForPaginate};

class Base implements RepositoryInterface, Jsonable, Arrayable, ArrayAccess
{
    use ForwardsCalls, HasAttributes, GuardsAttributes, HidesAttributes, HasEvents, ForPaginate;

    /**
     * @var bool
     */
    public $exists = false;

    /**
     * Connection name
     *
     * @var string
     */
    protected $connection;

    /**
     * Table name
     *
     * @var string
     */
    protected $table;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * @var string
     */
    protected $primary = 'id';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $primaryType = 'int';

    /**
     * The array of booted models.
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * The array of trait initializers that will be called on each new instance.
     *
     * @var array
     */
    protected static $traitInitializers = [];

    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();

        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Check if the model needs to be booted and if so, do it.
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        if (! isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            static::boot();
        }
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        static::setEventDispatcher(Main::getInstance()->container('events'));
    }

    /**
     * @param array $conditions
     * @param string $columns
     *
     * @return Collection|mixed
     */
    public function all(array $conditions = [], $columns = '*')
    {
        return $this->newQueryWith()->select($this->table, $columns, $conditions);
    }

    /**
     * @param array $conditions
     * @param string $columns
     *
     * @return Collection|mixed
     */
    public function one(array $conditions = [], $columns = '*')
    {
        return $this->newQueryWith()->get($this->table, $columns, $conditions);
    }

    /**
     * @param array $attributes
     *
     * @return RepositoryInterface
     */
    public function create(array $attributes = []): RepositoryInterface
    {
        $this->fill($attributes)->save();

        $this->exists = true;

        return $this;
    }

    /**
     * @param array $attributes
     * @param array $conditions
     *
     * @return bool
     */
    public function update($attributes = [], $conditions = []): bool
    {
        if (! $this->exists) {
            return false;
        }

        static::query()->update($this->table, $attributes, $conditions);

        return true;
    }

    /**
     * @param array $conditions
     *
     * @return bool
     */
    public function delete(): bool
    {
        if (! $this->exists) {
            return false;
        }

        if ($this->dispatchRepositoryEvent('deleting') === false) {
            return false;
        }

        static::query()->delete($this->table, [$this->primary => $this->getPrimary()]);

        $this->exists = false;

        $this->dispatchRepositoryEvent('deleted');

        return true;
    }

    /**
     * @param array $ids
     *
     * @return bool
     */
    public function destroy(array $ids = []): bool
    {
        static::query()->delete($this->table, [$this->primary.'[!]' => $ids]);

        return true;
    }

    /**
     * @param array $options
     *
     * @return bool
     */
    public function save(array $options = []): bool
    {
        $query = $this->newQuery();

        if ($this->dispatchRepositoryEvent('saving') === false) {
            return false;
        }

        if ($this->exists) {
            $saved = $this->isDirty() ?
                $this->performUpdate($query) : true;
        }

        else {
            $saved = $this->performInsert($query);
        }

        if ($saved) {
            $this->dispatchRepositoryEvent('saved');

            $this->syncOriginal();
        }

        return $saved;
    }

    /**
     * Perform a model insert operation.
     *
     * @param \Nicy\Framework\Bindings\DB\Query\Builder $query
     *
     * @return bool
     */
    protected function performInsert(Builder $query)
    {
        if ($this->dispatchRepositoryEvent('creating') === false) {
            return false;
        }

        $attributes = $this->getAttributes();

        if (empty($attributes)) {
            return true;
        }

        $query->insert($this->table, $attributes);

        if ($this->getIncrementing()) {
            $this->setAttribute($this->primary, $query->id());
        }

        $this->exists = true;

        $this->dispatchRepositoryEvent('created');

        return true;
    }

    /**
     * Perform a model update operation.
     *
     * @param \Nicy\Framework\Bindings\DB\Query\Builder $query
     *
     * @return bool
     */
    protected function performUpdate(Builder $query)
    {
        if ($this->dispatchRepositoryEvent('updating') === false) {
            return false;
        }

        $dirty = $this->getDirty();

        if (count($dirty) > 0) {

            $query->update($this->table, $dirty, $this->getConditionForSave());

            $this->syncChanges();

            $this->dispatchRepositoryEvent('updated');
        }

        return true;
    }

    /**
     * Set the keys for a save update query.
     *
     * @return array
     */
    protected function getConditionForSave()
    {
        return [$this->primary, $this->getKeyForSaveQuery()];
    }

    /**
     * Get the primary key value for a save query.
     *
     * @return mixed
     */
    protected function getKeyForSaveQuery()
    {
        return $this->original[$this->primary]
            ?? $this->getPrimary();
    }

    /**
     * @param array $attributes
     *
     * @return RepositoryInterface
     */
    public function fill(array $attributes = []): RepositoryInterface
    {
        $totallyGuarded = $this->totallyGuarded();

        foreach ($this->fillableFromArray($attributes) as $key => $value) {

            $key = $this->removeTableFromKey($key);

            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded) {
                throw new \RuntimeException(sprintf(
                    'Add [%s] to fillable property to allow mass assignment on [%s].',
                    $key, get_class($this)
                ));
            }
        }

        return $this;
    }

    /**
     * Remove the table name from a given key.
     *
     * @param string $key
     *
     * @return string
     */
    protected function removeTableFromKey($key)
    {
        return Str::contains($key, '.') ? last(explode('.', $key)) : $key;
    }

    /**
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQuery()
    {
        $query = Main::getInstance()->container('db')->connection($this->connection)->simpling(false);

        return $query->setRepository($this);
    }

    /**
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public function newQueryWith()
    {
        return static::query($this->with);
    }

    /**
     * @return string
     */
    public function table()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * @param array|null $with
     *
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    public static function query(array $with = [])
    {
        return (new static)->with($with)->newQuery();
    }

    /**
     * @return \Nicy\Framework\Bindings\DB\Repository\Base
     */
    public static function instance()
    {
        return new static;
    }

    /**
     * Create a new instance of the given repository.
     *
     * @param array $attributes
     * @param bool $exists
     *
     * @return static
     */
    public function newInstance($attributes = [], $exists = false)
    {
        $instance = new static((array) $attributes);

        $instance->exists = $exists;

        $instance->setConnection(
            $this->connection()
        );

        $instance->setTable($this->table());

        return $instance;
    }

    /**
     * @return mixed|void
     */
    public function getPrimary()
    {
        if (! $this->exists) {
            return;
        }

        return $this->getAttribute($this->primary);
    }

    /**
     * Determine if two repositories have the same ID and belong to the same table.
     *
     * @param \Nicy\Framework\Bindings\DB\Repository\Base|null $repository
     *
     * @return bool
     */
    public function is($repository)
    {
        return ! is_null($repository) &&
            $this->getPrimary() === $repository->getPrimary() &&
            $this->table() === $repository->table() &&
            $this->connection() === $repository->connection();
    }

    /**
     * Set the connection associated with the repository.
     *
     * @param string|null $name
     *
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Set the table associated with the repository.
     *
     * @param string $table
     *
     * @return $this
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param array|mixed $data
     *
     * @return \Nicy\Framework\Bindings\DB\Repository\Collection
     */
    public function newCollection($data)
    {
        return is_array($data) ? new Collection($data) : $data;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Determine if the given attribute exists.
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return ! is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset], $this->relations[$offset]);
    }

    /**
     * Handle dynamic method calls into the repository.
     *
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->forwardCallTo($this->newQuery(), $method, $parameters);
    }

    /**
     * @return string|void
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the repository instance to JSON.
     *
     * @param int $options
     *
     * @return string
     * @throws \RuntimeException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->toArray(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(
                'Error encoding repository ['.get_class($this).'] to JSON: '.json_last_error_msg()
            );
        }

        return $json;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }
}