<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Website\SiteSetting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    // ── Colour math helpers (used in Blade templates) ─────────────────────

    /**
     * Darken a hex colour by $amount percent (0-100).
     * e.g. darken('#e9a422', 15) → slightly darker gold
     */
    public static function darken(string $hex, int $amount): string
    {
        return self::adjustBrightness($hex, -$amount);
    }

    /**
     * Lighten a hex colour by $amount percent (0-100).
     */
    public static function lighten(string $hex, int $amount): string
    {
        return self::adjustBrightness($hex, $amount);
    }

    private static function adjustBrightness(string $hex, int $steps): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        $r = max(0, min(255, hexdec(substr($hex, 0, 2)) + (int)round($steps * 2.55)));
        $g = max(0, min(255, hexdec(substr($hex, 2, 2)) + (int)round($steps * 2.55)));
        $b = max(0, min(255, hexdec(substr($hex, 4, 2)) + (int)round($steps * 2.55)));
        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    /**
     * Save the theme color palette chosen in admin settings.
     * Each color is a 6-digit hex value validated server-side.
     */
    public function save(Request $request)
    {
        $request->validate([
            'theme_primary'      => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_secondary'    => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_accent'       => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_text'         => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_bg'           => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_navBg'        => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_adminPrimary' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'theme_adminNav'     => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        $setting = SiteSetting::instance();
        $setting->update([
            'theme_colors' => [
                'primary'      => $request->theme_primary,
                'secondary'    => $request->theme_secondary,
                'accent'       => $request->theme_accent,
                'text'         => $request->theme_text,
                'bg'           => $request->theme_bg,
                'navBg'        => $request->theme_navBg,
                'adminPrimary' => $request->theme_adminPrimary,
                'adminNav'     => $request->theme_adminNav,
            ],
        ]);

        // Bust the view cache so the new CSS variables are picked up immediately
        try { \Artisan::call('view:clear'); } catch (\Throwable) {}

        return back()->with('success', 'Theme colours saved successfully. The new colours are now live across the entire system.');
    }

    /**
     * Reset the theme back to the default navy/gold palette.
     */
    public function reset()
    {
        $setting = SiteSetting::instance();
        $setting->update(['theme_colors' => SiteSetting::defaultTheme()]);

        try { \Artisan::call('view:clear'); } catch (\Throwable) {}

        return back()->with('success', 'Theme reset to the default navy & gold palette.');
    }
}
