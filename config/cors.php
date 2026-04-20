<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | env('FRONTEND_URL') should be set to your deployed Next.js URL in
    | production (e.g. https://yoursite.vercel.app).
    | For local dev, add http://localhost:3000 to CORS_ALLOWED_ORIGINS.
    |
    | NOTE: The original config had a bug — env() only accepts 2 args,
    | so the third 'http://localhost:5173' was silently ignored.
    | We now use a comma-separated env var instead.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
     * Parse a comma-separated CORS_ALLOWED_ORIGINS env var so you can list
     * multiple origins without touching this file:
     *
     *   CORS_ALLOWED_ORIGINS=https://yoursite.vercel.app,http://localhost:3000
     */
    'allowed_origins' => array_filter(
        array_map(
            'trim',
            explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:3000'))
        )
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 86400,

    /*
     * Keep false unless you're sending cookies/sessions cross-origin.
     * JWT is Bearer-token based — credentials not required.
     */
    'supports_credentials' => false,

];
