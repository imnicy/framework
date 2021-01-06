<?php

namespace Nicy\Framework\Bindings\Filesystem\Contracts;

interface Factory
{
    /**
     * Get a filesystem implementation.
     *
     * @param string|null $name
     * @return \League\Flysystem\FilesystemOperator
     */
    public function disk($name=null);
}