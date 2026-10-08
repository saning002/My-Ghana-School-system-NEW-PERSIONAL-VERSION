<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ── academic_sessions: add mode (semester|term) ──────────────────────
        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->enum('mode', ['semester', 'term'])->default('semester')->after('year');
        });

        // ── academic_periods: add name + is_active ───────────────────────────
        // 'month' column already exists as free-text — we add a proper 'name'
        // column (for "Semester 1", "Term 2", etc.) and is_active flag.
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->string('name')->nullable()->after('month');
            $table->boolean('is_active')->default(false)->after('name');
            $table->unsignedTinyInteger('sequence')->default(1)->after('is_active');
        });

        // Back-fill name from month for all existing periods
        DB::table('academic_periods')->update([
            'name' => DB::raw('month'),
        ]);
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_active', 'sequence']);
        });

        Schema::table('academic_sessions', function (Blueprint $table) {
            $table->dropColumn('mode');
        });
    }
};
