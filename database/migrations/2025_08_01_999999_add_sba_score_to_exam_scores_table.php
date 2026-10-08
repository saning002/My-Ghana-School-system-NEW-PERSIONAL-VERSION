<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Guard: skip if the column already exists (e.g. was added by an
        // earlier migration that ran before this file was renamed).
        if (Schema::hasColumn('exam_scores', 'sba_score')) {
            return;
        }

        Schema::table('exam_scores', function (Blueprint $table) {
            $table->decimal('sba_score', 5, 2)->nullable()->after('quiz_score');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('exam_scores', 'sba_score')) {
            Schema::table('exam_scores', function (Blueprint $table) {
                $table->dropColumn('sba_score');
            });
        }
    }
};
