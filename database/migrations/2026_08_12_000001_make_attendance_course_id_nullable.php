<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            try { $table->dropForeign(['course_id']); } catch (\Throwable $e) {}
            // make nullable if not already
            if (Schema::hasColumn('attendances', 'course_id')) {
                try { $table->foreignId('course_id')->nullable()->change(); } catch (\Throwable $e) {}
            }
            try {
                $table->foreign('course_id')->references('id')->on('courses')->nullOnDelete();
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        // No down needed — safe idempotent migration
    }
};
