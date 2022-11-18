<?php

namespace Nicy\Framework\Bindings\JWT\Payload;

use Nicy\Support\Str;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Claim;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Custom;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Expiration;
use Nicy\Framework\Bindings\JWT\Payload\Claims\IssuedAt;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Issuer;
use Nicy\Framework\Bindings\JWT\Payload\Claims\JwtId;
use Nicy\Framework\Bindings\JWT\Payload\Claims\NotBefore;
use Nicy\Framework\Bindings\JWT\Payload\Claims\Subject;
use Nicy\Framework\Bindings\JWT\Support\Utils;
use Psr\Http\Message\RequestInterface;

class Factory
{
    /**
     * The request.
     *
     * @var RequestInterface
     */
    protected $request;

    /**
     * The TTL.
     *
     * @var int
     */
    protected $ttl = 60;

    /**
     * Time leeway in seconds.
     *
     * @var int
     */
    protected $leeway = 0;

    /**
     * The classes map.
     *
     * @var array
     */
    private $classMap = [
        'exp' => Expiration::class,
        'iat' => IssuedAt::class,
        'iss' => Issuer::class,
        'jti' => JwtId::class,
        'nbf' => NotBefore::class,
        'sub' => Subject::class
    ];

    /**
     * Constructor.
     *
     * @param RequestInterface $request
     * @return void
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get the instance of the claim when passing the name and value.
     *
     * @param string $name
     * @param mixed $value
     * @return Claim
     */
    public function get(string $name, $value)
    {
        if ($this->has($name)) {
            $claim = new $this->classMap[$name]($value);

            return method_exists($claim, 'setLeeway') ?
                $claim->setLeeway($this->leeway) :
                $claim;
        }

        return new Custom($name, $value);
    }

    /**
     * Check whether the claim exists.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->classMap);
    }

    /**
     * Generate the initial value and return the Claim instance.
     *
     * @param string $name
     * @return Claim
     */
    public function make(string $name)
    {
        return $this->get($name, $this->$name());
    }

    /**
     * Get the Issuer (iss) claim.
     *
     * @return string
     */
    public function iss()
    {
        $uri = $this->request->getUri();

        return sprintf('%s://%s/%s', $uri->getScheme(), $uri->getHost(), trim($uri->getPath(), '/'));
    }

    /**
     * Get the Issued At (iat) claim.
     *
     * @return int
     */
    public function iat()
    {
        return Utils::now()->getTimestamp();
    }

    /**
     * Get the Expiration (exp) claim.
     *
     * @return int
     */
    public function exp()
    {
        return Utils::now()->addMinutes($this->ttl)->getTimestamp();
    }

    /**
     * Get the Not Before (nbf) claim.
     *
     * @return int
     */
    public function nbf()
    {
        return Utils::now()->getTimestamp();
    }

    /**
     * Get the JWT Id (jti) claim.
     *
     * @return string
     */
    public function jti()
    {
        return Str::random();
    }

    /**
     * Add a new claim mapping.
     *
     * @param string $name
     * @param string $classPath
     *
     * @return $this
     */
    public function extend(string $name, string $classPath)
    {
        $this->classMap[$name] = $classPath;

        return $this;
    }

    /**
     * Set the request instance.
     *
     * @param RequestInterface $request
     * @return $this
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Set the token ttl (in minutes).
     *
     * @param int $ttl
     * @return $this
     */
    public function setTTL(int $ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get the token ttl.
     *
     * @return int
     */
    public function getTTL()
    {
        return $this->ttl;
    }

    /**
     * Set the leeway in seconds.
     *
     * @param int $leeway
     * @return $this
     */
    public function setLeeway(int $leeway)
    {
        $this->leeway = $leeway;

        return $this;
    }
}
