<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Spreedly Environment Key
    |--------------------------------------------------------------------------
    |
    | Your Spreedly environment key. Found in your Spreedly dashboard under
    | Developers > API credentials.
    |
    */
    'environment_key' => env('SPREEDLY_ENVIRONMENT_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Spreedly Access Secret
    |--------------------------------------------------------------------------
    |
    | Your Spreedly access secret. Found alongside your environment key in
    | the Spreedly dashboard.
    |
    */
    'access_secret' => env('SPREEDLY_ACCESS_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Spreedly Options
    |--------------------------------------------------------------------------
    |
    | Additional configuration options for the Spreedly HTTP client.
    |
    */
    'options' => [
        'base_url' => env('SPREEDLY_BASE_URL', 'https://core.spreedly.com/v1/'),
        'timeout' => (int) env('SPREEDLY_TIMEOUT', 30),
        'connect_timeout' => (int) env('SPREEDLY_CONNECT_TIMEOUT', 10),
        'retries' => (int) env('SPREEDLY_RETRIES', 3),
    ],
];
