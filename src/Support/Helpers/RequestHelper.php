<?php

namespace Nicy\Framework\Support\Helpers;

use InvalidArgumentException;
use Nicy\Framework\Main;
use Nicy\Support\Collection;
use Nicy\Support\Str;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UploadedFileInterface;

class RequestHelper
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
        return static::newCollection(true, true, true);
    }

    /**
     * @return Collection
     */
    public static function queries()
    {
        return static::newCollection(true, false, false);
    }

    /**
     * @return Collection
     */
    public static function requests()
    {
        return static::newCollection(false, true, false);
    }

    public static function files()
    {
        return static::newCollection(false, false, true);
    }

    /**
     * @param bool $withQueries
     * @param bool $withRequests
     * @param bool $withFiles
     *
     * @return \Nicy\Support\Collection
     */
    protected static function newCollection($withQueries = true, $withRequests = true, $withFiles = true)
    {
        if (static::$attributes) {
            return static::$attributes;
        }

        $request = Main::getInstance()->container('request');

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

        return static::$attributes = new Collection($attributes);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public static function get(string $name, $default = null)
    {
        return static::queries()->get($name, $default);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public static function request(string $name, $default = null)
    {
        return static::requests()->get($name, $default);
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    public static function file(string $name)
    {
        return static::files()->get($name);
    }

    /**
     * @param string $name
     * @param null $default
     *
     * @return mixed
     */
    public static function input(string $name, $default = null)
    {
        return static::all()->get($name, $default);
    }

    /**
     * @param string $key
     * @param bool $unique
     * @param null $disk
     *
     * @return string|false
     */
    public static function upload(string $key, bool $unique = false, string $disk = null)
    {
        if ($file = static::file($key)) {

            if (! $file instanceof UploadedFileInterface) {
                throw new InvalidArgumentException('Invalid upload file ['.$key.']');
            }
        }

        $filesystemManager = Main::getInstance()->container('filesystem');

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