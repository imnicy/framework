<?php

namespace Nicy\Framework\Bindings\Routing;

use Nicy\Framework\Container;
use Nicy\Support\Arr;
use Nicy\Support\Str;

class UrlGenerator
{
    /**
     * @var \Nicy\Framework\Container
     */
    protected $container;

    /**
     * @var \Psr\Http\Message\UriInterface
     */
    protected $uri;

    /**
     * The forced URL root.
     *
     * @var string
     */
    protected $forcedRoot;

    /**
     * The forced schema for URLs.
     *
     * @var string
     */
    protected $forceScheme;

    /**
     * The cached URL root.
     *
     * @var string|null
     */
    protected $cachedRoot;

    /**
     * A cached copy of the URL schema for the current request.
     *
     * @var string|null
     */
    protected $cachedSchema;

    /**
     * Create a new URL redirector instance.
     *
     * @param \Nicy\Framework\Container
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->uri = $container->get('request')->getUri();
    }

    /**
     * Get the full URL for the current request.
     *
     * @return string
     */
    public function full()
    {
        $scheme = $this->uri->getScheme();
        $authority = $this->uri->getAuthority();
        $path = $this->uri->getPath();
        $query = $this->uri->getQuery();

        $protocol = ($scheme ? $scheme . ':' : '') . ($authority ? '//' . $authority : '');

        return $protocol . $path . ($query ? '?'.$query : '');
    }

    /**
     * @return string
     */
    public function path()
    {
        return $this->uri->getPath();
    }

    /**
     * Get the current URL for the request.
     *
     * @return string
     */
    public function current()
    {
        return $this->to($this->uri->getPath());
    }

    /**
     * Generate a url for the application.
     *
     * @param string $path
     * @param array $extra
     * @param bool $secure
     * @return string
     */
    public function to($path, $extra=[], $secure=null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $scheme = $this->getSchemeForUrl($secure);
        $extra = $this->formatParameters($extra);

        $tail = implode('/', array_map(
                'rawurlencode', (array) $extra)
        );

        $root = $this->getRootUrl($scheme);
        return $this->trimUrl($root, $path, $tail);
    }

    /**
     * Generate a secure, absolute URL to the given path.
     *
     * @param string $path
     * @param array  $parameters
     * @return string
     */
    public function secure($path, $parameters=[])
    {
        return $this->to($path, $parameters, true);
    }

    /**
     * Generate a URL to an application asset.
     *
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function asset($path, $secure=null)
    {
        if ($this->isValidUrl($path)) {
            return $path;
        }

        $root = $this->getRootUrl($this->formatScheme($secure));

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

    /**
     * Generate a URL to an application asset from a root domain such as CDN etc.
     *
     * @param string $root
     * @param string $path
     * @param bool|null $secure
     * @return string
     */
    public function assetFrom($root, $path, $secure=null)
    {
        $root = $this->getRootUrl($this->formatScheme($secure), $root);

        return $this->removeIndex($root).'/'.trim($path, '/');
    }

    /**
     * Remove the index.php file from a path.
     *
     * @param string $root
     * @return string
     */
    protected function removeIndex($root)
    {
        $i = 'index.php';

        return Str::contains($root, $i) ? str_replace('/'.$i, '', $root) : $root;
    }

    /**
     * Generate a URL to a secure asset.
     *
     * @param string $path
     * @return string
     */
    public function secureAsset($path)
    {
        return $this->asset($path, true);
    }

    /**
     * Force the schema for URLs.
     *
     * @param string $schema
     * @return void
     */
    public function forceScheme($schema)
    {
        $this->cachedSchema = null;

        $this->forceScheme = $schema.'://';
    }

    /**
     * Get the default scheme for a raw URL.
     *
     * @param bool|null $secure
     * @return string
     */
    public function formatScheme($secure)
    {
        if (! is_null($secure)) {
            return $secure ? 'https://' : 'http://';
        }

        if (is_null($this->cachedSchema)) {
            $this->cachedSchema = $this->forceScheme ?: $this->uri->getScheme().'://';
        }

        return $this->cachedSchema;
    }

    /**
     * Get the URL to a named route.
     *
     * @param string $name
     * @param array  $parameters
     * @param array $queryParams
     * @return string
     * @throws \InvalidArgumentException
     */
    public function route($name, $parameters=[], $queryParams=[])
    {
        return $this->container->get('router.parser')->fullUrlFor(
            $this->uri, $name, $parameters, $queryParams
        );
    }

    /**
     * Determine if the given path is a valid URL.
     *
     * @param string $path
     * @return bool
     */
    public function isValidUrl($path)
    {
        if (Str::startsWith($path, ['#', '//', 'mailto:', 'tel:', 'http://', 'https://'])) {
            return true;
        }

        return filter_var($path, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Get the scheme for a raw URL.
     *
     * @param bool|null $secure
     * @return string
     */
    protected function getSchemeForUrl($secure)
    {
        if (is_null($secure)) {
            if (is_null($this->cachedSchema)) {
                $this->cachedSchema = $this->formatScheme($secure);
            }

            return $this->cachedSchema;
        }

        return $secure ? 'https://' : 'http://';
    }

    /**
     * Format the array of URL parameters.
     *
     * @param mixed|array $parameters
     * @return array
     */
    public function formatParameters($parameters)
    {
        $parameters = Arr::wrap($parameters);

        return $parameters;
    }

    /**
     * Replace the route parameters with their parameter.
     *
     * @param string $route
     * @param array $parameters
     * @return string
     */
    protected function replaceRouteParameters($route, &$parameters=[])
    {
        return preg_replace_callback('/\{(.*?)(:.*?)?(\{[0-9,]+\})?\}/', function ($m) use (&$parameters) {
            return isset($parameters[$m[1]]) ? Arr::pull($parameters, $m[1]) : $m[0];
        }, $route);
    }

    /**
     * Get the base URL for the request.
     *
     * @param string $scheme
     * @param string $root
     * @return string
     */
    protected function getRootUrl($scheme, $root=null)
    {
        if (is_null($root)) {
            if (is_null($this->cachedRoot)) {
                if (! $this->forcedRoot) {
                    $uri = $this->uri;

                    $uriScheme = $uri->getScheme();
                    $uriAuthority = $uri->getAuthority();

                    $this->cachedRoot = ($uriScheme ? $uriScheme . ':' : '') . ($uriAuthority ? '//' . $uriAuthority : '');
                }
                else {
                    $this->cachedRoot = $this->forcedRoot;
                }
            }

            $root = $this->cachedRoot;
        }

        $start = Str::startsWith($root, 'http://') ? 'http://' : 'https://';

        return preg_replace('~'.$start.'~', $scheme, $root, 1);
    }

    /**
     * Set the forced root URL.
     *
     * @param string $root
     * @return void
     */
    public function forceRootUrl($root)
    {
        $this->forcedRoot = rtrim($root, '/');

        $this->cachedRoot = null;
    }

    /**
     * Format the given URL segments into a single URL.
     *
     * @param string $root
     * @param string $path
     * @param string $tail
     * @return string
     */
    protected function trimUrl($root, $path, $tail='')
    {
        return trim($root.'/'.trim($path.'/'.$tail, '/'), '/');
    }
}
