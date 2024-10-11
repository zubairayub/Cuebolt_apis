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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'facebook' => [
        'client_id' => env(key:'FACEBOOK_CLIENT_ID'),
        'client_secret' => env(key:'FACEBOOK_CLIENT_SECRET'),
        'redirect' => 'https://127.0.0.1:8000/authenticate/callback/facebook'

    ],

    'google' => [
        'client_id' => env(key:'GOOGLE_CLIENT_ID'),
        'client_secret' => env(key:'GOOGLE_CLIENT_SECRET'),
        'redirect' => 'https://127.0.0.1:8000/authenticate/callback/google'

    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
