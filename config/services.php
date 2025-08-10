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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Payment Gateway Services
    |--------------------------------------------------------------------------
    */

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'connect_client_id' => env('STRIPE_CONNECT_CLIENT_ID'),
    ],

    'paypal' => [
        'mode' => env('PAYPAL_MODE', 'sandbox'), // sandbox or live
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'client_secret' => env('PAYPAL_CLIENT_SECRET'),
        'webhook_id' => env('PAYPAL_WEBHOOK_ID'),
    ],

    'fawry' => [
        'merchant_code' => env('FAWRY_MERCHANT_CODE'),
        'security_key' => env('FAWRY_SECURITY_KEY'),
        'integration_id' => env('FAWRY_INTEGRATION_ID'),
        'base_url' => env('FAWRY_BASE_URL', 'https://atfawry.fawrystaging.com'),
    ],

    'paymob' => [
        'api_key' => env('PAYMOB_API_KEY'),
        'iframe_id' => env('PAYMOB_IFRAME_ID'),
        'integration_id' => env('PAYMOB_INTEGRATION_ID'),
        'hmac_secret' => env('PAYMOB_HMAC_SECRET'),
        'base_url' => env('PAYMOB_BASE_URL', 'https://accept.paymob.com/api'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Services
    |--------------------------------------------------------------------------
    */

    'aramex' => [
        'username' => env('ARAMEX_USERNAME'),
        'password' => env('ARAMEX_PASSWORD'),
        'account_number' => env('ARAMEX_ACCOUNT_NUMBER'),
        'account_pin' => env('ARAMEX_ACCOUNT_PIN'),
        'account_entity' => env('ARAMEX_ACCOUNT_ENTITY'),
        'account_country_code' => env('ARAMEX_ACCOUNT_COUNTRY_CODE'),
        'base_url' => env('ARAMEX_BASE_URL', 'https://ws.aramex.net'),
    ],

];
