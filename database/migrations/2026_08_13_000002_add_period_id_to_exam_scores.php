<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_scores', 'academic_period_id')) {
                $table->foreignId('academic_period_id')->nullable()->after('program_id')->constrained('academic_periods')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            if (Schema::hasColumn('exam_scores', 'academic_period_id')) {
                $table->dropForeign(['academic_period_id']);
                $table->dropColumn('academic_period_id');
            }
        });
    }
};
