<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── exam_questions: teachers upload exam question docs ─────────────────
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lecturer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->foreignId('church_branch_id')->nullable()->constrained('church_branches')->nullOnDelete();
            $table->string('title');
            $table->string('academic_year', 20)->nullable();
            $table->string('term', 30)->nullable();
            $table->enum('document_type', ['pdf', 'word'])->default('pdf');
            $table->string('file_path');          // stored path
            $table->string('original_filename');  // original name for download
            $table->unsignedBigInteger('file_size')->nullable(); // bytes
            $table->text('notes')->nullable();
            $table->boolean('is_approved')->default(false); // admin can approve/flag
            $table->timestamps();
        });

        // ── work_logs: add notes/description field ─────────────────────────────
        Schema::table('teacher_work_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('teacher_work_logs', 'notes')) {
                $table->text('notes')->nullable()->after('title');
            }
            if (! Schema::hasColumn('teacher_work_logs', 'topic_covered')) {
                $table->string('topic_covered')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('teacher_work_logs', 'week_number')) {
                $table->unsignedTinyInteger('week_number')->nullable()->after('topic_covered');
            }
            if (! Schema::hasColumn('teacher_work_logs', 'admin_comment')) {
                $table->text('admin_comment')->nullable()->after('week_number');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');

        Schema::table('teacher_work_logs', function (Blueprint $table) {
            foreach (['notes','topic_covered','week_number','admin_comment'] as $col) {
                if (Schema::hasColumn('teacher_work_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
