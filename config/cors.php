<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE'], // Batasi metode HTTP

    'allowed_origins' => ['http://127.0.0.1:8000'], // Tentukan domain frontend

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization'], // Tentukan header yang diizinkan

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // Jika menggunakan autentikasi berbasis session/cookies

];


/*
return [

    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
*/
