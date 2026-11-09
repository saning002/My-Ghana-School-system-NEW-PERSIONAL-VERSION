<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $cols = [
                'hero_bg_type' => function() use ($table) { $table->string('hero_bg_type', 30)->default('default'); },
                'hero_bg_image' => function() use ($table) { $table->string('hero_bg_image', 400)->nullable(); },
                'hero_bg_video_url' => function() use ($table) { $table->string('hero_bg_video_url', 500)->nullable(); },
                'hero_bg_solid_color' => function() use ($table) { $table->string('hero_bg_solid_color', 20)->nullable(); },
                'hero_gradient_from' => function() use ($table) { $table->string('hero_gradient_from', 20)->nullable(); },
                'hero_gradient_to' => function() use ($table) { $table->string('hero_gradient_to', 20)->nullable(); },
                'hero_gradient_via' => function() use ($table) { $table->string('hero_gradient_via', 20)->nullable(); },
                'hero_gradient_angle' => function() use ($table) { $table->smallInteger('hero_gradient_angle')->default(135); },
                'hero_overlay_opacity' => function() use ($table) { $table->tinyInteger('hero_overlay_opacity')->default(60); },
                'hero_layout' => function() use ($table) { $table->string('hero_layout', 20)->default('split'); },
            ];
            foreach ($cols as $colName => $adder) {
                if (!Schema::hasColumn('site_settings', $colName)) {
                    $adder();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $cols = ['hero_bg_type','hero_bg_image','hero_bg_video_url','hero_bg_solid_color','hero_gradient_from','hero_gradient_to','hero_gradient_via','hero_gradient_angle','hero_overlay_opacity','hero_layout'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('site_settings', $c));
            if (!empty($existing)) $table->dropColumn($existing);
        });
    }
};
