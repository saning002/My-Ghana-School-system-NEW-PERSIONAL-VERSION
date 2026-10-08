<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            // Hero visual options
            $table->string('hero_bg_type', 30)->default('default')->after('hero_subtitle');
            // image | video | solid | gradient | glassmorphism | default
            $table->string('hero_bg_image', 400)->nullable()->after('hero_bg_type');
            $table->string('hero_bg_video_url', 500)->nullable()->after('hero_bg_image');
            $table->string('hero_bg_solid_color', 20)->nullable()->after('hero_bg_video_url');
            $table->string('hero_gradient_from', 20)->nullable()->after('hero_bg_solid_color');
            $table->string('hero_gradient_to', 20)->nullable()->after('hero_gradient_from');
            $table->string('hero_gradient_via', 20)->nullable()->after('hero_gradient_to');
            $table->smallInteger('hero_gradient_angle')->default(135)->after('hero_gradient_via');
            $table->tinyInteger('hero_overlay_opacity')->default(60)->after('hero_gradient_angle');
            // Layout: split | centered | fullscreen | minimal
            $table->string('hero_layout', 20)->default('split')->after('hero_overlay_opacity');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'hero_bg_type','hero_bg_image','hero_bg_video_url',
                'hero_bg_solid_color','hero_gradient_from','hero_gradient_to',
                'hero_gradient_via','hero_gradient_angle','hero_overlay_opacity',
                'hero_layout',
            ]);
        });
    }
};
