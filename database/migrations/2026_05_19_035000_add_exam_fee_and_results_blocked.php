<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_fees', function (Blueprint $table) {
            if (!Schema::hasColumn('program_fees', 'exam_fee')) {
                $table->decimal('exam_fee', 10, 2)->default(0.00);
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'results_blocked')) {
                $table->boolean('results_blocked')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::table('program_fees', function (Blueprint $table) {
            if (Schema::hasColumn('program_fees', 'exam_fee')) $table->dropColumn('exam_fee');
        });
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'results_blocked')) $table->dropColumn('results_blocked');
        });
    }
};
