<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Environment And Debug
    |--------------------------------------------------------------------------
    |
    | set the application environment, and should be debug?
    |
    */

    'env' => env('ENV', 'development'),

    'debug' => env('DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | use encryption with encrypt key and cipher
    |
    */

    'key' => env('KEY', 'default-framework-key'),

    'cipher' => 'AES-256-CBC',
];