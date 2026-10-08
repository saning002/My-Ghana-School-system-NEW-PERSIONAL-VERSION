<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Remove level column
            if (Schema::hasColumn('students', 'level')) {
                $table->dropColumn('level');
            }
        });

        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        // Add 'manifestation' to the status enum — syntax differs per driver
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students MODIFY COLUMN status ENUM('active','graduated','suspended','manifestation') NOT NULL DEFAULT 'active'"
            );
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: alter the underlying check constraint
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students DROP CONSTRAINT IF EXISTS students_status_check"
            );
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students ADD CONSTRAINT students_status_check CHECK (status IN ('active','graduated','suspended','manifestation'))"
            );
        }
        // SQLite: no constraint enforcement needed — string column accepts any value
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->integer('level')->default(1)->after('program_id');
        });

        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students MODIFY COLUMN status ENUM('active','graduated','suspended') NOT NULL DEFAULT 'active'"
            );
        } elseif ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students DROP CONSTRAINT IF EXISTS students_status_check"
            );
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students ADD CONSTRAINT students_status_check CHECK (status IN ('active','graduated','suspended'))"
            );
        }
    }
};
