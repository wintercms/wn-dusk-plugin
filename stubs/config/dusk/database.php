<?php

return [
    'default' => 'sqlite',
    'connections' => [
        'sqlite' => [
            'driver'   => 'sqlite',
            'database' => 'storage/dusk.sqlite',
        ],
    ],
    'useConfigForTesting' => false,
];
