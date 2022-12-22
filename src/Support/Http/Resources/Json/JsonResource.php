<?php

namespace Nicy\Framework\Support\Http\Resources\Json;

use ArrayAccess;
use JsonSerializable;
use Nicy\Framework\Exceptions\JsonEncodingException;
use Nicy\Framework\Support\Contracts\Http\Responsable;
use Nicy\Framework\Support\Http\Resources\HasOptions;
use Nicy\Support\Contracts\Arrayable;
use Nicy\Framework\Support\Http\Resources\ConditionallyLoadsAttributes;
use Nicy\Framework\Support\Http\Resources\DelegatesToResource;

class JsonResource implements ArrayAccess, JsonSerializable, Responsable
{
    use ConditionallyLoadsAttributes, DelegatesToResource, HasOptions;

    /**
     * The resource instance.
     *
     * @var mixed
     */
    public $resource;

    /**
     * The additional data that should be added to the top-level resource array.
     *
     * @var array
     */
    public $with = [];

    /**
     * The additional metadata that should be added to the resource response.
     *
     * Added during response construction by the developer.
     *
     * @var array
     */
    public $additional = [];

    /**
     * The "data" wrapper that should be applied.
     *
     * @var string|null
     */
    public static $wrap = 'data';

    /**
     * @var array
     */
    public $options;

    /**
     * Create a new resource instance.
     *
     * @param mixed $resource
     * @param array $options
     * @return void
     */
    public function __construct($resource, array $options=[])
    {
        $this->resource = $resource;

        $this->options = $this->ensureOptions($options);
    }

    /**
     * Create a new resource instance.
     *
     * @param mixed ...$parameters
     * @return static
     */
    public static function make(...$parameters)
    {
        return new static(...$parameters);
    }

    /**
     * Create a new anonymous resource collection.
     *
     * @param mixed $resource
     * @param array $options
     * @return \Nicy\Framework\Support\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource, array $options=[])
    {
        return tap(new AnonymousResourceCollection($resource, static::class, $options), function ($collection) {
            if (property_exists(static::class, 'preserveKeys')) {
                $collection->preserveKeys = (new static([]))->preserveKeys === true;
            }
        });
    }

    /**
     * Resolve the resource to an array.
     *
     * @return array
     */
    public function resolve()
    {
        $data = $this->toArray();

        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        } elseif ($data instanceof JsonSerializable) {
            $data = $data->jsonSerialize();
        }

        return $this->filter((array) $data);
    }

    /**
     * Transform the resource into an array.
     *
     * @return array|\Nicy\Support\Contracts\Arrayable|\JsonSerializable
     */
    public function toArray()
    {
        if (is_null($this->resource)) {
            return [];
        }

        return is_array($this->resource)
            ? $this->resource
            : $this->resource->toArray();
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param int $options
     * @return string
     *
     * @throws \Nicy\Framework\Exceptions\JsonEncodingException
     */
    public function toJson($options = 0)
    {
        $json = json_encode($this->jsonSerialize(), $options);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw JsonEncodingException::forResource($this, json_last_error_msg());
        }

        return $json;
    }

    /**
     * Get any additional data that should be returned with the resource array.
     *
     * @return array
     */
    public function with()
    {
        return $this->with;
    }

    /**
     * Add additional metadata to the resource response.
     *
     * @param array $data
     * @return $this
     */
    public function additional(array $data)
    {
        $this->additional = $data;

        return $this;
    }

    /**
     * Customize the response
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return void
     */
    public function withResponse($response)
    {
        //
    }

    /**
     * Get the JSON serialization options that should be applied to the resource response.
     *
     * @return int
     */
    public function jsonOptions()
    {
        return 0;
    }

    /**
     * Set the string that should wrap the outermost resource array.
     *
     * @param string $value
     * @return void
     */
    public static function wrap($value)
    {
        static::$wrap = $value;
    }

    /**
     * Disable wrapping of the outermost resource array.
     *
     * @return void
     */
    public static function withoutWrapping()
    {
        static::$wrap = null;
    }

    /**
     * Transform the resource into an HTTP response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function response()
    {
        return $this->toResponse();
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toResponse()
    {
        return (new ResourceResponse($this))->toResponse();
    }

    /**
     * @return bool
     */
    public function shouldBeJson(): bool
    {
        return true;
    }

    /**
     * Prepare the resource for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->resolve();
    }
}
