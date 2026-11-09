<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            if (!Schema::hasColumn('programs', 'expected_classworks')) {
                $table->integer('expected_classworks')->nullable();
            }
            if (!Schema::hasColumn('programs', 'expected_homeworks')) {
                $table->integer('expected_homeworks')->nullable();
            }
            if (!Schema::hasColumn('programs', 'expected_tests')) {
                $table->integer('expected_tests')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            foreach (['expected_classworks', 'expected_homeworks', 'expected_tests'] as $col) {
                if (Schema::hasColumn('programs', $col)) $table->dropColumn($col);
            }
        });
    }
};
