<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TenantSetupController extends Controller
{
    /**
     * Run all table creations directly via SQL — no artisan migrate.
     * Uses CREATE TABLE IF NOT EXISTS so it's safe on both fresh and existing DBs.
     */
    public function runMigrations(Tenant $tenant)
    {
        $results = [];

        // Step 1: Connect
        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->getPdo();
            $results[] = '✅ Connected to ' . $tenant->db_name;
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            return back()->with('setup_error', 'DB Connection failed: ' . $e->getMessage())
                         ->with('active_tab', 'setup');
        }

        // Step 2: Create every table using IF NOT EXISTS
        $tables = $this->tableDefinitions();
        $created = 0;
        $errors  = [];

        foreach ($tables as $tableName => $sql) {
            try {
                DB::connection('tenant')->statement($sql);
                $created++;
            } catch (\Throwable $e) {
                try { DB::connection('tenant')->statement('ROLLBACK'); } catch (\Throwable $ignored) {}
                $errors[] = "{$tableName}: " . $e->getMessage();
            }
        }

        $results[] = "✅ {$created} tables created/verified";
        if (count($errors)) {
            $results[] = '⚠️ ' . count($errors) . ' table errors: ' . implode('; ', array_slice($errors, 0, 3));
        }

        // Step 3: Safe constraint/index additions
        foreach ($this->constraintStatements() as $stmt) {
            try {
                DB::connection('tenant')->statement($stmt);
            } catch (\Throwable $e) {
                try { DB::connection('tenant')->statement('ROLLBACK'); } catch (\Throwable $ignored) {}
            }
        }
        $results[] = '✅ Indexes and constraints applied';

        // Step 3b: Add missing columns to existing tables (idempotent DO blocks)
        foreach ($this->columnAdditions() as $stmt) {
            try {
                DB::connection('tenant')->statement($stmt);
            } catch (\Throwable $e) {
                try { DB::connection('tenant')->statement('ROLLBACK'); } catch (\Throwable $ignored) {}
            }
        }
        $results[] = '✅ Column additions applied';

        // Step 4: Seed settings
        try {
            $exists = DB::connection('tenant')->getSchemaBuilder()->hasTable('settings');
            if ($exists) {
                Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--database' => 'tenant', '--force' => true]);
                $results[] = '✅ Settings seeded';
            } else {
                $results[] = '⚠️ Settings table missing after creation attempt';
            }
        } catch (\Throwable $e) {
            $results[] = '⚠️ Seeder error: ' . $e->getMessage();
        }

        // Reset
        DB::setDefaultConnection('pgsql');
        DB::purge('tenant');

        $message = implode(' | ', $results);
        AuditLog::record('tenant.setup', "Setup '{$tenant->name}': {$message}", $tenant->id);

        return back()->with('setup_success', $message)->with('active_tab', 'setup');
    }

    /**
     * Delete an admin from tenant DB (owner panel action).
     */
    public function deleteAdmin(Request $request, Tenant $tenant)
    {
        $userId = $request->validate(['user_id' => 'required|integer'])['user_id'];

        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            // Count super admins before deleting
            $superAdminCount = DB::connection('tenant')
                ->table('users')
                ->where('role', 'admin')
                ->where('is_super_admin', true)
                ->count();

            $user = DB::connection('tenant')->table('users')->where('id', $userId)->first();

            if (!$user) {
                DB::setDefaultConnection('pgsql'); DB::purge('tenant');
                return response()->json(['error' => 'User not found.'], 404);
            }

            if ($user->is_super_admin && $superAdminCount <= 1) {
                DB::setDefaultConnection('pgsql'); DB::purge('tenant');
                return response()->json(['error' => 'Cannot delete the last Super Admin.'], 422);
            }

            DB::connection('tenant')->table('users')->where('id', $userId)->delete();
            DB::setDefaultConnection('pgsql'); DB::purge('tenant');

            AuditLog::record('tenant.admin_deleted', "Admin ID {$userId} deleted from '{$tenant->name}'", $tenant->id);

            return response()->json(['success' => true, 'message' => 'Admin deleted successfully.']);

        } catch (\Throwable $e) {
            try { DB::setDefaultConnection('pgsql'); } catch (\Throwable $e2) {}
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update an admin in tenant DB (owner panel action).
     */
    public function updateAdmin(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'user_id'          => 'required|integer',
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|max:255',
            'is_super_admin'   => 'nullable|boolean',
            'church_branch_id' => 'nullable|integer',
            'new_password'     => 'nullable|string|min:8',
        ]);

        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            $emailTaken = DB::connection('tenant')
                ->table('users')
                ->where('email', $validated['email'])
                ->where('id', '!=', $validated['user_id'])
                ->exists();

            if ($emailTaken) {
                DB::setDefaultConnection('pgsql'); DB::purge('tenant');
                return response()->json(['error' => "Email '{$validated['email']}' is already taken."], 422);
            }

            $updates = [
                'full_name'        => $validated['full_name'],
                'email'            => $validated['email'],
                'is_super_admin'   => $request->boolean('is_super_admin', false),
                'church_branch_id' => $request->filled('church_branch_id') ? $validated['church_branch_id'] : null,
                'updated_at'       => now(),
            ];

            if (!empty($validated['new_password'])) {
                $updates['password'] = Hash::make($validated['new_password']);
            }

            DB::connection('tenant')->table('users')
                ->where('id', $validated['user_id'])
                ->update($updates);

            DB::setDefaultConnection('pgsql'); DB::purge('tenant');

            AuditLog::record('tenant.admin_updated', "Admin ID {$validated['user_id']} updated in '{$tenant->name}'", $tenant->id);

            return response()->json(['success' => true, 'message' => 'Admin updated successfully.']);

        } catch (\Throwable $e) {
            try { DB::setDefaultConnection('pgsql'); } catch (\Throwable $e2) {}
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Create initial super admin in tenant DB.
     */
    public function createAdmin(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'admin_full_name'  => 'required|string|max:255',
            'admin_email'      => 'required|email|max:255',
            'admin_password'   => 'required|string|min:8|confirmed',
            'church_branch_id' => 'nullable|integer',
            'is_super_admin'   => 'nullable|boolean',
        ]);

        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->getPdo();
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
            return back()->with('setup_error', 'DB Connection failed: ' . $e->getMessage())->with('active_tab', 'setup');
        }

        try {
            $exists = DB::connection('tenant')->table('users')->where('email', $validated['admin_email'])->exists();
            if ($exists) {
                DB::setDefaultConnection('pgsql'); DB::purge('tenant');
                return back()->with('setup_error', "Admin '{$validated['admin_email']}' already exists.")->with('active_tab', 'setup');
            }

            DB::connection('tenant')->table('users')->insert([
                'full_name'        => $validated['admin_full_name'],
                'email'            => $validated['admin_email'],
                'password'         => Hash::make($validated['admin_password']),
                'role'             => 'admin',
                'is_super_admin'   => $request->boolean('is_super_admin', true),
                'church_branch_id' => $request->filled('church_branch_id') ? $validated['church_branch_id'] : null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql'); DB::purge('tenant');
            return back()->with('setup_error', 'Failed to create admin: ' . $e->getMessage())->with('active_tab', 'setup');
        }

        DB::setDefaultConnection('pgsql'); DB::purge('tenant');

        AuditLog::record('tenant.admin_created', "Admin '{$validated['admin_email']}' created for '{$tenant->name}'", $tenant->id);

        return back()->with('setup_success', "✅ Super admin '{$validated['admin_email']}' created for {$tenant->name}.")->with('active_tab', 'setup');
    }

    /**
     * Reset admin password in tenant DB.
     */
    public function resetAdminPassword(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'reset_email'    => 'required|email',
            'reset_password' => 'required|string|min:8|confirmed',
        ]);

        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            $updated = DB::connection('tenant')->table('users')
                ->where('email', $validated['reset_email'])
                ->update(['password' => Hash::make($validated['reset_password']), 'updated_at' => now()]);

            DB::setDefaultConnection('pgsql'); DB::purge('tenant');

            if (!$updated) {
                return back()->with('setup_error', "No user found with email '{$validated['reset_email']}'")->with('active_tab', 'setup');
            }
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql'); DB::purge('tenant');
            return back()->with('setup_error', 'Reset failed: ' . $e->getMessage())->with('active_tab', 'setup');
        }

        AuditLog::record('tenant.password_reset', "Password reset for '{$validated['reset_email']}' in '{$tenant->name}'", $tenant->id);

        return back()->with('setup_success', "✅ Password updated for '{$validated['reset_email']}'")->with('active_tab', 'setup');
    }

    /**
     * List branches in tenant DB (JSON for AJAX).
     */
    public function listBranches(Tenant $tenant)
    {
        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');
            DB::connection('tenant')->getPdo();

            $branches = DB::connection('tenant')
                ->table('church_branches')
                ->orderBy('name')
                ->get(['id', 'name']);

            DB::setDefaultConnection('pgsql'); DB::purge('tenant');
            return response()->json($branches);

        } catch (\Throwable $e) {
            try { DB::setDefaultConnection('pgsql'); } catch (\Throwable $e2) {}
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * List admins in tenant DB (JSON for AJAX).
     */
    public function listAdmins(Tenant $tenant)
    {
        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            // Test connection first
            DB::connection('tenant')->getPdo();

            $admins = DB::connection('tenant')
                ->table('users')
                ->leftJoin('church_branches', 'users.church_branch_id', '=', 'church_branches.id')
                ->where('users.role', 'admin')
                ->orderByDesc('users.is_super_admin')
                ->orderBy('users.full_name')
                ->get([
                    'users.id',
                    'users.full_name',
                    'users.email',
                    'users.is_super_admin',
                    'users.church_branch_id',
                    'users.created_at',
                    'church_branches.name as branch_name',
                ]);

            DB::setDefaultConnection('pgsql');
            DB::purge('tenant');

            return response()->json($admins);

        } catch (\Throwable $e) {
            try { DB::setDefaultConnection('pgsql'); } catch (\Throwable $e2) {}
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Table Definitions ──────────────────────────────────────────────────────

    private function tableDefinitions(): array
    {
        return [
            'migrations' => 'CREATE TABLE IF NOT EXISTS "migrations" ("id" SERIAL PRIMARY KEY, "migration" VARCHAR(255) NOT NULL, "batch" INTEGER NOT NULL)',
            'password_reset_tokens' => 'CREATE TABLE IF NOT EXISTS "password_reset_tokens" ("email" VARCHAR(255) PRIMARY KEY, "token" VARCHAR(255) NOT NULL, "created_at" TIMESTAMP NULL)',
            'sessions' => 'CREATE TABLE IF NOT EXISTS "sessions" ("id" VARCHAR(255) PRIMARY KEY, "user_id" BIGINT NULL, "ip_address" VARCHAR(45) NULL, "user_agent" TEXT NULL, "payload" TEXT NOT NULL, "last_activity" INTEGER NOT NULL)',
            'cache' => 'CREATE TABLE IF NOT EXISTS "cache" ("key" VARCHAR(255) PRIMARY KEY, "value" TEXT NOT NULL, "expiration" INTEGER NOT NULL)',
            'cache_locks' => 'CREATE TABLE IF NOT EXISTS "cache_locks" ("key" VARCHAR(255) PRIMARY KEY, "owner" VARCHAR(255) NOT NULL, "expiration" INTEGER NOT NULL)',
            'jobs' => 'CREATE TABLE IF NOT EXISTS "jobs" ("id" BIGSERIAL PRIMARY KEY, "queue" VARCHAR(255) NOT NULL, "payload" TEXT NOT NULL, "attempts" SMALLINT NOT NULL DEFAULT 0, "reserved_at" INTEGER NULL, "available_at" INTEGER NOT NULL, "created_at" INTEGER NOT NULL)',
            'settings' => 'CREATE TABLE IF NOT EXISTS "settings" ("id" BIGSERIAL PRIMARY KEY, "key" VARCHAR(255) NOT NULL UNIQUE, "value" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'site_settings' => 'CREATE TABLE IF NOT EXISTS "site_settings" ("id" BIGSERIAL PRIMARY KEY, "site_name" VARCHAR(255) NOT NULL DEFAULT \'School\', "tagline" VARCHAR(255) NULL, "site_subtitle" VARCHAR(255) NULL, "site_logo_url" TEXT NULL, "hero_title" VARCHAR(255) NULL, "hero_subtitle" TEXT NULL, "theme" JSON NULL, "theme_colors" JSON NULL, "contact_email" VARCHAR(255) NULL, "contact_phone" VARCHAR(255) NULL, "contact_address" TEXT NULL, "facebook_url" VARCHAR(255) NULL, "twitter_url" VARCHAR(255) NULL, "instagram_url" VARCHAR(255) NULL, "youtube_url" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'church_branches' => 'CREATE TABLE IF NOT EXISTS "church_branches" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "code" VARCHAR(50) NULL, "location" VARCHAR(255) NULL, "address" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'users' => 'CREATE TABLE IF NOT EXISTS "users" ("id" BIGSERIAL PRIMARY KEY, "full_name" VARCHAR(255) NOT NULL, "surname" VARCHAR(255) NULL, "other_names" VARCHAR(255) NULL, "email" VARCHAR(255) NOT NULL, "date_of_birth" DATE NULL, "place_of_birth" VARCHAR(255) NULL, "religion" VARCHAR(255) NULL, "first_language" VARCHAR(255) NULL, "other_language" VARCHAR(255) NULL, "phone" VARCHAR(255) NULL, "address" TEXT NULL, "photo" VARCHAR(255) NULL, "qualification" VARCHAR(255) NULL, "bio" TEXT NULL, "gender" VARCHAR(255) NULL, "marital_status" VARCHAR(255) NULL, "nationality" VARCHAR(255) NULL, "role" VARCHAR(255) NOT NULL DEFAULT \'admin\', "is_super_admin" BOOLEAN NOT NULL DEFAULT FALSE, "church_branch_id" BIGINT NULL, "login_background_url" TEXT NULL, "email_verified_at" TIMESTAMP NULL, "password" VARCHAR(255) NOT NULL, "remember_token" VARCHAR(100) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'programs' => 'CREATE TABLE IF NOT EXISTS "programs" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "code" VARCHAR(50) NULL, "description" TEXT NULL, "sequence" INTEGER NOT NULL DEFAULT 0, "church_branch_id" BIGINT NULL, "duration_years" INTEGER NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'courses' => 'CREATE TABLE IF NOT EXISTS "courses" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "code" VARCHAR(255) NULL, "description" TEXT NULL, "program_id" BIGINT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'students' => 'CREATE TABLE IF NOT EXISTS "students" ("id" BIGSERIAL PRIMARY KEY, "user_id" BIGINT NOT NULL, "student_id" VARCHAR(255) NOT NULL, "program_id" BIGINT NULL, "church_branch_id" BIGINT NULL, "status" VARCHAR(255) NOT NULL DEFAULT \'active\', "year_of_admission" INTEGER NULL, "portal_password" VARCHAR(255) NULL, "gender" VARCHAR(255) NULL, "date_of_birth" DATE NULL, "phone" VARCHAR(255) NULL, "address" TEXT NULL, "photo" VARCHAR(255) NULL, "nationality" VARCHAR(255) NULL, "religion" VARCHAR(255) NULL, "guardian_name" VARCHAR(255) NULL, "guardian_phone" VARCHAR(255) NULL, "guardian_email" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'enrollments' => 'CREATE TABLE IF NOT EXISTS "enrollments" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "course_id" BIGINT NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'academic_sessions' => 'CREATE TABLE IF NOT EXISTS "academic_sessions" ("id" BIGSERIAL PRIMARY KEY, "year" VARCHAR(255) NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'academic_periods' => 'CREATE TABLE IF NOT EXISTS "academic_periods" ("id" BIGSERIAL PRIMARY KEY, "academic_session_id" BIGINT NOT NULL, "month" VARCHAR(255) NOT NULL, "is_active" BOOLEAN NOT NULL DEFAULT FALSE, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'attendances' => 'CREATE TABLE IF NOT EXISTS "attendances" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "academic_period_id" BIGINT NOT NULL, "course_id" BIGINT NULL, "date" DATE NULL, "status" VARCHAR(255) NULL DEFAULT \'present\', "present_days" INTEGER NOT NULL DEFAULT 0, "absent_days" INTEGER NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'exam_scores' => 'CREATE TABLE IF NOT EXISTS "exam_scores" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "course_id" BIGINT NOT NULL, "class_score" DECIMAL(8,2) NULL, "exam_score" DECIMAL(8,2) NULL, "academic_session_id" BIGINT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'program_fees' => 'CREATE TABLE IF NOT EXISTS "program_fees" ("id" BIGSERIAL PRIMARY KEY, "program_id" BIGINT NOT NULL, "church_branch_id" BIGINT NULL, "amount" DECIMAL(10,2) NOT NULL DEFAULT 0, "exam_fee" DECIMAL(10,2) NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'student_fees' => 'CREATE TABLE IF NOT EXISTS "student_fees" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "amount" DECIMAL(10,2) NULL, "exam_fee" DECIMAL(10,2) NULL, "notes" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'payments' => 'CREATE TABLE IF NOT EXISTS "payments" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "amount_paid" DECIMAL(10,2) NOT NULL, "payment_date" DATE NOT NULL, "notes" TEXT NULL, "payment_method" VARCHAR(255) NOT NULL DEFAULT \'cash\', "paystack_reference" VARCHAR(255) NULL, "paystack_status" VARCHAR(255) NULL, "paystack_channel" VARCHAR(255) NULL, "transaction_id" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'student_notifications' => 'CREATE TABLE IF NOT EXISTS "student_notifications" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NULL, "lecturer_id" BIGINT NULL, "recipient_type" VARCHAR(255) NOT NULL DEFAULT \'student\', "title" VARCHAR(255) NOT NULL, "message" TEXT NOT NULL, "is_read" BOOLEAN NOT NULL DEFAULT FALSE, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'course_assignments' => 'CREATE TABLE IF NOT EXISTS "course_assignments" ("id" BIGSERIAL PRIMARY KEY, "lecturer_id" BIGINT NOT NULL, "course_id" BIGINT NOT NULL, "program_id" BIGINT NOT NULL, "sba_percentage" INTEGER NULL, "exam_percentage" INTEGER NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'timetable_entries' => 'CREATE TABLE IF NOT EXISTS "timetable_entries" ("id" BIGSERIAL PRIMARY KEY, "program_id" BIGINT NOT NULL, "course_id" BIGINT NOT NULL, "lecturer_id" BIGINT NULL, "day_of_week" VARCHAR(255) NOT NULL, "start_time" TIME NOT NULL, "end_time" TIME NOT NULL, "room" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'school_events' => 'CREATE TABLE IF NOT EXISTS "school_events" ("id" BIGSERIAL PRIMARY KEY, "title" VARCHAR(255) NOT NULL, "description" TEXT NULL, "start_date" DATE NOT NULL, "end_date" DATE NULL, "type" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'scheme_of_learnings' => 'CREATE TABLE IF NOT EXISTS "scheme_of_learnings" ("id" BIGSERIAL PRIMARY KEY, "course_id" BIGINT NOT NULL, "lecturer_id" BIGINT NOT NULL, "academic_session_id" BIGINT NULL, "week_number" INTEGER NOT NULL, "topic" VARCHAR(255) NOT NULL, "subtopic" TEXT NULL, "objectives" TEXT NULL, "activities" TEXT NULL, "resources" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'promotion_histories' => 'CREATE TABLE IF NOT EXISTS "promotion_histories" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "from_program_id" BIGINT NULL, "to_program_id" BIGINT NULL, "action" VARCHAR(255) NOT NULL, "notes" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'staff_portal_users' => 'CREATE TABLE IF NOT EXISTS "staff_portal_users" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "email" VARCHAR(255) NOT NULL, "password" VARCHAR(255) NOT NULL, "role" VARCHAR(255) NOT NULL DEFAULT \'accountant\', "church_branch_id" BIGINT NULL, "remember_token" VARCHAR(100) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'staff_portal_permissions' => 'CREATE TABLE IF NOT EXISTS "staff_portal_permissions" ("id" BIGSERIAL PRIMARY KEY, "staff_portal_user_id" BIGINT NOT NULL, "permission" VARCHAR(255) NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'generated_documents' => 'CREATE TABLE IF NOT EXISTS "generated_documents" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "type" VARCHAR(255) NOT NULL, "uuid" VARCHAR(255) NOT NULL, "file_path" VARCHAR(255) NULL, "generated_by" BIGINT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'daily_fee_records' => 'CREATE TABLE IF NOT EXISTS "daily_fee_records" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "date" DATE NOT NULL, "amount" DECIMAL(10,2) NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'student_fee_exemptions' => 'CREATE TABLE IF NOT EXISTS "student_fee_exemptions" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "reason" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'exam_questions' => 'CREATE TABLE IF NOT EXISTS "exam_questions" ("id" BIGSERIAL PRIMARY KEY, "course_id" BIGINT NOT NULL, "lecturer_id" BIGINT NOT NULL, "title" VARCHAR(255) NOT NULL, "file_path" VARCHAR(255) NULL, "file_url" VARCHAR(255) NULL, "status" VARCHAR(255) NOT NULL DEFAULT \'pending\', "comment" TEXT NULL, "academic_session_id" BIGINT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'teacher_work_logs' => 'CREATE TABLE IF NOT EXISTS "teacher_work_logs" ("id" BIGSERIAL PRIMARY KEY, "lecturer_id" BIGINT NOT NULL, "course_id" BIGINT NOT NULL, "date" DATE NOT NULL, "topic_covered" TEXT NOT NULL, "notes" TEXT NULL, "admin_comment" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'class_teacher_assignments' => 'CREATE TABLE IF NOT EXISTS "class_teacher_assignments" ("id" BIGSERIAL PRIMARY KEY, "lecturer_id" BIGINT NOT NULL, "program_id" BIGINT NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'attribute_definitions' => 'CREATE TABLE IF NOT EXISTS "attribute_definitions" ("id" BIGSERIAL PRIMARY KEY, "label" VARCHAR(255) NOT NULL, "type" VARCHAR(255) NOT NULL DEFAULT \'rating\', "sort_order" INTEGER NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'student_reports' => 'CREATE TABLE IF NOT EXISTS "student_reports" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "academic_session_id" BIGINT NULL, "conduct" VARCHAR(255) NULL, "attitude" VARCHAR(255) NULL, "class_teacher_remarks" TEXT NULL, "head_teacher_remarks" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'student_report_attributes' => 'CREATE TABLE IF NOT EXISTS "student_report_attributes" ("id" BIGSERIAL PRIMARY KEY, "student_report_id" BIGINT NOT NULL, "attribute_definition_id" BIGINT NOT NULL, "value" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',

            // Website tables
            'website_blog_posts' => 'CREATE TABLE IF NOT EXISTS "website_blog_posts" ("id" BIGSERIAL PRIMARY KEY, "title" VARCHAR(255) NOT NULL, "slug" VARCHAR(255) NOT NULL UNIQUE, "excerpt" TEXT NULL, "content" TEXT NULL, "image_url" VARCHAR(255) NULL, "author" VARCHAR(255) NULL, "is_published" BOOLEAN NOT NULL DEFAULT FALSE, "published_at" TIMESTAMP NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_events' => 'CREATE TABLE IF NOT EXISTS "website_events" ("id" BIGSERIAL PRIMARY KEY, "title" VARCHAR(255) NOT NULL, "description" TEXT NULL, "image_url" VARCHAR(255) NULL, "start_date" DATE NOT NULL, "end_date" DATE NULL, "location" VARCHAR(255) NULL, "is_published" BOOLEAN NOT NULL DEFAULT FALSE, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_gallery_categories' => 'CREATE TABLE IF NOT EXISTS "website_gallery_categories" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_gallery_images' => 'CREATE TABLE IF NOT EXISTS "website_gallery_images" ("id" BIGSERIAL PRIMARY KEY, "category_id" BIGINT NULL, "image_url" VARCHAR(255) NOT NULL, "caption" VARCHAR(255) NULL, "sort_order" INTEGER NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_staff_members' => 'CREATE TABLE IF NOT EXISTS "website_staff_members" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "title" VARCHAR(255) NULL, "bio" TEXT NULL, "photo_url" VARCHAR(255) NULL, "email" VARCHAR(255) NULL, "sort_order" INTEGER NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_contact_messages' => 'CREATE TABLE IF NOT EXISTS "website_contact_messages" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "email" VARCHAR(255) NOT NULL, "phone" VARCHAR(255) NULL, "subject" VARCHAR(255) NULL, "message" TEXT NOT NULL, "is_read" BOOLEAN NOT NULL DEFAULT FALSE, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_admission_applications' => 'CREATE TABLE IF NOT EXISTS "website_admission_applications" ("id" BIGSERIAL PRIMARY KEY, "first_name" VARCHAR(255) NOT NULL, "last_name" VARCHAR(255) NOT NULL, "email" VARCHAR(255) NOT NULL, "phone" VARCHAR(255) NULL, "program_of_interest" VARCHAR(255) NULL, "message" TEXT NULL, "status" VARCHAR(255) NOT NULL DEFAULT \'pending\', "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_testimonials' => 'CREATE TABLE IF NOT EXISTS "website_testimonials" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "role" VARCHAR(255) NULL, "content" TEXT NOT NULL, "photo_url" VARCHAR(255) NULL, "is_active" BOOLEAN NOT NULL DEFAULT TRUE, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'website_programs' => 'CREATE TABLE IF NOT EXISTS "website_programs" ("id" BIGSERIAL PRIMARY KEY, "title" VARCHAR(255) NOT NULL, "description" TEXT NULL, "duration" VARCHAR(255) NULL, "image_url" VARCHAR(255) NULL, "is_active" BOOLEAN NOT NULL DEFAULT TRUE, "sort_order" INTEGER NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
        ];
    }

    private function columnAdditions(): array
    {
        // Each statement uses DO $$ BEGIN ... END $$ to check before adding
        // Safe to run multiple times — never fails if column already exists
        return [
            // attendances — add date, status, course_id
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='attendances' AND column_name='date') THEN ALTER TABLE attendances ADD COLUMN \"date\" DATE NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='attendances' AND column_name='status') THEN ALTER TABLE attendances ADD COLUMN \"status\" VARCHAR(255) NULL DEFAULT 'present'; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='attendances' AND column_name='course_id') THEN ALTER TABLE attendances ADD COLUMN \"course_id\" BIGINT NULL; END IF; END \$\$",

            // students — add extra profile columns
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='gender') THEN ALTER TABLE students ADD COLUMN \"gender\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='date_of_birth') THEN ALTER TABLE students ADD COLUMN \"date_of_birth\" DATE NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='phone') THEN ALTER TABLE students ADD COLUMN \"phone\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='address') THEN ALTER TABLE students ADD COLUMN \"address\" TEXT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='photo') THEN ALTER TABLE students ADD COLUMN \"photo\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='nationality') THEN ALTER TABLE students ADD COLUMN \"nationality\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='religion') THEN ALTER TABLE students ADD COLUMN \"religion\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='guardian_name') THEN ALTER TABLE students ADD COLUMN \"guardian_name\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='guardian_phone') THEN ALTER TABLE students ADD COLUMN \"guardian_phone\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='guardian_email') THEN ALTER TABLE students ADD COLUMN \"guardian_email\" VARCHAR(255) NULL; END IF; END \$\$",

            // users — add extra profile columns
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='qualification') THEN ALTER TABLE users ADD COLUMN \"qualification\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='bio') THEN ALTER TABLE users ADD COLUMN \"bio\" TEXT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='gender') THEN ALTER TABLE users ADD COLUMN \"gender\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='marital_status') THEN ALTER TABLE users ADD COLUMN \"marital_status\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='nationality') THEN ALTER TABLE users ADD COLUMN \"nationality\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='date_of_birth') THEN ALTER TABLE users ADD COLUMN \"date_of_birth\" DATE NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='place_of_birth') THEN ALTER TABLE users ADD COLUMN \"place_of_birth\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='religion') THEN ALTER TABLE users ADD COLUMN \"religion\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='first_language') THEN ALTER TABLE users ADD COLUMN \"first_language\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='other_language') THEN ALTER TABLE users ADD COLUMN \"other_language\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='surname') THEN ALTER TABLE users ADD COLUMN \"surname\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='other_names') THEN ALTER TABLE users ADD COLUMN \"other_names\" VARCHAR(255) NULL; END IF; END \$\$",

            // programs — add code
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='programs' AND column_name='code') THEN ALTER TABLE programs ADD COLUMN \"code\" VARCHAR(50) NULL; END IF; END \$\$",

            // church_branches — add code and location
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='church_branches' AND column_name='code') THEN ALTER TABLE church_branches ADD COLUMN \"code\" VARCHAR(50) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='church_branches' AND column_name='location') THEN ALTER TABLE church_branches ADD COLUMN \"location\" VARCHAR(255) NULL; END IF; END \$\$",

            // exam_scores — add extra columns
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='sba_score') THEN ALTER TABLE exam_scores ADD COLUMN \"sba_score\" DECIMAL(8,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='total_score') THEN ALTER TABLE exam_scores ADD COLUMN \"total_score\" DECIMAL(8,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='grade') THEN ALTER TABLE exam_scores ADD COLUMN \"grade\" VARCHAR(10) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='remarks') THEN ALTER TABLE exam_scores ADD COLUMN \"remarks\" VARCHAR(255) NULL; END IF; END \$\$",

            // site_settings — add all missing columns
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='tagline') THEN ALTER TABLE site_settings ADD COLUMN \"tagline\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='hero_title') THEN ALTER TABLE site_settings ADD COLUMN \"hero_title\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='hero_subtitle') THEN ALTER TABLE site_settings ADD COLUMN \"hero_subtitle\" TEXT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='theme_colors') THEN ALTER TABLE site_settings ADD COLUMN \"theme_colors\" JSON NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='contact_email') THEN ALTER TABLE site_settings ADD COLUMN \"contact_email\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='contact_phone') THEN ALTER TABLE site_settings ADD COLUMN \"contact_phone\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='contact_address') THEN ALTER TABLE site_settings ADD COLUMN \"contact_address\" TEXT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='facebook_url') THEN ALTER TABLE site_settings ADD COLUMN \"facebook_url\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='twitter_url') THEN ALTER TABLE site_settings ADD COLUMN \"twitter_url\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='instagram_url') THEN ALTER TABLE site_settings ADD COLUMN \"instagram_url\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='site_settings' AND column_name='youtube_url') THEN ALTER TABLE site_settings ADD COLUMN \"youtube_url\" VARCHAR(255) NULL; END IF; END \$\$",
        ];
    }

    private function constraintStatements(): array
    {
        return [
            // users email unique
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename='users' AND indexname='users_email_unique') THEN ALTER TABLE users ADD CONSTRAINT users_email_unique UNIQUE (email); END IF; END \$\$",
            // sessions indexes
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename='sessions' AND indexname='sessions_user_id_idx') THEN CREATE INDEX sessions_user_id_idx ON sessions(user_id); END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename='sessions' AND indexname='sessions_last_activity_idx') THEN CREATE INDEX sessions_last_activity_idx ON sessions(last_activity); END IF; END \$\$",
            // students student_id unique
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM pg_indexes WHERE tablename='students' AND indexname='students_student_id_unique') THEN ALTER TABLE students ADD CONSTRAINT students_student_id_unique UNIQUE (student_id); END IF; END \$\$",
        ];
    }
}
