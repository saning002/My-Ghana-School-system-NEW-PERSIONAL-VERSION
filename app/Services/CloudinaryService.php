<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * Handles file uploads to Cloudinary using the cloudinary/cloudinary_php SDK.
 * Supports images, PDF documents, Word (.doc/.docx), and other assets.
 *
 * Credentials must be set as environment variables (on Render: Environment panel):
 *   CLOUDINARY_URL=cloudinary://API_KEY:API_SECRET@CLOUD_NAME
 *   — OR individually —
 *   CLOUDINARY_CLOUD_NAME, CLOUDINARY_API_KEY, CLOUDINARY_API_SECRET
 */
class CloudinaryService
{
    /**
     * Build a cloudinary:// URL from available credentials.
     * Returns null if credentials are not properly set.
     */
    private static function cloudinaryUrl(): ?string
    {
        // 0. Check per-tenant config set by TenantMiddleware (highest priority)
        // This allows each school to have its own Cloudinary account
        $tenantUrl = config('cloudinary.cloud_url', '');
        if (
            is_string($tenantUrl) &&
            strlen($tenantUrl) > 20 &&
            str_starts_with($tenantUrl, 'cloudinary://') &&
            ! str_contains($tenantUrl, 'null') &&
            ! str_contains($tenantUrl, 'your_') &&
            ! str_contains($tenantUrl, 'API_KEY') &&
            ! str_contains($tenantUrl, 'API_SECRET') &&
            ! str_contains($tenantUrl, '://:') &&   // empty key
            ! preg_match('/cloudinary:\/\/\s*:\s*@/', $tenantUrl)  // key:secret both empty
        ) {
            return $tenantUrl;
        }

        // Also check individual config keys set by middleware
        $cfgName   = config('cloudinary.cloud_name', '');
        $cfgKey    = config('cloudinary.api_key', '');
        $cfgSecret = config('cloudinary.api_secret', '');
        if (
            is_string($cfgName) && strlen($cfgName) > 2 &&
            is_string($cfgKey)  && strlen($cfgKey)  > 5 &&
            is_string($cfgSecret) && strlen($cfgSecret) > 5
        ) {
            return "cloudinary://{$cfgKey}:{$cfgSecret}@{$cfgName}";
        }

        // 1. Try CLOUDINARY_URL directly (getenv works on Render + local)
        $url = getenv('CLOUDINARY_URL') ?: env('CLOUDINARY_URL', '');
        if (
            is_string($url) &&
            strlen($url) > 20 &&
            str_starts_with($url, 'cloudinary://') &&
            ! str_contains($url, 'null') &&
            ! str_contains($url, 'your_') &&
            ! str_contains($url, 'API_KEY') &&
            ! str_contains($url, 'API_SECRET')
        ) {
            return $url;
        }

        // 2. Build from individual env vars
        $name   = getenv('CLOUDINARY_CLOUD_NAME') ?: env('CLOUDINARY_CLOUD_NAME', '');
        $key    = getenv('CLOUDINARY_API_KEY')    ?: env('CLOUDINARY_API_KEY', '');
        $secret = getenv('CLOUDINARY_API_SECRET') ?: env('CLOUDINARY_API_SECRET', '');

        if (
            is_string($name) && strlen($name) > 2 &&
            is_string($key)  && strlen($key)  > 5 &&
            is_string($secret) && strlen($secret) > 5 &&
            $name !== 'your_cloud_name' && $key !== 'your_api_key'
        ) {
            return "cloudinary://{$key}:{$secret}@{$name}";
        }

        return null;
    }

    /**
     * Whether Cloudinary is properly configured.
     */
    public static function isConfigured(): bool
    {
        return self::cloudinaryUrl() !== null;
    }

    /**
     * Upload a file (image, PDF, Word doc, etc.) to Cloudinary and return the secure HTTPS URL.
     * Uses 'auto' resource_type so Cloudinary automatically handles PDFs, docs, and images.
     * Returns null on failure — callers fall back to local storage.
     */
    public static function upload(UploadedFile $file, string $folder = 'uploads', string $resourceType = 'auto'): ?string
    {
        $cloudinaryUrl = self::cloudinaryUrl();

        if ($cloudinaryUrl === null) {
            Log::warning('CloudinaryService: not configured. Set CLOUDINARY_URL in environment variables.');
            return null;
        }

        try {
            // Cloudinary PHP SDK v3 — initialise using the CLOUDINARY_URL string
            // by setting it as an env variable for this process, then constructing
            // the instance. The v3 SDK reads CLOUDINARY_URL automatically.
            $cloudName = config('cloudinary.cloud_name')  ?: null;
            $apiKey    = config('cloudinary.api_key')     ?: null;
            $apiSecret = config('cloudinary.api_secret')  ?: null;

            // Parse from URL if individual keys not available
            if (!$cloudName || !$apiKey || !$apiSecret) {
                // cloudinary://API_KEY:API_SECRET@CLOUD_NAME
                if (preg_match('|cloudinary://([^:]+):([^@]+)@(.+)|', $cloudinaryUrl, $m)) {
                    $apiKey    = $m[1];
                    $apiSecret = $m[2];
                    $cloudName = $m[3];
                }
            }

            if (!$cloudName || !$apiKey || !$apiSecret) {
                Log::error('CloudinaryService: could not parse credentials from URL');
                return null;
            }

            // Build the Cloudinary instance using the v3 Configuration array
            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key'    => $apiKey,
                    'api_secret' => $apiSecret,
                ],
                'url' => [
                    'secure' => true,
                ],
            ]);

            $result = $cloudinary->uploadApi()->upload($file->getRealPath(), [
                'folder'          => $folder,
                'resource_type'   => $resourceType,
                'use_filename'    => true,
                'unique_filename' => true,
                'overwrite'       => false,
            ]);

            $url = $result['secure_url'] ?? null;

            if ($url) {
                Log::info('CloudinaryService: upload successful', [
                    'url'           => $url,
                    'public_id'     => $result['public_id'] ?? null,
                    'resource_type' => $result['resource_type'] ?? $resourceType,
                    'bytes'         => $result['bytes'] ?? null,
                ]);
                return $url;
            }

            Log::error('CloudinaryService: upload returned no secure_url', [
                'result_keys' => array_keys((array) $result),
            ]);
            return null;

        } catch (\Throwable $e) {
            Log::error('CloudinaryService: upload failed', [
                'message' => $e->getMessage(),
                'folder'  => $folder,
                'file'    => $file->getClientOriginalName(),
                'trace'   => substr($e->getTraceAsString(), 0, 500),
            ]);
            return null;
        }
    }

    /**
     * Delete a file from Cloudinary by its secure URL.
     */
    public static function delete(string $url): void
    {
        if (! str_contains($url, 'cloudinary.com')) {
            return;
        }

        $cloudinaryUrl = self::cloudinaryUrl();
        if ($cloudinaryUrl === null) {
            return;
        }

        try {
            $cloudName = config('cloudinary.cloud_name')  ?: null;
            $apiKey    = config('cloudinary.api_key')     ?: null;
            $apiSecret = config('cloudinary.api_secret')  ?: null;

            if (!$cloudName || !$apiKey || !$apiSecret) {
                if (preg_match('|cloudinary://([^:]+):([^@]+)@(.+)|', $cloudinaryUrl, $m)) {
                    $apiKey = $m[1]; $apiSecret = $m[2]; $cloudName = $m[3];
                }
            }

            if (!$cloudName || !$apiKey || !$apiSecret) return;

            $cloudinary = new \Cloudinary\Cloudinary([
                'cloud' => ['cloud_name' => $cloudName, 'api_key' => $apiKey, 'api_secret' => $apiSecret],
                'url'   => ['secure' => true],
            ]);

            // Detect resource type
            $resourceType = 'image';
            if (preg_match('#/raw/upload/#', $url)) {
                $resourceType = 'raw';
            } elseif (preg_match('#/video/upload/#', $url)) {
                $resourceType = 'video';
            }

            if ($resourceType === 'raw') {
                if (preg_match('#/raw/upload/(?:v\d+/)?(.+)$#', $url, $m)) {
                    $publicId = $m[1];
                    $cloudinary->uploadApi()->destroy($publicId, ['resource_type' => 'raw']);
                    Log::info('CloudinaryService: deleted raw asset', ['public_id' => $publicId]);
                    return;
                }
            }

            if (preg_match('#/(?:image|video)/upload/(?:v\d+/)?(.+)\.[a-zA-Z0-9]+$#', $url, $m)) {
                $publicId = $m[1];
                $cloudinary->uploadApi()->destroy($publicId, ['resource_type' => $resourceType]);
                Log::info('CloudinaryService: deleted asset', ['public_id' => $publicId]);
            }
        } catch (\Throwable $e) {
            Log::warning('CloudinaryService: delete failed', [
                'url'     => $url,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
