<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'exchange_rates' => [
        'api_key' => env('EXCHANGE_RATE_API_KEY'),
    ],

    'tax_api' => [
        'enabled' => env('TAX_API_ENABLED', false),
        'url' => env('TAX_API_URL'),
        'api_key' => env('TAX_API_KEY'),
        'version' => env('TAX_API_VERSION', 'v1'),
        'cache_duration' => env('TAX_API_CACHE_DURATION', 3600),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
