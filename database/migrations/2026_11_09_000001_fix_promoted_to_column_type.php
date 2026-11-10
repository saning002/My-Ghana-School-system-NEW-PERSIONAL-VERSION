<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Fix promoted_to column — it was incorrectly created as bigint
 * but should be varchar (stores text like "Class 4", "JHS 2B" etc.)
 */
return new class extends Migration
{
    public function up(): void
    {
        try {
            // Check current type
            $cols = DB::select("
                SELECT data_type
                FROM information_schema.columns
                WHERE table_name = 'student_reports'
                  AND column_name = 'promoted_to'
            ");

            if (!empty($cols) && in_array($cols[0]->data_type, ['bigint','integer','int8','int4'])) {
                // Convert bigint to varchar — existing numeric values become strings
                DB::statement("
                    ALTER TABLE student_reports
                    ALTER COLUMN promoted_to TYPE varchar(255)
                    USING promoted_to::varchar
                ");
            }
        } catch (\Throwable $e) {
            // If column doesn't exist or is already varchar — no action needed
        }
    }

    public function down(): void
    {
        // No down — do not convert back to bigint
    }
};
