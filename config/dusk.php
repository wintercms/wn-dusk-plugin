<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dusk screenshot folder
    |--------------------------------------------------------------------------
    |
    | Defines the directory to save screenshots taken within Dusk tests.
    |
    */
    'screenshotsPath' => storage_path('dusk/screenshots'),

    /*
    |--------------------------------------------------------------------------
    | Dusk console folder
    |--------------------------------------------------------------------------
    |
    | Defines the directory to save console logs for failed Dusk tests.
    |
    */
    'consolePath' => storage_path('dusk/console'),

    /*
    |--------------------------------------------------------------------------
    | Dusk source folder
    |--------------------------------------------------------------------------
    |
    | Defines the directory to save source code for failed Dusk tests.
    |
    */
    'source' => storage_path('dusk/source'),
];
