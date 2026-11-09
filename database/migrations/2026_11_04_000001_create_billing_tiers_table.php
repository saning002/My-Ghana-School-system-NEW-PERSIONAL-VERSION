<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_tiers', function (Blueprint $table) {
            $table->id();

            // Which school these tiers belong to
            $table->unsignedBigInteger('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();

            // The student count range for this tier
            // max_students = NULL means "and above" (unlimited, for the top tier)
            $table->unsignedInteger('min_students')->default(0);   // e.g. 0, 101, 201
            $table->unsignedInteger('max_students')->nullable();   // e.g. 100, 200, NULL

            // Per-student rate when student count falls in this tier
            $table->decimal('rate_per_student', 8, 2);             // e.g. 8.00, 7.00

            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_tiers');
    }
};
