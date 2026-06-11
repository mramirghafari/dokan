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

    'customer_portal_gateway' => [
        'provider' => env('CUSTOMER_PORTAL_GATEWAY_PROVIDER', 'sandbox'),
        'payment_url' => env('CUSTOMER_PORTAL_GATEWAY_PAYMENT_URL'),
        'verification_url' => env('CUSTOMER_PORTAL_GATEWAY_VERIFICATION_URL'),
        'merchant_id' => env('CUSTOMER_PORTAL_GATEWAY_MERCHANT_ID'),
        'terminal_id' => env('CUSTOMER_PORTAL_GATEWAY_TERMINAL_ID'),
        'timeout' => env('CUSTOMER_PORTAL_GATEWAY_TIMEOUT', 15),
    ],

];
