<?php

namespace Nicy\Framework\Support\Helpers;

use Nicy\Framework\Main;
use Nicy\Support\Collection;
use Nicy\Support\Str;
use InvalidArgumentException;
use Psr\Http\Message\UploadedFileInterface;

class Request
{
    /**
     * @var \Nicy\Support\Collection
     */
    protected static $attributes;

    /**
     * @return \Nicy\Support\Collection
     */
    public static function all()
    {
        return static::collection();
    }

    /**
     * @return Collection
     */
    public static function queries()
    {
        return static::collection(true, false, false, false);
    }

    /**
     * @return Collection
     */
    public static function requests()
    {
        return static::collection(false, true, false, false);
    }

    /**
     * @return Collection
     */
    public static function files()
    {
        return static::collection(false, false, true, false);
    }

    /**
     * @return Collection
     */
    public static function params()
    {
        return static::collection(false, false, false, true);
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public static function getRequest()
    {
        return Main::instance()->container('request');
    }

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public static function getUri()
    {
        return static::getRequest()->getUri();
    }

    /**
     * @param bool $withQueries
     * @param bool $withRequests
     * @param bool $withFiles
     * @param bool $withParams
     * @return \Nicy\Support\Collection
     */
    protected static function collection($withQueries=true, $withRequests=true, $withFiles=true, $withParams=false)
    {
        if (static::$attributes) {
            return static::$attributes;
        }

        $request = static::getRequest();

        if (Str::contains($request->getHeaderLine('Content-Type'), ['+json', '/json'])) {
            $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        }

        $attributes = [];

        if ($withQueries) {
            $attributes = $attributes + (array) $request->getQueryParams();
        }

        if ($withRequests) {
            $attributes = $attributes + (array) $request->getParsedBody();
        }

        if ($withFiles) {
            $attributes = $attributes + (array) $request->getUploadedFiles();
        }

        if ($withParams) {
            $attributes = $attributes + (array) $request->getServerParams();
        }

        return static::$attributes = new Collection($attributes);
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function get(string $name, $default=null)
    {
        return static::queries()->get($name, $default);
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function request(string $name, $default=null)
    {
        return static::requests()->get($name, $default);
    }

    /**
     * @param string $name
     * @return mixed
     */
    public static function file(string $name)
    {
        return static::files()->get($name);
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function param(string $name, $default=null)
    {
        return static::params()->get($name, $default);
    }

    /**
     * @param string $name
     * @param null|mixed $default
     * @return mixed
     */
    public static function input(string $name, $default=null)
    {
        return static::all()->get($name, $default);
    }

    /**
     * @param string $key
     * @param bool $unique
     * @param string $disk
     * @return string|false
     */
    public static function upload(string $key, bool $unique=false, string $disk=null)
    {
        if ($file = static::file($key)) {
            if (! $file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid upload file ['.$key.']');
            }
        }

        $filesystemManager = Main::instance()->container('filesystem');
        $filesystem = $filesystemManager->disk($disk);

        if ($file->getError() === UPLOAD_ERR_OK) {
            $filename = $file->getClientFilename();

            if ($unique) {
                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                $basename = Str::random(16);
                $filename = sprintf('%s.%0.8s', $basename, $extension);
            }

            $uploaded = $filesystem->put($filename, $file->getStream()->getContents());

            if ($uploaded) {
                return $filename;
            }
        }

        return false;
    }
}