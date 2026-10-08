<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check and add columns safely to site_settings table
        Schema::table('site_settings', function (Blueprint $table) {
            $columns = $this->getExistingColumns();
            
            // Navbar & header
            if (!in_array('nav_brand_sub', $columns)) {
                $table->string('nav_brand_sub', 100)->nullable();
            }
            if (!in_array('nav_enroll_text', $columns)) {
                $table->string('nav_enroll_text', 60)->nullable();
            }
            if (!in_array('nav_portal_text', $columns)) {
                $table->string('nav_portal_text', 60)->nullable();
            }

            // Hero panel
            if (!in_array('hero_panel_title', $columns)) {
                $table->string('hero_panel_title', 150)->nullable();
            }
            if (!in_array('hero_panel_subtitle', $columns)) {
                $table->string('hero_panel_subtitle', 200)->nullable();
            }
            if (!in_array('hero_minis', $columns)) {
                $table->json('hero_minis')->nullable();
            }
            if (!in_array('hero_eyebrow', $columns)) {
                $table->string('hero_eyebrow', 100)->nullable();
            }

            // Hero CTA buttons
            if (!in_array('hero_cta_primary_text', $columns)) {
                $table->string('hero_cta_primary_text', 60)->nullable();
            }
            if (!in_array('hero_cta_secondary_text', $columns)) {
                $table->string('hero_cta_secondary_text', 60)->nullable();
            }

            // Why Choose Us section
            if (!in_array('why_eyebrow', $columns)) {
                $table->string('why_eyebrow', 80)->nullable();
            }
            if (!in_array('why_title', $columns)) {
                $table->string('why_title', 150)->nullable();
            }
            if (!in_array('why_subtitle', $columns)) {
                $table->string('why_subtitle', 300)->nullable();
            }
            if (!in_array('why_features', $columns)) {
                $table->json('why_features')->nullable();
            }

            // Programs section
            if (!in_array('programs_eyebrow', $columns)) {
                $table->string('programs_eyebrow', 80)->nullable();
            }
            if (!in_array('programs_title', $columns)) {
                $table->string('programs_title', 150)->nullable();
            }
            if (!in_array('programs_subtitle', $columns)) {
                $table->string('programs_subtitle', 300)->nullable();
            }

            // Testimonials section
            if (!in_array('testimonials_eyebrow', $columns)) {
                $table->string('testimonials_eyebrow', 80)->nullable();
            }
            if (!in_array('testimonials_title', $columns)) {
                $table->string('testimonials_title', 150)->nullable();
            }
            if (!in_array('testimonials_subtitle', $columns)) {
                $table->string('testimonials_subtitle', 300)->nullable();
            }

            // Blog section
            if (!in_array('blog_eyebrow', $columns)) {
                $table->string('blog_eyebrow', 80)->nullable();
            }
            if (!in_array('blog_title', $columns)) {
                $table->string('blog_title', 150)->nullable();
            }
            if (!in_array('blog_subtitle', $columns)) {
                $table->string('blog_subtitle', 300)->nullable();
            }

            // Events section
            if (!in_array('events_eyebrow', $columns)) {
                $table->string('events_eyebrow', 80)->nullable();
            }
            if (!in_array('events_title', $columns)) {
                $table->string('events_title', 150)->nullable();
            }
            if (!in_array('events_subtitle', $columns)) {
                $table->string('events_subtitle', 300)->nullable();
            }

            // CTA banner
            if (!in_array('cta_title', $columns)) {
                $table->string('cta_title', 150)->nullable();
            }
            if (!in_array('cta_subtitle', $columns)) {
                $table->string('cta_subtitle', 300)->nullable();
            }
            if (!in_array('cta_primary_text', $columns)) {
                $table->string('cta_primary_text', 60)->nullable();
            }
            if (!in_array('cta_secondary_text', $columns)) {
                $table->string('cta_secondary_text', 60)->nullable();
            }

            // About page
            if (!in_array('about_eyebrow', $columns)) {
                $table->string('about_eyebrow', 80)->nullable();
            }
            if (!in_array('about_mv_title', $columns)) {
                $table->string('about_mv_title', 150)->nullable();
            }
            if (!in_array('about_story_title', $columns)) {
                $table->string('about_story_title', 150)->nullable();
            }
            if (!in_array('about_story_text', $columns)) {
                $table->text('about_story_text')->nullable();
            }
            if (!in_array('about_timeline', $columns)) {
                $table->json('about_timeline')->nullable();
            }
            if (!in_array('about_cta_title', $columns)) {
                $table->string('about_cta_title', 150)->nullable();
            }
            if (!in_array('about_cta_subtitle', $columns)) {
                $table->string('about_cta_subtitle', 200)->nullable();
            }

            // Admissions page
            if (!in_array('admissions_eyebrow', $columns)) {
                $table->string('admissions_eyebrow', 80)->nullable();
            }
            if (!in_array('admissions_title', $columns)) {
                $table->string('admissions_title', 150)->nullable();
            }
            if (!in_array('admissions_subtitle', $columns)) {
                $table->string('admissions_subtitle', 300)->nullable();
            }
            if (!in_array('admissions_steps_eyebrow', $columns)) {
                $table->string('admissions_steps_eyebrow', 80)->nullable();
            }
            if (!in_array('admissions_steps', $columns)) {
                $table->json('admissions_steps')->nullable();
            }
            if (!in_array('admissions_requirements', $columns)) {
                $table->json('admissions_requirements')->nullable();
            }

            // Contact page
            if (!in_array('office_hours', $columns)) {
                $table->string('office_hours', 100)->nullable();
            }
            if (!in_array('contact_subjects', $columns)) {
                $table->json('contact_subjects')->nullable();
            }

            // Footer
            if (!in_array('footer_tagline', $columns)) {
                $table->string('footer_tagline', 300)->nullable();
            }
            if (!in_array('footer_programs', $columns)) {
                $table->json('footer_programs')->nullable();
            }
            if (!in_array('footer_privacy_url', $columns)) {
                $table->string('footer_privacy_url', 300)->nullable();
            }
            if (!in_array('footer_terms_url', $columns)) {
                $table->string('footer_terms_url', 300)->nullable();
            }
            if (!in_array('footer_copyright', $columns)) {
                $table->string('footer_copyright', 200)->nullable();
            }

            // Staff/Team section
            if (!in_array('staff_eyebrow', $columns)) {
                $table->string('staff_eyebrow', 80)->nullable();
            }
            if (!in_array('staff_title', $columns)) {
                $table->string('staff_title', 150)->nullable();
            }
            if (!in_array('staff_subtitle', $columns)) {
                $table->string('staff_subtitle', 300)->nullable();
            }

            // Theme colors (if not exists)
            if (!in_array('theme_colors', $columns)) {
                $table->json('theme_colors')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $columns = [
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
                'footer_tagline','footer_programs','footer_privacy_url',
                'footer_terms_url','footer_copyright',
                'staff_eyebrow','staff_title','staff_subtitle','theme_colors',
            ];
            
            $existing = $this->getExistingColumns();
            foreach ($columns as $column) {
                if (in_array($column, $existing)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function getExistingColumns(): array
    {
        return Schema::getColumnListing('site_settings');
    }
};