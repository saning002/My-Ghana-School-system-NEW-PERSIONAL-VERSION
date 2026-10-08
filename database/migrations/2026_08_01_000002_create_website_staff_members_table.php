<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('website_staff_members', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('position', 120);
            $table->text('bio')->nullable();
            $table->string('photo_path', 400)->nullable();
            $table->string('email')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('website_staff_members'); }
};
