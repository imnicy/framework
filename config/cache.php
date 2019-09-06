<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Cache Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default cache connection that gets used while
    | using this caching library. This connection is used when another is
    | not explicitly specified when executing a given caching function.
    |
    */

    'default' => 'files',

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */

    'stores' => [

        'apc' => [
            'driver' => 'apc',
        ],

        'files' => [
            'driver' => 'files',
            'path'   => storage_path('cache'),
        ],

        'memcached' => [
            'driver'  => 'memcached',

            'host' => '127.0.0.1',
            'port' => '11211',
        ],

        'redis' => [

            'driver' => 'redis',
            'client' => 'redis',

            'host' => '127.0.0.1',
            'port' => 6379,
            'password' => null,
            'database' => null,
        ],

    ]

];
