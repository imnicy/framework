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
            'database_type' => 'mysql',
            'database_name' => 'test',
            'server' => '192.168.254.220',
            'username' => 'root',
            'password' => 'abcdEF122',

            // [optional]
            'charset' => 'utf8',
            'collation' => 'utf8_general_ci',
            'port' => 3306,

            // [optional] Table prefix
            'prefix' => ''
        ]

    ],

];
