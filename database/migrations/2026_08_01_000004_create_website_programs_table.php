<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_programs', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('level', 30);          // daycare | nursery | preschool | kindergarten
            $table->string('age_range', 50);
            $table->text('description');
            $table->text('curriculum')->nullable();
            $table->text('schedule')->nullable();
            $table->string('icon', 80)->nullable();
            $table->string('color', 40)->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_programs'); }
};
