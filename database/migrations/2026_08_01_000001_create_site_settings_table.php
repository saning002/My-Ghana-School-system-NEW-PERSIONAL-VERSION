<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name', 120)->default('EduCare International School');
            $table->string('tagline', 300)->nullable();
            $table->string('hero_title', 200)->default('Nurturing Young Minds for a Bright Future');
            $table->string('hero_subtitle', 400)->nullable();
            $table->json('stats')->nullable();           // [{"value":"500+","label":"Happy Students"},…]
            $table->text('about_text')->nullable();
            $table->text('mission')->nullable();
            $table->text('vision')->nullable();
            $table->text('history')->nullable();
            $table->text('core_values')->nullable();
            $table->text('admissions_info')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('map_embed_url', 600)->nullable();
            $table->string('facebook', 300)->nullable();
            $table->string('twitter', 300)->nullable();
            $table->string('instagram', 300)->nullable();
            $table->string('youtube', 300)->nullable();
            $table->string('logo_path', 400)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('site_settings'); }
};
