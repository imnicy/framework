<?php

namespace Nicy\Framework\Support\Http\Resources;

use Nicy\Support\Collection;
use JsonSerializable;

class MergeValue
{
    /**
     * The data to be merged.
     *
     * @var array
     */
    public $data;

    /**
     * Create a new merge value instance.
     *
     * @param \Nicy\Support\Collection|\JsonSerializable|array $data
     * @return void
     */
    public function __construct($data)
    {
        if ($data instanceof Collection) {
            $this->data = $data->all();
        } elseif ($data instanceof JsonSerializable) {
            $this->data = $data->jsonSerialize();
        } else {
            $this->data = $data;
        }
    }
}
