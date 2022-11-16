<?php

namespace Nicy\Framework\Bindings\Validation;

use Nicy\Framework\Main;
use Nicy\Support\Arr;
use Rakit\Validation\Validator;
use Nicy\Framework\Exceptions\ValidationException;

class Factory
{
    /**
     * @return Validator
     */
    protected function getValidationFactory()
    {
        return new Validator();
    }

    /**
     * Given $inputs, $rules and $messages to make the Validation class instance
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return \Rakit\Validation\Validation
     */
    public function make($inputs, $rules, $messages=[])
    {
        return $this->getValidationFactory()->make($inputs, $rules, $messages);
    }

    /**
     * Given $inputs, $rules and $messages to validate the inputs
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return bool
     * @throws ValidationException
     */
    public function validate($inputs, $rules, $messages=[])
    {
        $validation = $this->make($inputs, $rules, $messages);

        $validation->validate();

        if ($validation->fails()) {

            // Dispatch a validated failed event
            Main::instance()->container('events')->dispatch('validate.fail', $validation->errors);

            throw new ValidationException(
                $validation,
                Arr::first($validation->errors->firstOfAll(':message', true)),
                $validation->errors
            );
        }

        return true;
    }
}