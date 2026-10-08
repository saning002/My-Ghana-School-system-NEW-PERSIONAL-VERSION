<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->decimal('quiz_score', 5, 2)->nullable();
            $table->decimal('exam_score', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'course_id', 'program_id'], 'uq_exam_scores');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_scores');
    }
};
