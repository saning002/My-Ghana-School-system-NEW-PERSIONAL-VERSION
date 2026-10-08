<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop the existing FK constraint first
            $table->dropForeign(['course_id']);
            // Re-add as nullable FK
            $table->foreignId('course_id')
                  ->nullable()
                  ->change();
            $table->foreign('course_id')
                  ->references('id')
                  ->on('courses')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['course_id']);
            $table->foreignId('course_id')->nullable(false)->change();
            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
        });
    }
};
