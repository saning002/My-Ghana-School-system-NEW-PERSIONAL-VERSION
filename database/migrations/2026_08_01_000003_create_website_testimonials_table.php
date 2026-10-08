<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('parent_name', 120);
            $table->string('child_name', 120)->nullable();
            $table->text('message');
            $table->unsignedTinyInteger('rating')->default(5);
            $table->string('photo_path', 400)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_testimonials'); }
};
