<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $permissionsToAdd = [
            'student_reports',
            'work_logs',
            'exam_questions',
            'scheme_of_learning',
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

    public function down(): void {}
};
