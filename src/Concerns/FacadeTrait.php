<?php

namespace Nicy\Framework\Concerns;

use Nicy\Framework\Support\Facade;

trait FacadeTrait
{
    /**
     * Indicates if the class aliases have been registered.
     *
     * @var bool
     */
    protected static $aliasesRegistered = false;

    /**
     * Register the facades for the manager.
     *
     * @param bool $aliases
     * @param array $userAliases
     * @return void
     */
    public function withFacades(bool $aliases=true, array $userAliases=[])
    {
        Facade::setFacadeContainer($this->container);

        if ($aliases) {
            $this->withAliases($userAliases);
        }
    }

    /**
     * Register the aliases for the application.
     *
     * @param array $userAliases
     * @return void
     */
    public function withAliases(array $userAliases=[])
    {
        $defaults = [
            //
        ];

        if (! static::$aliasesRegistered) {
            static::$aliasesRegistered = true;

            $merged = array_merge($defaults, $userAliases);

            foreach ($merged as $original => $alias) {
                class_alias($original, $alias);
            }
        }
    }
}