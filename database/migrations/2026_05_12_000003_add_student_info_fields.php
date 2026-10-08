<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add extra fields to users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('address');
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            $table->string('nationality')->nullable()->after('marital_status');
        });

        // Add extra fields to students table
        Schema::table('students', function (Blueprint $table) {
            $table->string('qualifications')->nullable()->after('date_issued');
            $table->string('native_town')->nullable()->after('qualifications');
            $table->string('enrollment_year')->nullable()->after('native_town');
            $table->enum('study_mode', ['full_time', 'part_time'])->default('full_time')->after('enrollment_year');
            $table->string('profession')->nullable()->after('study_mode');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'marital_status', 'nationality']);
        });
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['qualifications', 'native_town', 'enrollment_year', 'study_mode', 'profession']);
        });
    }
};
