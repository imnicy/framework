<?php

namespace Nicy\Framework\Bindings\JWT\Payload;

use Nicy\Support\Collection as BaseCollection;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Claim;
use Nicy\Support\Str;

class Collection extends BaseCollection
{
    /**
     * Create a new collection.
     *
     * @param mixed $items
     * @return void
     */
    public function __construct($items=[])
    {
        parent::__construct($this->getArrayableItems($items));
    }

    /**
     * Get a Claim instance by it's unique name.
     *
     * @param string $name
     * @param callable|null $callback
     * @param mixed $default
     * @return Claim
     */
    public function getByClaimName($name, callable $callback=null, $default=null)
    {
        return $this->filter(function (Claim $claim) use ($name) {
            return $claim->getName() === $name;
        })->first($callback, $default);
    }

    /**
     * Validation each claim under a given context.
     *
     * @param string $context
     * @return $this
     */
    public function validate(string $context='payload')
    {
        $args = func_get_args();

        array_shift($args);

        $this->each(function ($claim) use ($context, $args) {
            call_user_func_array(
                [$claim, 'validate'.Str::ucfirst($context)],
                $args
            );
        });

        return $this;
    }

    /**
     * Determine if the Collection contains all of the given keys.
     *
     * @param mixed $claims
     * @return bool
     */
    public function hasAllClaims($claims)
    {
        return count($claims) && (new static($claims))->diff($this->keys())->isEmpty();
    }

    /**
     * Get the claims as key/val array.
     *
     * @return array
     */
    public function toPlainArray()
    {
        return $this->map(function (Claim $claim) {
            return $claim->getValue();
        })->toArray();
    }

    /**
     * {@inheritdoc}
     */
    protected function getArrayableItems($items)
    {
        return $this->sanitizeClaims($items);
    }

    /**
     * Ensure that the given claims array is keyed by the claim name.
     *
     * @param mixed $items
     * @return array
     */
    private function sanitizeClaims($items)
    {
        $claims = [];
        foreach ($items as $key => $value) {
            if (! is_string($key) && $value instanceof Claim) {
                $key = $value->getName();
            }

            $claims[$key] = $value;
        }

        return $claims;
    }
}
