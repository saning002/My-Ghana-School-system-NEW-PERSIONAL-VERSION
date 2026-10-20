<?php

/**
 * Website URL helper — generates slug-aware URLs for the public school website.
 *
 * If the current URL contains /school/{slug}/website, returns slug-based URLs
 * (e.g. /school/saning-academy/website/about) so navigation stays on the
 * permanent slug URL. Otherwise falls back to the session-based /website/* routes.
 *
 * Usage in Blade: <a href="{{ wu('about') }}">About</a>
 */
if (!function_exists('wu')) {
    function wu(string $page = '', string $extra = ''): string
    {
        $path = request()->path();
        $urlSlug = null;

        // Detect slug from URL: /school/{slug}/website/...
        if (preg_match('#^school/([^/]+)/website#', $path, $m)) {
            $urlSlug = $m[1];
        }

        if ($urlSlug) {
            $base = url('/school/' . $urlSlug . '/website');
            return $page === '' ? $base : $base . '/' . $page . $extra;
        }

        // Fallback to named routes
        $map = [
            ''          => 'website.home',
            'home'      => 'website.home',
            'about'     => 'website.about',
            'academics' => 'website.academics',
            'admissions'=> 'website.admissions',
            'gallery'   => 'website.gallery',
            'events'    => 'website.events',
            'blog'      => 'website.blog',
            'staff'     => 'website.staff',
            'contact'   => 'website.contact',
            'portal'    => 'website.portal',
            'apply'     => 'website.apply',
        ];

        $routeName = $map[$page] ?? 'website.home';
        try {
            return route($routeName);
        } catch (\Throwable $e) {
            return url('/website');
        }
    }
}

/**
 * Resolve an image path to a URL — handles both Cloudinary URLs and local paths.
 * If the path is already a full URL (Cloudinary), returns it as-is.
 * Otherwise, uses the public disk.
 */
if (!function_exists('img_url')) {
    function img_url(?string $path): ?string
    {
        if (!$path) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        try {
            return \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        } catch (\Throwable $e) {
            return null;
        }
    }
}
