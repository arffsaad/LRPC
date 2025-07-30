<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Optional authentication middleware for incoming requests
    |--------------------------------------------------------------------------
    | If set, LRPC will use this to validate external calls (e.g., API key)
    */
    'authentication' => null,

    /*
    |--------------------------------------------------------------------------
    | External service endpoints
    |--------------------------------------------------------------------------
    */
    'services' => [
    //     'OrdersService' => [
    //         'url' => env('ORDERS_SERVICE_URL'),
    //         'auth' => null, // optional: could be an API key, token, etc.
    //     ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Namespace configuration
    |--------------------------------------------------------------------------
    */
    'namespaces' => [
        'internal' => 'App\\Lrpc\\Internal',
        'external' => 'App\\Lrpc\\External',
    ],
];
