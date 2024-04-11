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
    'sourcePath' => storage_path('dusk/source'),

    /*
    |--------------------------------------------------------------------------
    | Form tester passthrough folder
    |--------------------------------------------------------------------------
    |
    | Defines the directory to save passthrough files used during Backend form
    | testing.
    |
    */
    'formTesterPassthroughPath' => storage_path('dusk/form-tester'),
];
