<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Widens website_programs.level from varchar(30) to varchar(100)
 * so school programs with long names (e.g. "third_semester_practical_program")
 * do not cause "value too long" errors during the seeder migration.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('website_programs', function (Blueprint $table) {
            $table->string('level', 100)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('website_programs', function (Blueprint $table) {
            $table->string('level', 30)->nullable()->change();
        });
    }
};
