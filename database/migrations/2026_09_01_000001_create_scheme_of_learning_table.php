<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheme_of_learning', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('academic_year', 20);           // e.g. 2025/2026
            $table->string('term', 30)->nullable();        // Term 1, Semester 1, etc.
            $table->unsignedTinyInteger('week_number');    // 1–52
            $table->string('topic', 255);
            $table->text('subtopics')->nullable();
            $table->text('learning_objectives')->nullable();
            $table->string('teaching_methods', 255)->nullable();
            $table->string('resources', 255)->nullable();
            $table->string('assessment_type', 100)->nullable(); // Quiz, Test, Project…
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'course_id', 'academic_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheme_of_learning');
    }
};
