<?php

namespace App\Models\Website;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'site_name','tagline','hero_title','hero_subtitle','stats',
        'about_text','mission','vision','history','core_values','admissions_info',
        'contact_email','contact_phone','address','map_embed_url',
        'facebook','twitter','instagram','youtube','logo_path','theme_colors','website_theme',
        // Hero visual options
        'hero_bg_type','hero_bg_image','hero_bg_video_url',
        'hero_bg_solid_color','hero_gradient_from','hero_gradient_to',
        'hero_gradient_via','hero_gradient_angle','hero_overlay_opacity',
        'hero_layout',
        // Navbar
        'nav_brand_sub','nav_enroll_text','nav_portal_text',
        // Hero panel
        'hero_panel_title','hero_panel_subtitle','hero_minis','hero_eyebrow',
        'hero_cta_primary_text','hero_cta_secondary_text',
        // Home sections
        'why_eyebrow','why_title','why_subtitle','why_features',
        'programs_eyebrow','programs_title','programs_subtitle',
        'testimonials_eyebrow','testimonials_title','testimonials_subtitle',
        'blog_eyebrow','blog_title','blog_subtitle',
        'events_eyebrow','events_title','events_subtitle',
        'cta_title','cta_subtitle','cta_primary_text','cta_secondary_text',
        // About page
        'about_eyebrow','about_mv_title','about_story_title','about_story_text',
        'about_timeline','about_cta_title','about_cta_subtitle',
        // Admissions page
        'admissions_eyebrow','admissions_title','admissions_subtitle',
        'admissions_steps_eyebrow','admissions_steps','admissions_requirements',
        // Contact
        'office_hours','contact_subjects',
        // Footer
        'footer_site_name','footer_tagline','footer_programs','footer_nav_title',
        'footer_programs_title','footer_apply_text','footer_contact_title',
        'footer_address','footer_phone','footer_email','footer_office_hours',
        'footer_portal_text','footer_copyright',
        'footer_privacy_text','footer_privacy_url',
        'footer_terms_text','footer_terms_url',
        'footer_support_text','footer_support_url',
        // Staff section
        'staff_eyebrow','staff_title','staff_subtitle',
        // Academics page
        'academics_hero_title','academics_hero_subtitle',
        'academics_eyebrow','academics_section_title','academics_section_subtitle',
        'academics_empty_text','academics_apply_btn','academics_contact_btn',
        'academics_curriculum_label','academics_highlights_label','academics_schedule_label',
        'academics_cta_text','academics_cta_btn1_text','academics_cta_btn2_text','academics_cta_btn3_text',
        // Report card
        'report_card_orientation',
    ];

    protected $casts = [
        'stats'                    => 'array',
        'theme_colors'             => 'array',
        'website_theme'            => 'array',
        'hero_minis'               => 'array',
        'why_features'             => 'array',
        'about_timeline'           => 'array',
        'core_values'              => 'array',
        'admissions_steps'         => 'array',
        'admissions_requirements'  => 'array',
        'contact_subjects'         => 'array',
        'footer_programs'          => 'array',
    ];

    /** Default theme — navy/gold used across both the school system and website. */
    public static function defaultTheme(): array
    {
        return [
            'primary'   => '#e9a422',   // gold  — buttons, accents, highlights
            'secondary' => '#0a1f44',   // navy  — navbar, hero backgrounds
            'accent'    => '#0891b2',   // teal  — links, active states, student portal
            'text'      => '#3a3830',   // warm dark grey — body text
            'bg'        => '#ffffff',   // page background
            'navBg'     => '#0a1f44',   // navbar / sidebar background
            // Admin panel palette
            'adminPrimary' => '#D4A017', // gold used in admin sidebar & buttons
            'adminNav'     => '#0B1121', // very dark navy used in admin sidebar
        ];
    }

    /** Default website-only theme — independent from the admin panel palette. */
    public static function defaultWebsiteTheme(): array
    {
        return [
            'primary'   => '#e9a422',   // gold  — buttons, links, highlights
            'secondary' => '#0a1f44',   // navy  — headings, hero backgrounds
            'accent'    => '#0891b2',   // teal  — active states, badges
            'text'      => '#3a3830',   // body text
            'bg'        => '#ffffff',   // page background
            'navBg'     => '#0a1f44',   // website navbar background
        ];
    }

    /** Get the singleton row, creating defaults if missing. */
    public static function instance(): self
    {
        return static::firstOrCreate([], [
            'site_name'    => 'Kingdom Ministerial University College',
            'tagline'      => 'Raising Kingdom Leaders for the Nations',
            'hero_title'   => 'Raising Kingdom Leaders for a',
            'hero_subtitle'=> 'A premier ministerial institution dedicated to equipping men and women with the knowledge, character, and skills for effective Kingdom ministry.',
            'theme_colors' => json_encode(self::defaultTheme()),
        ]);
    }

    /**
     * Get an attribute with a fallback if the column doesn't exist
     */
    public function getAttribute($key)
    {
        // If the attribute exists in the database, return it
        if (array_key_exists($key, $this->attributes)) {
            return parent::getAttribute($key);
        }

        // Provide fallback values for fields that might not exist yet in DB
        $fallbacks = [
            'nav_brand_sub'              => 'Education & Excellence',
            'nav_enroll_text'            => 'Enroll Now',
            'hero_panel_title'           => 'World-Class Early Education',
            'hero_panel_subtitle'        => 'Holistic development for ages 1–6',
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
            'cta_subtitle'               => 'Applications are open. Spots are limited.',
            'about_story_title'          => 'A Legacy of Nurturing Young Minds',
            'admissions_title'           => 'How to Apply in 4 Steps',
            'admissions_subtitle'        => 'Quick and easy enrollment process.',
            'office_hours'               => 'Mon – Fri: 7:00 AM – 5:00 PM',
            'staff_eyebrow'              => 'Meet the Team',
            'staff_title'                => 'Our Dedicated Educators',
            'staff_subtitle'             => 'Passionate professionals committed to every child\'s success.',
            // Academics page
            'academics_hero_title'       => 'Our <em>Academic Programs</em>',
            'academics_hero_subtitle'    => 'Carefully designed programs rooted in academic excellence and Kingdom values.',
            'academics_eyebrow'          => 'What We Offer',
            'academics_section_title'    => 'Programs for Every Learner',
            'academics_section_subtitle' => 'Every program is thoughtfully designed to develop knowledge, character, and skills.',
            'academics_empty_text'       => 'Our programs are being updated. Check back soon!',
            'academics_apply_btn'        => 'Apply Now',
            'academics_contact_btn'      => 'Contact Us',
            'academics_curriculum_label' => 'Curriculum Overview',
            'academics_highlights_label' => 'Program Highlights',
            'academics_schedule_label'   => 'Schedule',
            'academics_cta_text'         => 'Ready to enroll your child? Spaces fill up fast.',
            'academics_cta_btn1_text'    => 'Apply Now',
            'academics_cta_btn2_text'    => 'Admissions Info',
            'academics_cta_btn3_text'    => 'Contact Us',
            // Footer
            'footer_site_name'           => null,
            'footer_tagline'             => 'A nurturing environment where every child is celebrated, respected, and inspired to reach their full potential.',
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
        ];

        return $fallbacks[$key] ?? parent::getAttribute($key);
    }

    /** Resolved admin theme — falls back to defaults for any missing key. */
    public function getThemeAttribute(): array
    {
        $saved = $this->theme_colors ?? [];
        return array_merge(self::defaultTheme(), is_array($saved) ? $saved : []);
    }

    /**
     * Resolved website-only theme.
     * Falls back to website_theme column, then to theme_colors (legacy),
     * then to defaultWebsiteTheme(). Completely independent from the admin panel.
     */
    public function getWebsiteThemeAttribute(): array
    {
        // Use dedicated website_theme column if it has data
        $saved = $this->website_theme ?? [];
        if (!empty($saved) && is_array($saved)) {
            return array_merge(self::defaultWebsiteTheme(), $saved);
        }

        // Fall back to the website-relevant keys from theme_colors (legacy)
        $legacy = $this->theme_colors ?? [];
        if (!empty($legacy) && is_array($legacy)) {
            $websiteKeys = ['primary','secondary','accent','text','bg','navBg'];
            $legacyWebsite = array_intersect_key($legacy, array_flip($websiteKeys));
            if (!empty($legacyWebsite)) {
                return array_merge(self::defaultWebsiteTheme(), $legacyWebsite);
            }
        }

        return self::defaultWebsiteTheme();
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo_path) return null;
        if (filter_var($this->logo_path, FILTER_VALIDATE_URL)) return $this->logo_path;
        return Storage::disk('public')->url($this->logo_path);
    }
}
