<?php

namespace Nicy\Framework\Bindings\Bus;

use Closure;
use ReflectionClass;
use ReflectionProperty;
use Nicy\Framework\Bindings\Validation\Factory;

class ValidatingMiddleware
{
    /**
     * The validation factory instance.
     *
     * @var \Nicy\Framework\Bindings\Validation\Factory
     */
    protected $factory;

    /**
     * Create a new validating middleware instance.
     *
     * @param \Nicy\Framework\Bindings\Validation\Factory $factory
     * @return void
     */
    public function __construct(Factory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * Validate the command before execution.
     *
     * @param object $command
     * @param Closure $next
     * @return void
     * @throws \ReflectionException
     * @throws \Nicy\Framework\Exceptions\ValidationException
     */
    public function handle($command, Closure $next)
    {
        if (property_exists($command, 'rules') && is_array($command->rules)) {
            $this->validate($command);
        }

        return $next($command);
    }

    /**
     * Validate the command.
     *
     * @param object $command
     * @return void
     * @throws \ReflectionException
     * @throws \Nicy\Framework\Exceptions\ValidationException
     */
    protected function validate($command)
    {
        if (method_exists($command, 'validate')) {
            $command->validate();
        }

        $messages = property_exists($command, 'validationMessages') ? $command->validationMessages : [];

        $this->factory->validate($this->getData($command), $command->rules, $messages);
    }

    /**
     * Get the data to be validated.
     *
     * @param object $command
     * @return array
     * @throws \ReflectionException
     */
    protected function getData($command)
    {
        $data = [];
        foreach ((new ReflectionClass($command))->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $value = $property->getValue($command);

            if (in_array($name, ['rules', 'validationMessages'], true)) {
                continue;
            }

            $data[$name] = $value;
        }

        return $data;
    }
}
