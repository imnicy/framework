<?php

namespace Nicy\Framework\Bindings\JWT\Providers;

use DateTimeImmutable;
use Exception;
use ReflectionClass;
use Lcobucci\JWT\Validation\ConstraintViolation;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer;
use Lcobucci\JWT\Signer\Hmac\Sha256 as HS256;
use Lcobucci\JWT\Signer\Hmac\Sha384 as HS384;
use Lcobucci\JWT\Signer\Hmac\Sha512 as HS512;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RS256;
use Lcobucci\JWT\Signer\Rsa\Sha384 as RS384;
use Lcobucci\JWT\Signer\Rsa\Sha512 as RS512;
use Lcobucci\JWT\Signer\Rsa;
use Nicy\Support\Collection;
use Nicy\Framework\Bindings\JWT\Contracts\Providers\JWT;
use Nicy\Framework\Bindings\JWT\Exceptions\JWTException;
use Nicy\Framework\Bindings\JWT\Exceptions\TokenInvalidException;

class Lcobucci extends Provider implements JWT
{
    /**
     * @var Signer
     */
    protected $signer;

    public function __construct($algo, $keys)
    {
        $this->setAlgo($algo)->setKeys($keys);
    }

    /**
     * Signers that this provider supports.
     *
     * @var array
     */
    protected $signers = [
        'HS256' => HS256::class,
        'HS384' => HS384::class,
        'HS512' => HS512::class,

        'RS256' => RS256::class,
        'RS384' => RS384::class,
        'RS512' => RS512::class,
    ];

    /**
     * Create a JSON Web Token.
     *
     * @param array $payload
     * @return string
     * @throws JWTException|\ReflectionException
     */
    public function encode($payload)
    {
        $configuration = $this->configure();

        $builder = $configuration->builder();

        try {
            $now = new DateTimeImmutable();
            foreach ($payload as $key => $value) {
                if ($key == 'iss') {
                    $builder->issuedBy($value);
                }
                else if ($key == 'iat') {
                    $builder->issuedAt($now->setTimestamp($value));
                }
                else if ($key == 'exp') {
                    $builder->expiresAt($now->setTimestamp($value));
                }
                else if ($key == 'nbf') {
                    $builder->canOnlyBeUsedAfter($now->setTimestamp($value));
                }
                else if ($key == 'jti') {
                    $builder->identifiedBy($value);
                }
                else if ($key == 'aud') {
                    $builder->permittedFor($value);
                }
                else if ($key == 'sub') {
                    $builder->relatedTo($value);
                }
                else {
                    $builder->withClaim($key, $value);
                }
            }
        } catch (Exception $e) {
            throw new JWTException('Could not create token: ' . $e->getMessage(), $e->getCode(), $e);
        }
        return $builder->getToken($configuration->signer(), $configuration->signingKey())->toString();
    }

    /**
     * Get a configuration
     *
     * @return Configuration
     * @throws JWTException|\ReflectionException
     */
    protected function configure()
    {
        $key = InMemory::base64Encoded($this->getPublicKey());

        if ($this->isAsymmetric()) {
            return Configuration::forAsymmetricSigner(
                $this->getSigner(), InMemory::base64Encoded($this->getPrivateKey()), $key
            );
        }
        else {
            return Configuration::forSymmetricSigner($this->getSigner(), $key);
        }
    }

    /**
     * Decode a JSON Web Token.
     *
     * @param string $token
     * @return array
     * @throws JWTException|\ReflectionException
     */
    public function decode($token)
    {
        $configuration = $this->configure();
        $parser = $configuration->parser();

        try {
            $jwt = $parser->parse($token);
            // Signature
            $verified = $configuration->signer()->verify(
                $jwt->signature()->hash(), $jwt->payload(), $configuration->signingKey()
            );
            if (false === $verified) {
                throw new ConstraintViolation('Token signature mismatch');
            }

        } catch (Exception $e) {
            throw new TokenInvalidException('Could not decode token: ' . $e->getMessage(), $e->getCode(), $e);
        }
        return (new Collection($jwt->claims()->all()))->map(function ($claim) {
            if ($claim instanceof DateTimeImmutable) {
                return $claim->getTimestamp();
            }
            else {
                return $claim;
            }
        })->toArray();
    }

    /**
     * Get the signer instance.
     *
     * @throws JWTException
     * @return Signer
     */
    protected function getSigner()
    {
        if (! array_key_exists($this->getAlgo(), $this->signers)) {
            throw new JWTException('The given algorithm could not be found');
        }
        return new $this->signers[$this->getAlgo()];
    }

    /**
     * @return bool
     * @throws \ReflectionException|JWTException
     */
    protected function isAsymmetric()
    {
        $reflect = new ReflectionClass($this->getSigner());

        return $reflect->isSubclassOf(Rsa::class);
    }
}
