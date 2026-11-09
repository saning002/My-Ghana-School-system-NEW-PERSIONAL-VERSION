<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('academic_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_sessions', 'mode')) {
                $table->enum('mode', ['semester', 'term'])->default('semester')->after('year');
            }
        });

        Schema::table('academic_periods', function (Blueprint $table) {
            if (!Schema::hasColumn('academic_periods', 'name')) {
                $table->string('name')->nullable()->after('month');
            }
            if (!Schema::hasColumn('academic_periods', 'is_active')) {
                $table->boolean('is_active')->default(false)->after('name');
            }
            if (!Schema::hasColumn('academic_periods', 'sequence')) {
                $table->unsignedTinyInteger('sequence')->default(1)->after('is_active');
            }
        });

        try { DB::table('academic_periods')->update(['name' => DB::raw('month')]); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        Schema::table('academic_periods', function (Blueprint $table) {
            foreach (['name', 'is_active', 'sequence'] as $col) {
                if (Schema::hasColumn('academic_periods', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('academic_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('academic_sessions', 'mode')) $table->dropColumn('mode');
        });
    }
};
