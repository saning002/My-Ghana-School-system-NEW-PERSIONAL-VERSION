<?php

return [
    'notification_url' => env('CLOUDINARY_NOTIFICATION_URL'),

    // cloud_url is intentionally null by default.
    // TenantMiddleware sets config('cloudinary.cloud_url') at runtime
    // using the per-school credentials stored in the owner panel.
    // If a system-wide CLOUDINARY_URL env var is set on Render it is used
    // as a fallback, otherwise null tells CloudinaryService to check the
    // individual cloud_name / api_key / api_secret keys instead.
    'cloud_url'   => env('CLOUDINARY_URL') ?: null,
    'cloud_name'  => env('CLOUDINARY_CLOUD_NAME') ?: null,
    'api_key'     => env('CLOUDINARY_API_KEY')    ?: null,
    'api_secret'  => env('CLOUDINARY_API_SECRET') ?: null,

    'upload_preset' => env('CLOUDINARY_UPLOAD_PRESET'),
    'upload_route'  => env('CLOUDINARY_UPLOAD_ROUTE'),
    'upload_action' => env('CLOUDINARY_UPLOAD_ACTION'),
];
