<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function () {
            // Delete all child tables referencing old students
            DB::table('attendances')->delete();
            DB::table('exam_scores')->delete();
            DB::table('payments')->delete();
            DB::table('enrollments')->delete();
            
            try {
                DB::table('promotion_histories')->delete();
            } catch (\Throwable $e) {}

            // Retrieve all user IDs who are students
            $studentUserIds = DB::table('users')->where('role', 'student')->pluck('id')->toArray();

            // Clear students table
            DB::table('students')->delete();

            // Clear their corresponding user profiles
            if (!empty($studentUserIds)) {
                DB::table('users')->whereIn('id', $studentUserIds)->delete();
            }
            DB::table('users')->where('role', 'student')->delete();
        });

        // NOTE: Run ImportStudentsFromPdfSeeder separately once it is created:
        // php artisan db:seed --class=ImportStudentsFromPdfSeeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback path needed for data clear
    }
};
