<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add missing columns to tenant tables that the app expects but
 * were not included in the initial CREATE TABLE IF NOT EXISTS setup.
 * Safe to run multiple times (uses hasColumn checks).
 */
return new class extends Migration
{
    public function up(): void
    {
        // attendances — add date and status columns used by dashboard
        if (Schema::hasTable('attendances')) {
            Schema::table('attendances', function (Blueprint $table) {
                if (!Schema::hasColumn('attendances', 'date')) {
                    $table->date('date')->nullable()->after('absent_days');
                }
                if (!Schema::hasColumn('attendances', 'status')) {
                    $table->string('status')->nullable()->default('present')->after('date');
                }
                if (!Schema::hasColumn('attendances', 'course_id')) {
                    $table->unsignedBigInteger('course_id')->nullable()->after('status');
                }
            });
        }

        // students — add missing columns
        if (Schema::hasTable('students')) {
            Schema::table('students', function (Blueprint $table) {
                if (!Schema::hasColumn('students', 'surname')) {
                    $table->string('surname')->nullable()->after('user_id');
                }
                if (!Schema::hasColumn('students', 'other_names')) {
                    $table->string('other_names')->nullable()->after('surname');
                }
                if (!Schema::hasColumn('students', 'gender')) {
                    $table->string('gender')->nullable();
                }
                if (!Schema::hasColumn('students', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable();
                }
                if (!Schema::hasColumn('students', 'phone')) {
                    $table->string('phone')->nullable();
                }
                if (!Schema::hasColumn('students', 'address')) {
                    $table->text('address')->nullable();
                }
                if (!Schema::hasColumn('students', 'photo')) {
                    $table->string('photo')->nullable();
                }
                if (!Schema::hasColumn('students', 'nationality')) {
                    $table->string('nationality')->nullable();
                }
                if (!Schema::hasColumn('students', 'religion')) {
                    $table->string('religion')->nullable();
                }
                if (!Schema::hasColumn('students', 'guardian_name')) {
                    $table->string('guardian_name')->nullable();
                }
                if (!Schema::hasColumn('students', 'guardian_phone')) {
                    $table->string('guardian_phone')->nullable();
                }
                if (!Schema::hasColumn('students', 'guardian_email')) {
                    $table->string('guardian_email')->nullable();
                }
            });
        }

        // users — add missing columns
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'surname')) {
                    $table->string('surname')->nullable()->after('full_name');
                }
                if (!Schema::hasColumn('users', 'other_names')) {
                    $table->string('other_names')->nullable()->after('surname');
                }
                if (!Schema::hasColumn('users', 'qualification')) {
                    $table->string('qualification')->nullable();
                }
                if (!Schema::hasColumn('users', 'bio')) {
                    $table->text('bio')->nullable();
                }
                if (!Schema::hasColumn('users', 'gender')) {
                    $table->string('gender')->nullable();
                }
                if (!Schema::hasColumn('users', 'marital_status')) {
                    $table->string('marital_status')->nullable();
                }
                if (!Schema::hasColumn('users', 'nationality')) {
                    $table->string('nationality')->nullable();
                }
                if (!Schema::hasColumn('users', 'date_of_birth')) {
                    $table->date('date_of_birth')->nullable();
                }
                if (!Schema::hasColumn('users', 'place_of_birth')) {
                    $table->string('place_of_birth')->nullable();
                }
                if (!Schema::hasColumn('users', 'religion')) {
                    $table->string('religion')->nullable();
                }
                if (!Schema::hasColumn('users', 'first_language')) {
                    $table->string('first_language')->nullable();
                }
                if (!Schema::hasColumn('users', 'other_language')) {
                    $table->string('other_language')->nullable();
                }
            });
        }

        // programs — add missing columns
        if (Schema::hasTable('programs')) {
            Schema::table('programs', function (Blueprint $table) {
                if (!Schema::hasColumn('programs', 'code')) {
                    $table->string('code')->nullable();
                }
            });
        }

        // church_branches — add missing columns
        if (Schema::hasTable('church_branches')) {
            Schema::table('church_branches', function (Blueprint $table) {
                if (!Schema::hasColumn('church_branches', 'code')) {
                    $table->string('code')->nullable();
                }
                if (!Schema::hasColumn('church_branches', 'location')) {
                    $table->string('location')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        // No down migration needed — these are additive only
    }
};
