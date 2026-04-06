<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'storage/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => [
        'https://bersekolah.com',
        'https://www.bersekolah.com',
        'http://localhost:4321', // untuk development lokal
        'http://localhost:4322', // untuk development lokal
        'http://localhost:3000', // untuk development lokal
    ],
    'allowed_origins_patterns' => [
        '#^https://.*\.bersekolah\.com$#', // subdomain bersekolah.com
    ],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];