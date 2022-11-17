<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | This option defines the default database connection name that gets used
    | when connect to database
    |
    */

    'default' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Supports all SQL databases, including MySQL, MSSQL, SQLite, MariaDB,
    | PostgreSQL, Sybase, Oracle and more

    | @see https://medoo.in/api/new
    |
    */

    'connections' => [

        'default' => [
            // required
            'type' => 'mysql',
            'database' => env('DB_NAME', 'sample'),
            'host' => env('DB_HOST', '127.0.0.1:3306'),
            'username' => env('DB_USER', 'root'),
            'password' => env('DB_PASSWORD'),

            // [optional]
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',

            // [optional] Table prefix
            'prefix' => ''
        ]

    ],

];
