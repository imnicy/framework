<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Twig view configures
    |--------------------------------------------------------------------------
    |
    | more documents
    |
    | @see https://twig.symfony.com/doc/2.x/
    |
    */

    'path' => resource_path('views'),

    'debug' => true,

    'charset' => 'UTF-8',

    'strict_variables' => false,

    'autoescape' => 'html',

    'cache' => storage_path('cache/views'),

    'auto_reload' => true,

    'optimizations' => -1,

];