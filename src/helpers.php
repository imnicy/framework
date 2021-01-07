<?php

if (! function_exists('main')) {
    /**
     * Get the manager instance.
     *
     * @return \Nicy\Framework\Main
     */
    function main()
    {
        return Nicy\Framework\Main::instance();
    }
}

if (! function_exists('container')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $make
     * @param  array  $parameters
     * @return mixed|\Nicy\Container\Contracts\Container
     */
    function container($make=null, array $parameters=[])
    {
        return main()->container($make, $parameters);
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param  array|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    function config($key=null, $default=null)
    {
        if (is_null($key)) {
            return container('config');
        }

        if (is_array($key)) {
            return container('config')->set($key);
        }

        return container('config')->get($key, $default);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @param  string  $path
     * @return string
     */
    function storage_path($path='')
    {
        return main()->path('storage' . DIRECTORY_SEPARATOR . $path);
    }
}

if (! function_exists('resources_path')) {
    /**
     * Get the path to the resources folder.
     *
     * @param  string  $path
     * @return string
     */
    function resources_path($path='')
    {
        return main()->path('resources' . DIRECTORY_SEPARATOR . $path);
    }
}

if (! function_exists('public_path')) {
    /**
     * Get the path to the public folder.
     *
     * @param  string  $path
     * @return string
     */
    function public_path($path='')
    {
        return main()->path('public' . DIRECTORY_SEPARATOR . $path);
    }
}

if (! function_exists('path')) {
    /**
     * Get the path to the base folder.
     *
     * @param  string  $path
     * @return string
     */
    function path($path='')
    {
        return main()->path($path);
    }
}

if (! function_exists('route')) {
    /**
     * Get url from route name
     *
     * @param string $routeName
     * @param array $data
     * @param array $queryParams
     * @return string
     */
    function route(string $routeName, array $data=[], array $queryParams=[]) :string
    {
        return container('url')->route($routeName, $data, $queryParams);
    }
}

if (! function_exists('url')) {
    /**
     * Get url from request uri
     *
     * @param string $path
     * @param array $extra
     * @param bool|null $secure
     * @return string
     */
    function url($path, $extra=[], $secure=null)
    {
        return container('url')->to($path, $extra, $secure);
    }
}

if (! function_exists('asset')) {
    /**
     * Get asset url from request uri
     *
     * @param $path
     * @param null $secure
     * @return string
     */
    function asset($path, $secure=null)
    {
        return container('url')->asset($path, $secure);
    }
}

if (! function_exists('cache')) {
    /**
     * Get a cache instance
     *
     * @param string|null $driver
     * @return \Phpfastcache\Helper\Psr16Adapter
     */
    function cache($driver=null)
    {
        return container('cache')->driver($driver);
    }
}

if (! function_exists('db')) {
    /**
     * Get a database instance from the connection
     *
     * @param string|null $connection
     * @return \Nicy\Framework\Bindings\DB\Query\Builder
     */
    function db($connection=null)
    {
        return container('db')->connection($connection);
    }
}

if (! function_exists('validate')) {
    /**
     * Validate inputs
     *
     * @param array $inputs
     * @param array $rules
     * @param array $messages
     * @return void|bool
     */
    function validate(array $inputs, array $rules, array $messages=[])
    {
        return container('validator')->validate($inputs, $rules, $messages);
    }
}

if (! function_exists('view')) {
    /**
     * Render a template to string
     *
     * @param string $name
     * @param array $context
     * @return string
     */
    function view($name, array $context=[])
    {
        return container('view')->render($name, $context);
    }
}

if (! function_exists('info')) {
    /**
     * Logging string and context to log file
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function info($message, array $context=[])
    {
        return container('logger')->info($message, $context);
    }
}

if (! function_exists('debug')) {
    /**
     * Logging string and context to log file
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function debug($message, array $context=[])
    {
        return container('logger')->debug($message, $context);
    }
}

if (! function_exists('warning')) {
    /**
     * Logging string and context to log file
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function warning($message, array $context=[])
    {
        return container('logger')->warning($message, $context);
    }
}

if (! function_exists('error')) {
    /**
     * Logging string and context to log file
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function error($message, array $context=[])
    {
        return container('logger')->error($message, $context);
    }
}

if (! function_exists('notice')) {
    /**
     * Logging string and context to log file
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function notice($message, array $context=[])
    {
        return container('logger')->notice($message, $context);
    }
}

if (! function_exists('log')) {
    /**
     * Logging string and context to log file
     *
     * @param int $level
     * @param string $message
     * @param array $context
     * @return void
     */
    function log($level, $message, array $context=[])
    {
        return container('logger')->log($level, $message, $context);
    }
}

if (! function_exists('event')) {
    /**
     * Dispatch a event
     *
     * @param mixed $event
     * @param array $payload
     * @return mixed
     */
    function event($event, $payload=[])
    {
        return container('events')->dispatch($event, $payload);
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed|\Nicy\Framework\Bindings\Session\Store|\Nicy\Framework\Bindings\Session\SessionManager
     */
    function session($key=null, $default=null)
    {
        if (is_null($key)) {
            return container('session');
        }

        if (is_array($key)) {
            return container('session')->put($key);
        }

        return container('session')->get($key, $default);
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     * @throws \RuntimeException
     */
    function csrf_token()
    {
        if (container()->has('session')) {
            return container('session')->token();
        }

        throw new RuntimeException('Application session store not set.');
    }
}

if (! function_exists('csrf_field')) {
    /**
     * Generate a CSRF token form field.
     *
     * @return string|\Nicy\Support\HtmlString
     */
    function csrf_field()
    {
        return new Nicy\Support\HtmlString('<input type="hidden" name="_token" value="'.csrf_token().'">');
    }
}

if (! function_exists('get_cookie')) {
    /**
     * Get cookie from request
     *
     * @param string $name
     * @param string|null $value
     * @return mixed
     */
    function get_cookie(string $name, ?string $value=null)
    {
        return container('cookie')->get($name, $value);
    }
}

if (! function_exists('set_cookie')) {
    /**
     * Make a SetCookie instance
     *
     * @param string $name
     * @param string|null $value
     * @return \Dflydev\FigCookies\SetCookie
     */
    function set_cookie(string $name, ?string $value=null)
    {
        return Nicy\Framework\Bindings\Cookie\Factory::setCookie($name, $value);
    }
}
