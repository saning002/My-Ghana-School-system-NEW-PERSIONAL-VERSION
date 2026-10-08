<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('teacher_work_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('teacher_work_logs', 'notes')) {
                $table->text('notes')->nullable()->after('title');
            }
            if (!Schema::hasColumn('teacher_work_logs', 'topic_covered')) {
                $table->string('topic_covered')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('teacher_work_logs', 'week_number')) {
                $table->unsignedSmallInteger('week_number')->nullable()->after('topic_covered');
            }
            if (!Schema::hasColumn('teacher_work_logs', 'admin_comment')) {
                $table->text('admin_comment')->nullable()->after('week_number');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teacher_work_logs', function (Blueprint $table) {
            //
        });
    }
};
