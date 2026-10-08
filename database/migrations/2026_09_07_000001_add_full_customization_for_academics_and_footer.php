<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Academics page customization fields
            if (! Schema::hasColumn('site_settings', 'academics_curriculum_label')) {
                $table->string('academics_curriculum_label', 100)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_highlights_label')) {
                $table->string('academics_highlights_label', 100)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_schedule_label')) {
                $table->string('academics_schedule_label', 100)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_cta_text')) {
                $table->string('academics_cta_text', 300)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_cta_btn1_text')) {
                $table->string('academics_cta_btn1_text', 60)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_cta_btn2_text')) {
                $table->string('academics_cta_btn2_text', 60)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_cta_btn3_text')) {
                $table->string('academics_cta_btn3_text', 60)->nullable();
            }

            // Footer section customization fields
            if (! Schema::hasColumn('site_settings', 'footer_site_name')) {
                $table->string('footer_site_name', 150)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_nav_title')) {
                $table->string('footer_nav_title', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_programs_title')) {
                $table->string('footer_programs_title', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_apply_text')) {
                $table->string('footer_apply_text', 60)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_contact_title')) {
                $table->string('footer_contact_title', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_address')) {
                $table->string('footer_address', 300)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_phone')) {
                $table->string('footer_phone', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_email')) {
                $table->string('footer_email', 150)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_office_hours')) {
                $table->string('footer_office_hours', 150)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_portal_text')) {
                $table->string('footer_portal_text', 60)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_privacy_text')) {
                $table->string('footer_privacy_text', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_terms_text')) {
                $table->string('footer_terms_text', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_support_text')) {
                $table->string('footer_support_text', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'footer_support_url')) {
                $table->string('footer_support_url', 300)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $cols = [
                'academics_curriculum_label',
                'academics_highlights_label',
                'academics_schedule_label',
                'academics_cta_text',
                'academics_cta_btn1_text',
                'academics_cta_btn2_text',
                'academics_cta_btn3_text',
                'footer_site_name',
                'footer_nav_title',
                'footer_programs_title',
                'footer_apply_text',
                'footer_contact_title',
                'footer_address',
                'footer_phone',
                'footer_email',
                'footer_office_hours',
                'footer_portal_text',
                'footer_privacy_text',
                'footer_terms_text',
                'footer_support_text',
                'footer_support_url',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('site_settings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
