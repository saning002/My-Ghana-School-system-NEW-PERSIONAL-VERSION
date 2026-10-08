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
        Schema::table('programs', function (Blueprint $table) {
            $table->integer('expected_classworks')->nullable()->after('sequence');
            $table->integer('expected_homeworks')->nullable()->after('expected_classworks');
            $table->integer('expected_tests')->nullable()->after('expected_homeworks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->dropColumn(['expected_classworks', 'expected_homeworks', 'expected_tests']);
        });
    }
};
