<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            // Add attempt number (1 = first sitting, 2 = repeat, etc.)
            $table->unsignedTinyInteger('attempt')->default(1)->after('program_id');

            // Drop the old unique constraint that prevented repeat attempts
            $table->dropUnique('uq_exam_scores');

            // New unique constraint includes attempt
            $table->unique(['student_id', 'course_id', 'program_id', 'attempt'], 'uq_exam_scores_attempt');
        });
    }

    public function down(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            $table->dropUnique('uq_exam_scores_attempt');
            $table->dropColumn('attempt');
            $table->unique(['student_id', 'course_id', 'program_id'], 'uq_exam_scores');
        });
    }
};
