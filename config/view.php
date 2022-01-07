<?php

return [

    /*
    |--------------------------------------------------------------------------
    | View configures
    |--------------------------------------------------------------------------
    |
    | more documents
    |
    | @see https://latte.nette.org/en/develop
    |
    */

    'debug' => env('VIEW_DEBUG', true),

    'assets_path' => resources_path('assets'),

    'cache' => storage_path('framework/views'),

    'path' => resources_path('views'),

    'auto_reload' => true,
];