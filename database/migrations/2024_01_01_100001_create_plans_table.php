<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // Basic, Standard, Premium
            $table->string('slug')->unique();                // basic, standard, premium
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);    // Monthly price
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'yearly'])->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->integer('max_students')->nullable();     // null = unlimited
            $table->integer('max_lecturers')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
