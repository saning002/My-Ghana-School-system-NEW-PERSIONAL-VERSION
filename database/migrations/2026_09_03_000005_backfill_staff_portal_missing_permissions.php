<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add work_logs, scheme_of_learning, and exam_questions permissions
     * to ALL existing staff portal users who don't already have them.
     * Runs exactly once. Admin can untick them afterwards.
     */
    public function up(): void
    {
        $permissionsToAdd = [
            'work_logs',
            'scheme_of_learning',
            'exam_questions',
        ];

        $staffUsers = DB::table('staff_portal_users')->get();

        foreach ($staffUsers as $user) {
            foreach ($permissionsToAdd as $perm) {
                $exists = DB::table('staff_portal_permissions')
                    ->where('staff_portal_user_id', $user->id)
                    ->where('permission', $perm)
                    ->exists();

                if (!$exists) {
                    DB::table('staff_portal_permissions')->insert([
                        'staff_portal_user_id' => $user->id,
                        'permission'           => $perm,
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // No rollback — admin can untick manually
    }
};
