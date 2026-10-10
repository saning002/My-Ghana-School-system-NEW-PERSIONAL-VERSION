<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Paystack Keys
    |--------------------------------------------------------------------------
    | Get your keys from https://dashboard.paystack.com/#/settings/developers
    | Use test keys for development, live keys for production.
    */
    'public_key'  => env('PAYSTACK_PUBLIC_KEY', ''),
    'secret_key'  => env('PAYSTACK_SECRET_KEY', ''),
    'payment_url' => env('PAYSTACK_PAYMENT_URL', 'https://api.paystack.co'),
];
