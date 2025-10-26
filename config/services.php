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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/api/auth/google/callback'),
    ],

    'socialite' => [
        'google' => [
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/api/auth/google/callback'),
        ],
    ],

    'n8n' => [
        'base_url' => env('N8N_BASE_URL', 'http://localhost:5678'),
        'webhook_url' => env('N8N_WEBHOOK_URL', 'http://localhost:5678/webhook/skincare-simulation'),
        'api_key' => env('N8N_API_KEY'),
        'timeout' => env('N8N_TIMEOUT', 150),
        'mock_enabled' => env('N8N_MOCK_ENABLED', true),
        'failover' => [
            'enabled' => env('N8N_FAILOVER_ENABLED', true),
            'provider_order' => array_values(array_filter(array_map(
                'trim',
                explode(',', env('N8N_FAILOVER_PROVIDERS', 'openai,gemini,claude'))
            ))),
            'max_retries' => env('N8N_FAILOVER_MAX_RETRIES', 2),
        ],
    ],

    'whatsapp' => [
        'enabled' => env('WHATSAPP_ENABLED', false),
        'business_number' => env('WHATSAPP_BUSINESS_NUMBER'),
        'default_message' => env(
            'WHATSAPP_DEFAULT_MESSAGE',
            'Halo, saya tertarik dengan simulasi produk :product (ID: :simulation_id).'
        ),
    ],

];
