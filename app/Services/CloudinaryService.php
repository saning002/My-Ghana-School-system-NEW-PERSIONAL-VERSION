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
            $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);

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
            $cloudinary = new \Cloudinary\Cloudinary($cloudinaryUrl);

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
