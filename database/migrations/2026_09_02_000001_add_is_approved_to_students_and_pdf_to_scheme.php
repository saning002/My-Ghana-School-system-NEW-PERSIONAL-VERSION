<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds:
 *  - students.is_approved  (boolean, default false) — controls the NEW badge
 *  - scheme_of_learning.pdf_path (nullable string)  — for uploaded PDF schemes
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── students: is_approved ─────────────────────────────────────────────
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'is_approved')) {
                $table->boolean('is_approved')->default(false)->after('status');
            }
        });

        // Mark all currently-active students as already approved so their
        // NEW badge does not suddenly appear after the migration.
        \Illuminate\Support\Facades\DB::table('students')
            ->where('status', 'active')
            ->update(['is_approved' => true]);

        // ── scheme_of_learning: pdf_path ──────────────────────────────────────
        Schema::table('scheme_of_learning', function (Blueprint $table) {
            if (! Schema::hasColumn('scheme_of_learning', 'pdf_path')) {
                $table->string('pdf_path')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('scheme_of_learning', 'church_branch_id')) {
                $table->foreignId('church_branch_id')
                      ->nullable()
                      ->constrained('church_branches')
                      ->nullOnDelete()
                      ->after('lecturer_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'is_approved')) {
                $table->dropColumn('is_approved');
            }
        });

        Schema::table('scheme_of_learning', function (Blueprint $table) {
            if (Schema::hasColumn('scheme_of_learning', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
            if (Schema::hasColumn('scheme_of_learning', 'church_branch_id')) {
                $table->dropForeignIfExists(['church_branch_id']);
                $table->dropColumn('church_branch_id');
            }
        });
    }
};
