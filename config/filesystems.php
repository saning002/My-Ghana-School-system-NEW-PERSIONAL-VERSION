<?php

return [

    'default' => env('FILESYSTEM_DISK', 'local'),

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root'   => storage_path('app/private'),
            'serve'  => true,
            'throw'  => false,
        ],

        'public' => [
            'driver'     => 'local',
            'root'       => storage_path('app/public'),
            'url'        => env('APP_URL') . '/storage',
            'visibility' => 'public',
            'throw'      => false,
        ],

        // ── Backblaze B2 — persistent file storage for PDFs, documents etc. ──
        // Set these environment variables on Render:
        //   B2_KEY_ID        = your Backblaze Application Key ID
        //   B2_APPLICATION_KEY = your Backblaze Application Key
        //   B2_BUCKET        = your bucket name (e.g. kingdom-school-files)
        //   B2_REGION        = your bucket region (e.g. us-west-004)
        //   B2_ENDPOINT      = https://s3.{region}.backblazeb2.com
        //
        // Get these from backblaze.com → Buckets → App Keys
        'b2' => [
            'driver'                  => 's3',
            'key'                     => env('B2_KEY_ID'),
            'secret'                  => env('B2_APPLICATION_KEY'),
            'region'                  => env('B2_REGION', 'us-west-004'),
            'bucket'                  => env('B2_BUCKET'),
            'endpoint'                => env('B2_ENDPOINT'),
            'use_path_style_endpoint' => true,
            'url'                     => env('B2_URL'),  // optional public URL
            'visibility'              => 'public',
            'throw'                   => false,
        ],

        // ── AWS S3 (alternative to B2) ────────────────────────────────────────
        's3' => [
            'driver'     => 's3',
            'key'        => env('AWS_ACCESS_KEY_ID'),
            'secret'     => env('AWS_SECRET_ACCESS_KEY'),
            'region'     => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket'     => env('AWS_BUCKET'),
            'url'        => env('AWS_URL'),
            'endpoint'   => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw'      => false,
        ],

    ],

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
