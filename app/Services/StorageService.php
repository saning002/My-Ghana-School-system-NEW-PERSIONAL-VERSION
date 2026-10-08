<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Handles persistent file storage for documents (PDFs, Word files, etc.)
 *
 * Upload priority:
 *  1. Backblaze B2  (if B2_KEY_ID + B2_BUCKET env vars are set)
 *  2. AWS S3        (if AWS_ACCESS_KEY_ID + AWS_BUCKET env vars are set)
 *  3. Cloudinary    (if CLOUDINARY_URL is set — PDFs/docs uploaded as resource_type=raw)
 *  4. public disk   (local fallback — files are ephemeral on Render, use only in dev)
 *
 * Cloudinary is used for document storage when no B2/S3 bucket is configured.
 * Files uploaded to Cloudinary are stored as 'raw' resource type so they can
 * be downloaded/viewed as PDFs (not converted to images).
 */
class StorageService
{
    // ── Disk detection ────────────────────────────────────────────────────────

    /**
     * Detect which persistent disk is configured.
     * Returns 'b2', 's3', or 'public' (fallback).
     * NOTE: Cloudinary is handled separately in uploadDocument() — it returns
     *       a full HTTPS URL rather than a disk path.
     */
    public static function persistentDisk(): string
    {
        if (self::b2Configured()) return 'b2';
        if (self::s3Configured()) return 's3';
        return 'public';
    }

    public static function b2Configured(): bool
    {
        $key    = getenv('B2_KEY_ID') ?: env('B2_KEY_ID', '');
        $bucket = getenv('B2_BUCKET') ?: env('B2_BUCKET', '');
        return ! empty($key) && ! empty($bucket);
    }

    public static function s3Configured(): bool
    {
        $key    = getenv('AWS_ACCESS_KEY_ID') ?: env('AWS_ACCESS_KEY_ID', '');
        $bucket = getenv('AWS_BUCKET')        ?: env('AWS_BUCKET', '');
        return ! empty($key) && ! empty($bucket);
    }

    // ── Upload ────────────────────────────────────────────────────────────────

    /**
     * Upload a document (PDF, Word, etc.) to the best available persistent storage.
     *
     * Returns:
     *  - A full HTTPS URL  when stored on Cloudinary (starts with https://)
     *  - A relative path   when stored on B2, S3, or public disk (e.g. "exam-questions/uuid.pdf")
     *  - null              on complete failure
     *
     * The caller stores whichever value is returned in the DB column.
     * Controllers must handle both URL and path values when serving files back.
     */
    public static function uploadDocument(UploadedFile $file, string $folder = 'documents'): ?string
    {
        $ext      = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . $ext;
        $path     = $folder . '/' . $filename;

        // 1. Try B2 first (most persistent, cheapest)
        if (self::b2Configured()) {
            try {
                $result = Storage::disk('b2')->putFileAs($folder, $file, $filename, 'public');
                if ($result) {
                    Log::info("StorageService: document uploaded to B2", ['path' => $path]);
                    return $path;
                }
            } catch (\Throwable $e) {
                Log::error("StorageService: B2 upload failed — " . $e->getMessage());
            }
        }

        // 2. Try S3
        if (self::s3Configured()) {
            try {
                $result = Storage::disk('s3')->putFileAs($folder, $file, $filename, 'public');
                if ($result) {
                    Log::info("StorageService: document uploaded to S3", ['path' => $path]);
                    return $path;
                }
            } catch (\Throwable $e) {
                Log::error("StorageService: S3 upload failed — " . $e->getMessage());
            }
        }

        // 3. Try Cloudinary (when no B2/S3 configured — common on Render free tier)
        //    PDFs/docs must use resource_type 'raw' so Cloudinary serves the actual
        //    file bytes instead of converting to an image preview.
        if (CloudinaryService::isConfigured()) {
            $cloudinaryFolder = 'school-documents/' . $folder;
            $url = CloudinaryService::upload($file, $cloudinaryFolder, 'raw');
            if ($url) {
                Log::info("StorageService: document uploaded to Cloudinary (raw)", [
                    'url'    => $url,
                    'folder' => $cloudinaryFolder,
                ]);
                // Return the full URL — controllers detect URLs via filter_var(FILTER_VALIDATE_URL)
                return $url;
            }
            Log::warning("StorageService: Cloudinary upload returned null, falling back to public disk");
        }

        // 4. Final fallback — local public disk (ephemeral on Render, fine for dev/Coolify)
        try {
            $result = Storage::disk('public')->putFileAs($folder, $file, $filename, 'public');
            if ($result) {
                Log::warning("StorageService: document stored on public disk (ephemeral — will be lost on redeploy)", [
                    'path' => $path,
                ]);
                return $path;
            }
        } catch (\Throwable $e) {
            Log::error("StorageService: public disk fallback also failed — " . $e->getMessage());
        }

        return null;
    }

    // ── URL generation ────────────────────────────────────────────────────────

    /**
     * Get a URL for a stored document path.
     * Only used for disk-based storage (B2/S3/public).
     * Cloudinary documents are stored as full URLs already.
     */
    public static function url(string $path, int $expiresInMinutes = 10): string
    {
        // If it's already a full URL (Cloudinary), return as-is
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk = self::persistentDisk();

        try {
            if ($disk !== 'public') {
                return Storage::disk($disk)->temporaryUrl($path, now()->addMinutes($expiresInMinutes));
            }
            return Storage::disk($disk)->url($path);
        } catch (\Throwable $e) {
            try {
                return Storage::disk($disk)->url($path);
            } catch (\Throwable $e2) {
                try {
                    return Storage::disk('public')->url($path);
                } catch (\Throwable $e3) {
                    return '/storage/' . $path;
                }
            }
        }
    }

    // ── Serve / stream ────────────────────────────────────────────────────────

    /**
     * Get the absolute local filesystem path for serving a file inline.
     *
     * For cloud disks (B2/S3): streams content to a temp file.
     * For public disk: returns the direct filesystem path if the file exists.
     *
     * Returns null if the file cannot be found on any disk.
     */
    public static function localPath(string $path): ?string
    {
        // Full URLs (Cloudinary) must go through proxyFromUrl() — not this method
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return self::proxyFromUrl($path);
        }

        $disk = self::persistentDisk();

        // Public disk — direct path check
        if ($disk === 'public') {
            $localPath = Storage::disk('public')->path($path);
            if (file_exists($localPath)) {
                return $localPath;
            }
            // File might not exist on ephemeral disk (e.g. after a redeploy on Render)
            Log::warning("StorageService::localPath: file not found on public disk", ['path' => $path]);
            return null;
        }

        // Cloud disk (B2/S3) — stream to temp file
        try {
            $content = Storage::disk($disk)->get($path);
            if ($content) {
                $ext = pathinfo($path, PATHINFO_EXTENSION) ?: 'pdf';
                $tmp = tempnam(sys_get_temp_dir(), 'doc_') . '.' . $ext;
                file_put_contents($tmp, $content);
                return $tmp;
            }
        } catch (\Throwable $e) {
            Log::warning("StorageService::localPath ({$disk}) failed for {$path}: " . $e->getMessage());
        }

        // Fallback: try public disk (file may have been saved there during an earlier fallback)
        $publicPath = Storage::disk('public')->path($path);
        if (file_exists($publicPath)) {
            Log::info("StorageService::localPath: found on public disk fallback", ['path' => $path]);
            return $publicPath;
        }

        return null;
    }

    // ── Proxy ─────────────────────────────────────────────────────────────────

    /**
     * Proxy/stream a remote HTTPS file URL through the app.
     * Handles Cloudinary (raw + image/upload), S3, B2 presigned URLs, or any HTTPS URL.
     * The remote URL is NEVER exposed to the browser — always streamed server-side.
     *
     * Returns the path to a local temp file containing the file content, or null on failure.
     */
    public static function proxyFromUrl(string $url, string $contentType = 'application/pdf'): ?string
    {
        // Fix Cloudinary PDF URLs uploaded under old resource_type=image:
        // /image/upload/ delivers a rendered image preview, not the actual PDF bytes.
        // /raw/upload/  delivers the actual file — what we need.
        if (str_contains($url, 'cloudinary.com')) {
            $url = self::fixCloudinaryPdfUrl($url);
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS      => 5,
                CURLOPT_TIMEOUT        => 60,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT      => 'Mozilla/5.0 (compatible; SchoolApp/1.0)',
                CURLOPT_HTTPHEADER     => [
                    'Accept: application/pdf,application/octet-stream,*/*',
                ],
            ]);
            $content          = curl_exec($ch);
            $httpCode         = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseType     = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
            $error            = curl_error($ch);
            curl_close($ch);

            Log::info("StorageService::proxyFromUrl", [
                'url'          => $url,
                'http_code'    => $httpCode,
                'content_type' => $responseType,
                'content_size' => strlen($content ?? ''),
            ]);

            if ($content && $httpCode === 200) {
                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'pdf';
                if (empty($ext) || strlen($ext) > 5) $ext = 'pdf';
                $tmp = tempnam(sys_get_temp_dir(), 'prx_') . '.' . $ext;
                file_put_contents($tmp, $content);
                return $tmp;
            }

            Log::warning("StorageService::proxyFromUrl: HTTP {$httpCode} for {$url}. cURL error: {$error}");
        } catch (\Throwable $e) {
            Log::warning("StorageService::proxyFromUrl exception for {$url}: " . $e->getMessage());
        }

        return null;
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    /**
     * Delete a document from whatever storage it was saved to.
     * Handles both full Cloudinary URLs and relative disk paths.
     */
    public static function delete(string $path): void
    {
        // Cloudinary URL — use CloudinaryService to delete
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            if (str_contains($path, 'cloudinary.com')) {
                CloudinaryService::delete($path);
            }
            // Other external URLs: nothing to delete server-side
            return;
        }

        // Disk-based path
        $disk = self::persistentDisk();
        try {
            Storage::disk($disk)->delete($path);
        } catch (\Throwable $e) {
            Log::warning("StorageService::delete ({$disk}) failed for {$path}: " . $e->getMessage());
        }

        // Also try public disk in case it was stored there as fallback
        if ($disk !== 'public') {
            try {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            } catch (\Throwable $e) {}
        }
    }

    // ── Exists check ──────────────────────────────────────────────────────────

    public static function exists(string $path): bool
    {
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            // Assume Cloudinary URLs exist (checking requires an API call)
            return true;
        }

        $disk = self::persistentDisk();
        try {
            if (Storage::disk($disk)->exists($path)) return true;
        } catch (\Throwable $e) {}

        if ($disk !== 'public') {
            try {
                return Storage::disk('public')->exists($path);
            } catch (\Throwable $e) {}
        }

        return false;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /**
     * Convert a Cloudinary image/upload URL to a raw file delivery URL.
     *
     * When a PDF was accidentally uploaded as resource_type=image, Cloudinary
     * stores it under /image/upload/ and serves an image preview instead of
     * the actual PDF bytes.  Changing to /raw/upload/ forces actual file delivery.
     */
    private static function fixCloudinaryPdfUrl(string $url): string
    {
        if (str_contains($url, '/raw/upload/')) {
            return $url; // Already correct
        }

        // Replace /image/upload/ or /video/upload/ with /raw/upload/
        $fixed = preg_replace('#/(image|video)/upload/#', '/raw/upload/', $url);

        // Strip any Cloudinary transformation parameters from raw URLs
        // (transformations like w_300,h_400 are not valid for raw resource type)
        $fixed = preg_replace('#/raw/upload/[a-z_]+[^/]*,([^/]+)/#', '/raw/upload/', $fixed);

        if ($fixed !== $url) {
            Log::info("StorageService::fixCloudinaryPdfUrl", ['from' => $url, 'to' => $fixed]);
        }

        return $fixed;
    }
}
