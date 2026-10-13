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
     * Create initial super admin in tenant DB.
     */
    public function createAdmin(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'admin_full_name' => 'required|string|max:255',
            'admin_email'     => 'required|email|max:255',
            'admin_password'  => 'required|string|min:8|confirmed',
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
                'full_name'      => $validated['admin_full_name'],
                'email'          => $validated['admin_email'],
                'password'       => Hash::make($validated['admin_password']),
                'role'           => 'admin',
                'is_super_admin' => true,
                'created_at'     => now(),
                'updated_at'     => now(),
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
     * List admins in tenant DB (JSON for AJAX).
     */
    public function listAdmins(Tenant $tenant)
    {
        $admins = collect();
        try {
            DB::purge('tenant');
            Config::set('database.connections.tenant', $tenant->dbConfig());
            DB::setDefaultConnection('tenant');

            $admins = DB::connection('tenant')->table('users')
                ->where('role', 'admin')
                ->orderByDesc('is_super_admin')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'email', 'is_super_admin', 'created_at']);

            DB::setDefaultConnection('pgsql'); DB::purge('tenant');
        } catch (\Throwable $e) {
            DB::setDefaultConnection('pgsql');
        }

        return response()->json($admins);
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
            'site_settings' => 'CREATE TABLE IF NOT EXISTS "site_settings" ("id" BIGSERIAL PRIMARY KEY, "site_name" VARCHAR(255) NOT NULL DEFAULT \'School\', "site_subtitle" VARCHAR(255) NULL, "site_logo_url" TEXT NULL, "theme" JSON NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'church_branches' => 'CREATE TABLE IF NOT EXISTS "church_branches" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "address" TEXT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'users' => 'CREATE TABLE IF NOT EXISTS "users" ("id" BIGSERIAL PRIMARY KEY, "full_name" VARCHAR(255) NOT NULL, "surname" VARCHAR(255) NULL, "other_names" VARCHAR(255) NULL, "email" VARCHAR(255) NOT NULL, "date_of_birth" DATE NULL, "place_of_birth" VARCHAR(255) NULL, "religion" VARCHAR(255) NULL, "first_language" VARCHAR(255) NULL, "other_language" VARCHAR(255) NULL, "phone" VARCHAR(255) NULL, "address" TEXT NULL, "photo" VARCHAR(255) NULL, "qualification" VARCHAR(255) NULL, "bio" TEXT NULL, "gender" VARCHAR(255) NULL, "marital_status" VARCHAR(255) NULL, "nationality" VARCHAR(255) NULL, "role" VARCHAR(255) NOT NULL DEFAULT \'admin\', "is_super_admin" BOOLEAN NOT NULL DEFAULT FALSE, "church_branch_id" BIGINT NULL, "login_background_url" TEXT NULL, "email_verified_at" TIMESTAMP NULL, "password" VARCHAR(255) NOT NULL, "remember_token" VARCHAR(100) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'programs' => 'CREATE TABLE IF NOT EXISTS "programs" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "description" TEXT NULL, "sequence" INTEGER NOT NULL DEFAULT 0, "church_branch_id" BIGINT NULL, "duration_years" INTEGER NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'courses' => 'CREATE TABLE IF NOT EXISTS "courses" ("id" BIGSERIAL PRIMARY KEY, "name" VARCHAR(255) NOT NULL, "code" VARCHAR(255) NULL, "description" TEXT NULL, "program_id" BIGINT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'students' => 'CREATE TABLE IF NOT EXISTS "students" ("id" BIGSERIAL PRIMARY KEY, "user_id" BIGINT NOT NULL, "student_id" VARCHAR(255) NOT NULL, "program_id" BIGINT NULL, "church_branch_id" BIGINT NULL, "status" VARCHAR(255) NOT NULL DEFAULT \'active\', "year_of_admission" INTEGER NULL, "portal_password" VARCHAR(255) NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'enrollments' => 'CREATE TABLE IF NOT EXISTS "enrollments" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "course_id" BIGINT NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'academic_sessions' => 'CREATE TABLE IF NOT EXISTS "academic_sessions" ("id" BIGSERIAL PRIMARY KEY, "year" VARCHAR(255) NOT NULL, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'academic_periods' => 'CREATE TABLE IF NOT EXISTS "academic_periods" ("id" BIGSERIAL PRIMARY KEY, "academic_session_id" BIGINT NOT NULL, "month" VARCHAR(255) NOT NULL, "is_active" BOOLEAN NOT NULL DEFAULT FALSE, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
            'attendances' => 'CREATE TABLE IF NOT EXISTS "attendances" ("id" BIGSERIAL PRIMARY KEY, "student_id" BIGINT NOT NULL, "academic_period_id" BIGINT NOT NULL, "present_days" INTEGER NOT NULL DEFAULT 0, "absent_days" INTEGER NOT NULL DEFAULT 0, "created_at" TIMESTAMP NULL, "updated_at" TIMESTAMP NULL)',
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
