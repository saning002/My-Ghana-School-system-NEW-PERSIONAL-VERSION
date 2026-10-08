<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_gallery_images', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->string('image_path', 400);
            $table->string('caption', 255)->nullable();
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('website_gallery_categories')
                  ->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_gallery_images'); }
};
