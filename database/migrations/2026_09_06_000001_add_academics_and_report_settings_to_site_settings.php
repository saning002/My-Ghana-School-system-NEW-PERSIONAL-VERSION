<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds:
 *  - academics_* columns to site_settings (were in the model but never migrated)
 *  - report_card_orientation to site_settings (portrait | landscape)
 *  - website_programs: adds extra fields (image_path, features JSON)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Academics page columns
            if (! Schema::hasColumn('site_settings', 'academics_hero_title')) {
                $table->string('academics_hero_title', 200)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_hero_subtitle')) {
                $table->string('academics_hero_subtitle', 300)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_eyebrow')) {
                $table->string('academics_eyebrow', 80)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_section_title')) {
                $table->string('academics_section_title', 150)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_section_subtitle')) {
                $table->string('academics_section_subtitle', 300)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_empty_text')) {
                $table->string('academics_empty_text', 300)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_apply_btn')) {
                $table->string('academics_apply_btn', 60)->nullable();
            }
            if (! Schema::hasColumn('site_settings', 'academics_contact_btn')) {
                $table->string('academics_contact_btn', 60)->nullable();
            }
            // Report card orientation setting
            if (! Schema::hasColumn('site_settings', 'report_card_orientation')) {
                $table->string('report_card_orientation', 20)->default('landscape')->nullable();
            }
        });

        // website_programs: add image_path and features
        Schema::table('website_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('website_programs', 'image_path')) {
                $table->string('image_path', 500)->nullable();
            }
            if (! Schema::hasColumn('website_programs', 'features')) {
                $table->json('features')->nullable(); // array of feature strings
            }
            if (! Schema::hasColumn('website_programs', 'highlights')) {
                $table->text('highlights')->nullable(); // rich text or bullet points
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            foreach ([
                'academics_hero_title','academics_hero_subtitle','academics_eyebrow',
                'academics_section_title','academics_section_subtitle',
                'academics_empty_text','academics_apply_btn','academics_contact_btn',
                'report_card_orientation',
            ] as $col) {
                if (Schema::hasColumn('site_settings', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('website_programs', function (Blueprint $table) {
            foreach (['image_path','features','highlights'] as $col) {
                if (Schema::hasColumn('website_programs', $col)) $table->dropColumn($col);
            }
        });
    }
};
