<?php

namespace Nicy\Framework\Bindings\JWT;

use Nicy\Framework\Bindings\JWT\Payload\Factory as ClaimFactory;
use Nicy\Framework\Bindings\JWT\Parser\AuthHeaders;
use Nicy\Framework\Bindings\JWT\Parser\Parser;
use Nicy\Framework\Bindings\JWT\Providers\Lcobucci;
use Nicy\Framework\Bindings\JWT\Validators\PayloadValidator;
use Nicy\Framework\Container;
use Nicy\Framework\Support\ServiceProvider;

class JWTServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerJWTProvider();
        $this->registerManager();
        $this->registerTokenParser();
        $this->registerJWT();
        $this->registerPayloadValidator();
        $this->registerClaimFactory();
        $this->registerPayloadFactory();
    }

    /**
     * Register the bindings for the JSON Web Token provider.
     *
     * @return void
     */
    protected function registerJWTProvider()
    {
        $this->registerLcobucciProvider();

        $this->container->singleton('jwt.provider', function () {
            return $this->container->get('providers.jwt');
        });
    }

    /**
     * Register the bindings for the Lcobucci JWT provider.
     *
     * @return void
     */
    protected function registerLcobucciProvider()
    {
        $this->container->singleton('providers.jwt', function () {
            return new Lcobucci(
                $this->config('algo'), $this->config('keys')
            );
        });
    }

    /**
     * Register the bindings for the JWT Manager.
     *
     * @return void
     */
    protected function registerManager()
    {
        $this->container->singleton('jwt.manager', function () {
            $instance = new Manager(
                $this->container['jwt.provider'],
                $this->container['jwt.payload.factory']
            );
            return $instance->setPersistentClaims($this->config('persistent_claims'));
        });
    }

    /**
     * Register the bindings for the Token Parser.
     *
     * @return void
     */
    protected function registerTokenParser()
    {
        $this->container->singleton('jwt.parser', function () {
            return new Parser(
                $this->container['request'], [new AuthHeaders()]
            );
        });
    }

    /**
     * Register the bindings for the main JWT class.
     *
     * @return void
     */
    protected function registerJWT()
    {
        $this->container->singleton('jwt', function () {
            return (new JWT(
                $this->container['jwt.manager'],
                $this->container['jwt.parser']
            ))->lockSubject($this->config('lock_subject'));
        });
    }

    /**
     * Register the bindings for the payload validator.
     *
     * @return void
     */
    protected function registerPayloadValidator()
    {
        $this->container->singleton('jwt.validators.payload', function () {
            return (new PayloadValidator)
                ->setRefreshTTL($this->config('refresh_ttl'))
                ->setRequiredClaims($this->config('required_claims'));
        });
    }

    /**
     * Register the bindings for the Claim Factory.
     *
     * @return void
     */
    protected function registerClaimFactory()
    {
        $this->container->singleton('jwt.claim.factory', function () {
            $factory = new ClaimFactory($this->container['request']);
            return $factory->setTTL($this->config('ttl'))->setLeeway($this->config('leeway'));
        });
    }

    /**
     * Register the bindings for the Payload Factory.
     *
     * @return void
     */
    protected function registerPayloadFactory()
    {
        $this->container->singleton('jwt.payload.factory', function () {
            return new Factory(
                $this->container['jwt.claim.factory'],
                $this->container['jwt.validators.payload']
            );
        });
    }

    /**
     * Helper to get the config values.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    protected function config($key, $default=null)
    {
        return config("jwt.$key", $default);
    }
}
