<?php

return [
    'default' => env('DB_CONNECTION', 'sqlite'),
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => env('DB_DATABASE', 'storage/dusk.sqlite'),
        ],
    ],
    'useConfigForTesting' => env('DB_USE_CONFIG_FOR_TESTING', false),
];
