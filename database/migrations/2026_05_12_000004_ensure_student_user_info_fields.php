<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gender')) {
                $table->enum('gender', ['male', 'female', 'other'])->nullable()->after('address');
            }
            if (! Schema::hasColumn('users', 'marital_status')) {
                $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable()->after('gender');
            }
            if (! Schema::hasColumn('users', 'nationality')) {
                $table->string('nationality')->nullable()->after('marital_status');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'photo')) {
                $table->string('photo')->nullable()->after('church_branch_id');
            }
            if (! Schema::hasColumn('students', 'signal')) {
                $table->string('signal')->nullable()->after('photo');
            }
            if (! Schema::hasColumn('students', 'position')) {
                $table->string('position')->nullable()->after('signal');
            }
            if (! Schema::hasColumn('students', 'date_issued')) {
                $table->date('date_issued')->nullable()->after('position');
            }
            if (! Schema::hasColumn('students', 'qualifications')) {
                $table->string('qualifications')->nullable()->after('date_issued');
            }
            if (! Schema::hasColumn('students', 'native_town')) {
                $table->string('native_town')->nullable()->after('qualifications');
            }
            if (! Schema::hasColumn('students', 'enrollment_year')) {
                $table->string('enrollment_year')->nullable()->after('native_town');
            }
            if (! Schema::hasColumn('students', 'study_mode')) {
                $table->enum('study_mode', ['full_time', 'part_time'])->default('full_time')->after('enrollment_year');
            }
            if (! Schema::hasColumn('students', 'profession')) {
                $table->string('profession')->nullable()->after('study_mode');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'nationality')) {
                $table->dropColumn('nationality');
            }
            if (Schema::hasColumn('users', 'marital_status')) {
                $table->dropColumn('marital_status');
            }
            if (Schema::hasColumn('users', 'gender')) {
                $table->dropColumn('gender');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            foreach (['profession', 'study_mode', 'enrollment_year', 'native_town', 'qualifications', 'date_issued', 'position', 'signal', 'photo'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
