<?php

return [
    'enableRoutesCache' => env('ROUTES_CACHE', false),
    'enableAssetCache' => env('ASSET_CACHE', false),
    'databaseTemplates' => env('DATABASE_TEMPLATES', false),
    'linkPolicy' => env('LINK_POLICY', 'detect'),
    'enableCsrfProtection' => env('ENABLE_CSRF', true),
];
