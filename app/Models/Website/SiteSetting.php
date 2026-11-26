<?php

namespace App\Models\Website;

use App\Models\Concerns\TenantAwareConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * SiteSetting — the website CMS singleton row for a tenant.
 *
 * JSON columns (theme_colors, website_theme, etc.) are stored as JSON strings
 * by DB::table()->update() calls throughout the app. We intentionally keep
 * $casts empty and override syncOriginal() so that $original always holds
 * raw strings — this prevents array_diff_key() TypeError inside Eloquent's
 * getArrayableItems() / getDirty() when string vs array types are compared.
 */
class SiteSetting extends Model
{
    use TenantAwareConnection;

    protected $table = 'site_settings';

    private const JSON_COLS = [
        'stats', 'theme_colors', 'website_theme', 'hero_minis',
        'why_features', 'about_timeline', 'core_values', 'admissions_steps',
        'admissions_requirements', 'contact_subjects', 'footer_programs',
    ];

    protected $fillable = [
        'site_name','tagline','hero_title','hero_subtitle','stats',
        'about_text','mission','vision','history','core_values','admissions_info',
        'contact_email','contact_phone','address','map_embed_url',
        'facebook','twitter','instagram','youtube','logo_path','theme_colors','website_theme',
        'hero_bg_type','hero_bg_image','hero_bg_video_url',
        'hero_bg_solid_color','hero_gradient_from','hero_gradient_to',
        'hero_gradient_via','hero_gradient_angle','hero_overlay_opacity','hero_layout',
        'nav_brand_sub','nav_enroll_text','nav_portal_text',
        'hero_panel_title','hero_panel_subtitle','hero_minis','hero_eyebrow',
        'hero_cta_primary_text','hero_cta_secondary_text',
        'why_eyebrow','why_title','why_subtitle','why_features',
        'programs_eyebrow','programs_title','programs_subtitle',
        'testimonials_eyebrow','testimonials_title','testimonials_subtitle',
        'blog_eyebrow','blog_title','blog_subtitle',
        'events_eyebrow','events_title','events_subtitle',
        'cta_title','cta_subtitle','cta_primary_text','cta_secondary_text',
        'about_eyebrow','about_mv_title','about_story_title','about_story_text',
        'about_timeline','about_cta_title','about_cta_subtitle',
        'admissions_eyebrow','admissions_title','admissions_subtitle',
        'admissions_steps_eyebrow','admissions_steps','admissions_requirements',
        'office_hours','contact_subjects',
        'footer_site_name','footer_tagline','footer_programs','footer_nav_title',
        'footer_programs_title','footer_apply_text','footer_contact_title',
        'footer_address','footer_phone','footer_email','footer_office_hours',
        'footer_portal_text','footer_copyright',
        'footer_privacy_text','footer_privacy_url',
        'footer_terms_text','footer_terms_url',
        'footer_support_text','footer_support_url',
        'staff_eyebrow','staff_title','staff_subtitle',
        'academics_hero_title','academics_hero_subtitle',
        'academics_eyebrow','academics_section_title','academics_section_subtitle',
        'academics_empty_text','academics_apply_btn','academics_contact_btn',
        'academics_curriculum_label','academics_highlights_label','academics_schedule_label',
        'academics_cta_text','academics_cta_btn1_text','academics_cta_btn2_text','academics_cta_btn3_text',
        'report_card_orientation',
        'portal_intro_title','portal_intro_subtitle',
        'portal_student_title','portal_student_desc',
        'portal_teacher_title','portal_teacher_desc',
        'portal_staff_title','portal_staff_desc',
        'portal_parent_title','portal_parent_desc','portal_help_text',
        'about_hero_title','about_hero_subtitle','about_mv_subtitle',
        'core_values_title','core_values_subtitle',
        'admissions_hero_title','admissions_hero_subtitle',
        'admissions_req_title','admissions_req_subtitle',
        'nav_home_text','nav_about_text','nav_academics_text','nav_admissions_text',
        'nav_blog_text','nav_events_text','nav_gallery_text','nav_staff_text','nav_contact_text',
    ];

    // No $casts — JSON decoded manually to avoid Eloquent array_diff_key crash
    protected $casts = [];

    // ─────────────────────────────────────────────────────────────────────────
    // syncOriginal override
    // Eloquent's getArrayableItems() calls array_diff_key($values, array_flip($this->getHidden()))
    // which crashes when $values contains array entries (from cast JSON cols) but
    // $this->original contains raw strings for those same keys.
    // By storing $this->attributes directly (raw strings from DB, no casting),
    // both sides of any comparison are always the same type.
    // ─────────────────────────────────────────────────────────────────────────
    public function syncOriginal()
    {
        $this->original = $this->attributes;
        return $this;
    }

    // ── Safe JSON decoder ────────────────────────────────────────────────────
    public static function decodeJson(mixed $raw): array
    {
        if (is_array($raw)) return $raw;
        if (!is_string($raw) || $raw === '') return [];
        $v = json_decode($raw, true);
        if (is_string($v)) $v = json_decode($v, true);
        return is_array($v) ? $v : [];
    }

    // ── Defaults ─────────────────────────────────────────────────────────────
    public static function defaultTheme(): array
    {
        return [
            'primary'      => '#e9a422',
            'secondary'    => '#0a1f44',
            'accent'       => '#0891b2',
            'text'         => '#3a3830',
            'bg'           => '#ffffff',
            'navBg'        => '#0a1f44',
            'adminPrimary' => '#D4A017',
            'adminNav'     => '#0B1121',
        ];
    }

    public static function defaultWebsiteTheme(): array
    {
        return [
            'primary'   => '#e9a422',
            'secondary' => '#0a1f44',
            'accent'    => '#0891b2',
            'text'      => '#3a3830',
            'bg'        => '#ffffff',
            'navBg'     => '#0a1f44',
        ];
    }

    // ── Singleton ─────────────────────────────────────────────────────────────
    public static function instance(): self
    {
        try {
            $row = static::first();
            if ($row) return $row;
        } catch (\Throwable $e) {
            // DB not reachable or hydration error — return empty model with defaults
            return new static();
        }

        try {
            \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('site_settings')
                ->insertOrIgnore([
                    'site_name'    => 'School',
                    'tagline'      => '',
                    'theme_colors' => json_encode(self::defaultTheme()),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
        } catch (\Throwable $e) {}

        try {
            return static::first() ?? new static();
        } catch (\Throwable $e) {
            return new static();
        }
    }

    // ── getAttribute: JSON cols decoded, missing cols get fallbacks ───────────
    public function getAttribute($key)
    {
        if (in_array($key, self::JSON_COLS, true)) {
            return self::decodeJson($this->attributes[$key] ?? null);
        }
        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }
        $fallback = $this->fallback($key);
        return $fallback !== null ? $fallback : parent::getAttribute($key);
    }

    private function fallback(string $key): mixed
    {
        static $f = [
            'nav_brand_sub'              => 'Education & Excellence',
            'nav_enroll_text'            => 'Enroll Now',
            'hero_panel_title'           => 'World-Class Education',
            'hero_panel_subtitle'        => 'Excellence in learning',
            'hero_cta_primary_text'      => 'Apply Now',
            'hero_cta_secondary_text'    => 'Learn More',
            'why_eyebrow'                => 'Why Choose Us',
            'why_title'                  => 'Where Every Child Comes First',
            'why_subtitle'               => 'We provide a safe, stimulating environment.',
            'programs_eyebrow'           => 'Our Programs',
            'programs_title'             => 'Programs Designed for Every Stage',
            'programs_subtitle'          => '',
            'testimonials_eyebrow'       => 'Parent Reviews',
            'testimonials_title'         => 'What Parents Say',
            'testimonials_subtitle'      => '',
            'blog_eyebrow'               => 'Latest News',
            'blog_title'                 => 'From Our Blog',
            'blog_subtitle'              => '',
            'events_eyebrow'             => 'School Calendar',
            'events_title'               => 'Upcoming Events',
            'events_subtitle'            => '',
            'cta_title'                  => 'Ready to Join Our Family?',
            'cta_subtitle'               => 'Applications are open.',
            'about_story_title'          => 'Our Story',
            'admissions_title'           => 'How to Apply',
            'admissions_subtitle'        => 'Simple enrollment process.',
            'office_hours'               => 'Mon – Fri: 7:00 AM – 5:00 PM',
            'staff_eyebrow'              => 'Meet the Team',
            'staff_title'                => 'Our Dedicated Educators',
            'staff_subtitle'             => 'Passionate professionals.',
            'academics_hero_title'       => 'Our <em>Academic Programs</em>',
            'academics_hero_subtitle'    => 'Designed for academic excellence.',
            'academics_eyebrow'          => 'What We Offer',
            'academics_section_title'    => 'Programs for Every Learner',
            'academics_section_subtitle' => 'Thoughtfully designed programs.',
            'academics_empty_text'       => 'Our programs are being updated. Check back soon!',
            'academics_apply_btn'        => 'Apply Now',
            'academics_contact_btn'      => 'Contact Us',
            'academics_curriculum_label' => 'Curriculum Overview',
            'academics_highlights_label' => 'Program Highlights',
            'academics_schedule_label'   => 'Schedule',
            'academics_cta_text'         => 'Ready to enroll? Spaces fill up fast.',
            'academics_cta_btn1_text'    => 'Apply Now',
            'academics_cta_btn2_text'    => 'Admissions Info',
            'academics_cta_btn3_text'    => 'Contact Us',
            'footer_site_name'           => null,
            'footer_tagline'             => '',
            'footer_nav_title'           => 'Navigation',
            'footer_programs_title'      => 'Programs',
            'footer_apply_text'          => 'Apply Online',
            'footer_contact_title'       => 'Contact',
            'footer_office_hours'        => 'Mon–Fri: 7:00 AM – 5:00 PM',
            'footer_portal_text'         => 'Staff Portals',
            'footer_copyright'           => 'All rights reserved.',
            'footer_privacy_text'        => 'Privacy Policy',
            'footer_terms_text'          => 'Terms of Use',
            'footer_support_text'        => 'Support',
            'portal_intro_title'         => 'Access Your Portals',
            'portal_intro_subtitle'      => 'Choose your portal below to log in',
            'portal_student_title'       => 'Student Portal',
            'portal_student_desc'        => 'Check results, attendance and fees',
            'portal_teacher_title'       => 'Teacher Portal',
            'portal_teacher_desc'        => 'Mark attendance and enter scores',
            'portal_staff_title'         => 'Staff Portal',
            'portal_staff_desc'          => 'For accountants, secretaries and heads',
            'portal_parent_title'        => 'Parent Portal',
            'portal_parent_desc'         => 'Monitor your child\'s progress',
            'portal_help_text'           => 'Need help? Contact your school administrator.',
            'about_hero_title'           => 'About Our School',
            'about_hero_subtitle'        => 'Our history, mission and values',
            'about_mv_subtitle'          => 'Our guiding principles',
            'core_values_title'          => 'Our Core Values',
            'core_values_subtitle'       => 'The principles that guide everything we do',
            'admissions_hero_title'      => 'Admissions',
            'admissions_hero_subtitle'   => 'Begin your journey with us',
            'admissions_req_title'       => 'Documents Needed',
            'admissions_req_subtitle'    => 'Please prepare the following documents',
            'nav_home_text'              => 'Home',
            'nav_about_text'             => 'About',
            'nav_academics_text'         => 'Academics',
            'nav_admissions_text'        => 'Admissions',
            'nav_blog_text'              => 'Blog',
            'nav_events_text'            => 'Events',
            'nav_gallery_text'           => 'Gallery',
            'nav_staff_text'             => 'Staff',
            'nav_contact_text'           => 'Contact',
        ];
        return array_key_exists($key, $f) ? $f[$key] : null;
    }

    // ── Theme accessors ───────────────────────────────────────────────────────
    public function getThemeAttribute(): array
    {
        return array_merge(
            self::defaultTheme(),
            self::decodeJson($this->attributes['theme_colors'] ?? null)
        );
    }

    public function getWebsiteThemeAttribute(): array
    {
        $saved = self::decodeJson($this->attributes['website_theme'] ?? null);
        if (!empty($saved)) {
            return array_merge(self::defaultWebsiteTheme(), $saved);
        }
        $legacy = self::decodeJson($this->attributes['theme_colors'] ?? null);
        if (!empty($legacy)) {
            $subset = array_intersect_key($legacy, array_flip(['primary','secondary','accent','text','bg','navBg']));
            if (!empty($subset)) return array_merge(self::defaultWebsiteTheme(), $subset);
        }
        return self::defaultWebsiteTheme();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) return null;
        if (filter_var($this->logo_path, FILTER_VALIDATE_URL)) return $this->logo_path;
        return Storage::disk('public')->url($this->logo_path);
    }
}
