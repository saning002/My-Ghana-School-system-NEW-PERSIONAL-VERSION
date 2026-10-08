<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->string('excerpt', 400)->nullable();
            $table->text('content');
            $table->string('featured_image_path', 400)->nullable();
            $table->boolean('published')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_blog_posts'); }
};
