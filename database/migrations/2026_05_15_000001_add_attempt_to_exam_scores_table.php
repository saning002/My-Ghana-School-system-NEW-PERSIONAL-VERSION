<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_scores', 'attempt')) {
                $table->unsignedTinyInteger('attempt')->default(1);
            }
            // Drop old unique only if it exists
            try { $table->dropUnique('uq_exam_scores'); } catch (\Throwable $e) {}
            // Add new unique only if it doesn't already exist
            try {
                $table->unique(['student_id', 'course_id', 'program_id', 'attempt'], 'uq_exam_scores_attempt');
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            try { $table->dropUnique('uq_exam_scores_attempt'); } catch (\Throwable $e) {}
            if (Schema::hasColumn('exam_scores', 'attempt')) {
                $table->dropColumn('attempt');
            }
            try { $table->unique(['student_id', 'course_id', 'program_id'], 'uq_exam_scores'); } catch (\Throwable $e) {}
        });
    }
};
