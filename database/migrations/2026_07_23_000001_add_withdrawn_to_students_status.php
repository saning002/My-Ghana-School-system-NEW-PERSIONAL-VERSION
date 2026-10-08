<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students MODIFY COLUMN status ENUM('active','graduated','suspended','manifestation','withdrawn') NOT NULL DEFAULT 'active'"
            );
        } elseif ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students DROP CONSTRAINT IF EXISTS students_status_check"
            );
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students ADD CONSTRAINT students_status_check CHECK (status IN ('active','graduated','suspended','manifestation','withdrawn'))"
            );
        }
        // SQLite: no constraint enforcement needed
    }

    public function down(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();

        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students MODIFY COLUMN status ENUM('active','graduated','suspended','manifestation') NOT NULL DEFAULT 'active'"
            );
        } elseif ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students DROP CONSTRAINT IF EXISTS students_status_check"
            );
            \Illuminate\Support\Facades\DB::statement(
                "ALTER TABLE students ADD CONSTRAINT students_status_check CHECK (status IN ('active','graduated','suspended','manifestation'))"
            );
        }
    }
};
