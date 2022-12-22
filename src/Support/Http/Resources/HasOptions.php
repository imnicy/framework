<?php

namespace Nicy\Framework\Support\Http\Resources;

trait HasOptions
{
    /**
     * Get a resource collection option value
     *
     * @param string $key
     * @return mixed
     */
    protected function getOption(string $key)
    {
        return $this->options[$key] ?? null;
    }

    /**
     * @param array $options
     * @return array
     */
    protected function ensureOptions(array $options)
    {
        return array_filter($options, function($value) {
            return is_numeric($value) || is_string($value) || is_bool($value) || is_null($value);
        });
    }

    /**
     * Set a resource collection option item
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function setOption(string $key, $value=null)
    {
        $this->options[$key] = $value;
    }
}