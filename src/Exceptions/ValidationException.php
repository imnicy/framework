<?php

namespace Nicy\Framework\Exceptions;

use Exception;

class ValidationException extends Exception
{
    /**
     * The validator instance.
     *
     * @var \Rakit\Validation\Validation
     */
    public $validation;

    /**
     * The status code to use for the response.
     *
     * @var int
     */
    public $status = 422;

    /**
     * The name of the error bag.
     *
     * @var string
     */
    public $errorBag;

    /**
     * Create a new exception instance.
     *
     * @param \Rakit\Validation\Validation  $validation
     * @param string $content
     * @param mixed $errorBag
     * @return void
     */
    public function __construct($validation, string $content=null, $errorBag=null)
    {
        parent::__construct($content);

        $this->errorBag = $errorBag;
        $this->validation = $validation;
    }

    /**
     * Get all the validation error messages.
     *
     * @return array
     */
    public function errors()
    {
        return $this->validation->errors()->all();
    }

    /**
     * Set the HTTP status code to be used for the response.
     *
     * @param int $status
     * @return $this
     */
    public function status(int $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set the error bag on the exception.
     *
     * @param mixed $errorBag
     * @return $this
     */
    public function errorBag($errorBag)
    {
        $this->errorBag = $errorBag;

        return $this;
    }
}
