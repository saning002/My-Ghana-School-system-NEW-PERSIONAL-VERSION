<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    */

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ── Africa's Talking (SMS for Ghana) ────────────────────────────────────
    'africastalking' => [
        'username'  => env('AT_USERNAME', 'sandbox'),
        'api_key'   => env('AT_API_KEY', ''),
        'sender_id' => env('AT_SENDER_ID', ''),   // Your registered sender ID (optional)
    ],

    // ── Twilio (WhatsApp) ────────────────────────────────────────────────────
    'twilio' => [
        'sid'            => env('TWILIO_SID', ''),
        'token'          => env('TWILIO_TOKEN', ''),
        'whatsapp_from'  => env('TWILIO_WHATSAPP_FROM', ''),  // e.g. +14155238886
    ],

];
