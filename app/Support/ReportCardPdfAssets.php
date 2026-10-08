<?php

namespace App\Support;

use App\Models\Student;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

/**
 * Embeds logo and student photo as base64 data URIs for DomPDF.
 * DomPDF has isRemoteEnabled=false so all images must be embedded.
 */
class ReportCardPdfAssets
{
    public static function logoBase64(): ?string
    {
        // 1. First priority: admin's custom logo from Settings table (set via admin settings)
        try {
            if (Schema::hasTable('settings')) {
                $customLogo = Setting::get('site_logo_url');
                if ($customLogo) {
                    $result = self::resolveLogoPath($customLogo);
                    if ($result) return $result;
                }
            }
        } catch (\Throwable $e) {
            // Fall through if Settings table/column missing
        }

        // 2. Second priority: Website SiteSetting logo (set via website settings)
        try {
            if (Schema::hasTable('site_settings')) {
                $siteSetting = \App\Models\Website\SiteSetting::instance();
                $siteLogoPath = $siteSetting->logo_path ?? null;
                if ($siteLogoPath) {
                    $result = self::resolveLogoPath($siteLogoPath);
                    if ($result) return $result;
                }
                // Also check logo_url property (computed attribute)
                $logoUrl = $siteSetting->logo_url ?? null;
                if ($logoUrl) {
                    $result = self::resolveLogoPath($logoUrl);
                    if ($result) return $result;
                }
            }
        } catch (\Throwable $e) {
            // Fall through
        }

        // 3. Final fallback: default public logos (removed per user request - no hardcoded logos)
        return null;
    }

    /**
     * Resolve a logo from various path formats (URL, storage relative, public relative)
     * to a base64 data URI.
     */
    private static function resolveLogoPath(string $raw): ?string
    {
        // External URL — download and embed
        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            try {
                $context = stream_context_create([
                    'http' => ['timeout' => 5, 'ignore_errors' => true],
                    'ssl'  => ['verify_peer' => false, 'verify_peer_name' => false],
                ]);
                $content = @file_get_contents($raw, false, $context);
                if ($content !== false && strlen($content) > 0) {
                    $finfo = new \finfo(FILEINFO_MIME_TYPE);
                    $mime  = $finfo->buffer($content) ?: 'image/png';
                    return 'data:' . $mime . ';base64,' . base64_encode($content);
                }
            } catch (\Throwable $e) { /* fall through */ }
            return null;
        }

        // Normalize path: strip known prefixes
        $path = str_replace('\\', '/', trim($raw));
        $diskKey = $path;
        foreach (['/storage/', 'storage/', 'public/', '/public/'] as $prefix) {
            if (str_starts_with($diskKey, $prefix)) {
                $diskKey = ltrim(substr($diskKey, strlen($prefix)), '/');
                break;
            }
        }

        // Try Storage::disk('public') — main persistent mount
        try {
            $candidate = Storage::disk('public')->path($diskKey);
            if (File::exists($candidate) && is_readable($candidate)) {
                return self::encodeFile($candidate);
            }
        } catch (\Throwable $e) { /* continue */ }

        // Try public/uploads/
        $candidate = public_path('uploads/' . $diskKey);
        if (File::exists($candidate) && is_readable($candidate)) {
            return self::encodeFile($candidate);
        }

        // Try direct public path
        $candidate = public_path($path);
        if (File::exists($candidate) && is_readable($candidate)) {
            return self::encodeFile($candidate);
        }

        return null;
    }

    private static function encodeFile(string $absPath): ?string
    {
        $content = @file_get_contents($absPath);
        if ($content === false || strlen($content) === 0) return null;
        $mime = @mime_content_type($absPath) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode($content);
    }

    public static function studentPhotoBase64(Student $student): ?string
    {
        if (! $student->photo || ! is_string($student->photo)) {
            return null;
        }

        $raw = $student->photo;

        // External URL — fetch the image content directly
        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            try {
                $content = @file_get_contents($raw);
                if ($content === false || strlen($content) === 0) {
                    return null;
                }
                // Detect mime from content
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime  = $finfo->buffer($content) ?: 'image/jpeg';
                return 'data:' . $mime . ';base64,' . base64_encode($content);
            } catch (\Throwable $e) {
                return null;
            }
        }

        $abs = self::resolveAbsPath($raw);

        if (! $abs || ! File::exists($abs) || ! is_readable($abs)) {
            return null;
        }

        $mime = @mime_content_type($abs) ?: 'image/jpeg';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($abs));
    }

    /**
     * Resolve a stored photo path value to an absolute filesystem path.
     * Handles all path formats: relative disk path, /storage/... URL path,
     * storage/... prefix, public/... prefix, uploads/... prefix.
     */
    private static function resolveAbsPath(string $raw): ?string
    {
        $path = str_replace('\\', '/', trim($raw));

        // Already a full URL — can't embed without remote access
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return null;
        }

        // Normalise: strip known prefixes to get the disk-relative key
        $diskKey = $path;
        foreach (['/storage/', 'storage/', 'public/'] as $prefix) {
            if (str_starts_with($diskKey, $prefix)) {
                $diskKey = ltrim(substr($diskKey, strlen($prefix)), '/');
                break;
            }
        }

        // 1. Try Storage::disk('public') — the persistent mount
        $candidate = Storage::disk('public')->path($diskKey);
        if (File::exists($candidate) && is_readable($candidate)) {
            return $candidate;
        }

        // 2. Try public/uploads/
        $candidate = public_path('uploads/' . $diskKey);
        if (File::exists($candidate) && is_readable($candidate)) {
            return $candidate;
        }

        // 3. Try direct public path (for paths like uploads/students/photos/...)
        $candidate = public_path($path);
        if (File::exists($candidate) && is_readable($candidate)) {
            return $candidate;
        }

        return null;
    }
}
