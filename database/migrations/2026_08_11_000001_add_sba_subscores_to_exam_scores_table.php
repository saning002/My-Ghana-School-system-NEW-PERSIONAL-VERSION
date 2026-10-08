<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            if (!Schema::hasColumn('exam_scores', 'test1_score')) {
                $table->decimal('test1_score', 5, 2)->nullable()->after('sba_score')
                      ->comment('SBA component 1 – Test 1');
            }
            if (!Schema::hasColumn('exam_scores', 'groupwork_score')) {
                $table->decimal('groupwork_score', 5, 2)->nullable()->after('test1_score')
                      ->comment('SBA component 2 – Group Work');
            }
            if (!Schema::hasColumn('exam_scores', 'test2_score')) {
                $table->decimal('test2_score', 5, 2)->nullable()->after('groupwork_score')
                      ->comment('SBA component 3 – Test 2');
            }
            if (!Schema::hasColumn('exam_scores', 'project_score')) {
                $table->decimal('project_score', 5, 2)->nullable()->after('test2_score')
                      ->comment('SBA component 4 – Project Work');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exam_scores', function (Blueprint $table) {
            foreach (['test1_score','groupwork_score','test2_score','project_score'] as $col) {
                if (Schema::hasColumn('exam_scores', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
