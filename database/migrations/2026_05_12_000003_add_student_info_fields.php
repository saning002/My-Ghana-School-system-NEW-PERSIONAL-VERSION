<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable();
            }
            if (!Schema::hasColumn('users', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            }
            if (!Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable();
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'qualifications')) {
                $table->string('qualifications')->nullable();
            }
            if (!Schema::hasColumn('students', 'native_town')) {
                $table->string('native_town')->nullable();
            }
            if (!Schema::hasColumn('students', 'enrollment_year')) {
                $table->string('enrollment_year')->nullable();
            }
            if (!Schema::hasColumn('students', 'study_mode')) {
                $table->enum('study_mode', ['full_time', 'part_time'])->default('full_time');
            }
            if (!Schema::hasColumn('students', 'profession')) {
                $table->string('profession')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['gender', 'marital_status', 'nationality'] as $col) {
                if (Schema::hasColumn('users', $col)) $table->dropColumn($col);
            }
        });
        Schema::table('students', function (Blueprint $table) {
            foreach (['qualifications', 'native_town', 'enrollment_year', 'study_mode', 'profession'] as $col) {
                if (Schema::hasColumn('students', $col)) $table->dropColumn($col);
            }
        });
    }
};
