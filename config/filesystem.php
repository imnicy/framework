<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "public" disk, as well as a variety of cloud
    | based disks are available to your application
    |
    */

    'default' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: local, cloud drivers and more
    |
    | @see https://packagist.org/packages/league/flysystem
    |
    */

    'disks' => [

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
        ],

    ],

];
