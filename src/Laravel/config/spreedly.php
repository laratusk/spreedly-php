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

    /*
    |--------------------------------------------------------------------------
    | MAC Address Override
    |--------------------------------------------------------------------------
    |
    | The SDK automatically detects the machine's MAC address to bind
    | certificates per-server. Set this only if auto-detection does not work
    | in your environment (e.g., containerised deployments with no network
    | interface).
    |
    */
    'mac_address' => env('SPREEDLY_MAC_ADDRESS', ''),

    /*
    |--------------------------------------------------------------------------
    | Certificate Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for auto-generated self-signed certificates used by
    | Spreedly's certificate pinning feature.
    |
    | certificate_days_valid: How many days the generated certificate is valid.
    | certificate_key_bits:   RSA key size in bits. 2048 is the minimum recommended.
    |
    */
    'certificate_days_valid' => (int) env('SPREEDLY_CERTIFICATE_DAYS_VALID', 365),
    'certificate_key_bits' => (int) env('SPREEDLY_CERTIFICATE_KEY_BITS', 2048),
    'certificate_expiring_days' => (int) env('SPREEDLY_CERTIFICATE_EXPIRING_DAYS', 7),
];
