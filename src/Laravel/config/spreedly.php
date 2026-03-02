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
    | MAC Address Certificate Binding
    |--------------------------------------------------------------------------
    |
    | When enabled, certificates are bound to the machine they were created on
    | using its MAC address. SpreedlyCertificate::current() will then prefer
    | the certificate matching the current machine over the global default.
    |
    | mac_address_command should be a shell expression whose stdout is the MAC
    | address, e.g. on macOS:
    |   ifconfig en0 | awk '/ether/{print $2}'
    |
    */
    'mac_address_enabled' => env('SPREEDLY_MAC_ADDRESS_ENABLED', false),
    'mac_address_command' => env('SPREEDLY_MAC_ADDRESS_COMMAND', ''),
    'mac_address' => env('SPREEDLY_MAC_ADDRESS', ''),
];
