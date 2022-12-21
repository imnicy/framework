<?php

namespace Nicy\Framework\Facades;

use Nicy\Framework\Bindings\JWT\Contracts\Subject;
use Nicy\Framework\Support\Facade;
use Psr\Http\Message\RequestInterface;

/**
 * Class JWT
 * @package Package\JWT\Facades
 *
 * @method static \Nicy\Framework\Bindings\JWT\Token subject(Subject $subject)
 * @method static \Nicy\Framework\Bindings\JWT\JWT setRequest(RequestInterface $request)
 * @method static \Nicy\Framework\Bindings\JWT\Payload payload()
 */
class JWT extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jwt';
    }
}
