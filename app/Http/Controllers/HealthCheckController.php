<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class HealthCheckController extends Controller
{
    public function check(Request $request)
    {
        $slug = $request->query('tenant', 'saning-academy');
        $results = [];
        $pass = 0;
        $fail = 0;

        // ── 1. CENTRAL DB ─────────────────────────────────────────────────
        $results['central_db'] = $this->test('Central DB Connection', function () {
            DB::connection('pgsql')->getPdo();
            return 'Connected';
        }, $pass, $fail);

        $results['tenants_table'] = $this->test('Tenants table exists', function () {
            return DB::connection('pgsql')->getSchemaBuilder()->hasTable('tenants') ? 'OK' : 'MISSING';
        }, $pass, $fail);

        $results['owner_users'] = $this->test('Owner users table', function () {
            $count = DB::connection('pgsql')->table('owner_users')->count();
            return "{$count} owner(s)";
        }, $pass, $fail);

        $results['messaging_credits'] = $this->test('Messaging credits table', function () {
            return DB::connection('pgsql')->getSchemaBuilder()->hasTable('messaging_credits') ? 'OK' : 'MISSING';
        }, $pass, $fail);

        $results['paystack_transactions'] = $this->test('Paystack transactions table', function () {
            return DB::connection('pgsql')->getSchemaBuilder()->hasTable('paystack_transactions') ? 'OK' : 'MISSING';
        }, $pass, $fail);

        // ── 2. TENANT RESOLUTION ──────────────────────────────────────────
        $tenant = Tenant::on('pgsql')->where('slug', $slug)->first();

        $results['tenant_found'] = $this->test("Tenant '{$slug}' found", function () use ($tenant) {
            return $tenant ? "YES: {$tenant->name}" : 'NOT FOUND';
        }, $pass, $fail);

        if (!$tenant) {
            return response()->json([
                'pass' => $pass, 'fail' => $fail,
                'results' => $results,
                'error' => "Tenant '{$slug}' not found — cannot run further tests",
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // ── 3. TENANT DB CONNECTION ────────────────────────────────────────
        DB::purge('tenant');
        Config::set('database.connections.tenant', $tenant->dbConfig());
        DB::setDefaultConnection('tenant');

        $results['tenant_db'] = $this->test('Tenant DB Connection', function () {
            DB::connection('tenant')->getPdo();
            return 'Connected to ' . config('database.connections.tenant.database');
        }, $pass, $fail);

        // ── 4. TENANT TABLES ──────────────────────────────────────────────
        $requiredTables = [
            'users', 'students', 'programs', 'courses', 'enrollments',
            'academic_sessions', 'academic_periods', 'attendances',
            'exam_scores', 'payments', 'site_settings', 'settings',
            'church_branches', 'course_assignments', 'student_notifications',
            'staff_portal_users', 'staff_portal_permissions',
            'program_fees', 'student_fees', 'term_fee_bills', 'fee_reminders',
            'website_programs', 'website_blog_posts', 'website_events',
            'website_testimonials', 'website_staff_members', 'website_contact_messages',
            'website_admission_applications', 'website_gallery_categories',
            'website_gallery_images', 'sessions', 'cache',
        ];

        foreach ($requiredTables as $table) {
            $results["table_{$table}"] = $this->test("Table: {$table}", function () use ($table) {
                return DB::getSchemaBuilder()->hasTable($table) ? 'OK' : 'MISSING';
            }, $pass, $fail);
        }

        // ── 5. KEY COLUMNS ON STUDENTS ────────────────────────────────────
        $studentCols = ['user_id', 'student_id', 'program_id', 'church_branch_id', 'status',
            'is_approved', 'password', 'admission_date', 'enrollment_year', 'study_mode',
            'results_blocked', 'class_applying_for', 'guardian_name', 'guardian_phone',
            'father_name', 'mother_name', 'pickup_name', 'consent_signed',
            'admission_fee_paid', 'background_photo', 'signal', 'position'];

        $missingStudentCols = [];
        foreach ($studentCols as $col) {
            if (!DB::getSchemaBuilder()->hasColumn('students', $col)) {
                $missingStudentCols[] = $col;
            }
        }
        $results['student_columns'] = $this->test('Student columns complete', function () use ($missingStudentCols) {
            return empty($missingStudentCols) ? 'All present' : 'MISSING: ' . implode(', ', $missingStudentCols);
        }, $pass, $fail);

        // ── 6. KEY COLUMNS ON SITE_SETTINGS ──────────────────────────────
        $siteCols = ['site_name', 'tagline', 'hero_title', 'theme_colors', 'website_theme',
            'hero_bg_type', 'hero_bg_image', 'hero_layout', 'logo_path',
            'nav_brand_sub', 'footer_tagline', 'footer_copyright'];

        $missingSiteCols = [];
        foreach ($siteCols as $col) {
            if (!DB::getSchemaBuilder()->hasColumn('site_settings', $col)) {
                $missingSiteCols[] = $col;
            }
        }
        $results['site_settings_columns'] = $this->test('Site settings columns complete', function () use ($missingSiteCols) {
            return empty($missingSiteCols) ? 'All present' : 'MISSING: ' . implode(', ', $missingSiteCols);
        }, $pass, $fail);

        // ── 7. KEY COLUMNS ON STAFF_PORTAL_USERS ───────────────────────────
        $staffCols = ['full_name', 'email', 'password', 'role', 'phone', 'is_active'];
        $missingStaffCols = [];
        foreach ($staffCols as $col) {
            if (!DB::getSchemaBuilder()->hasColumn('staff_portal_users', $col)) {
                $missingStaffCols[] = $col;
            }
        }
        $results['staff_portal_columns'] = $this->test('Staff portal columns complete', function () use ($missingStaffCols) {
            return empty($missingStaffCols) ? 'All present' : 'MISSING: ' . implode(', ', $missingStaffCols);
        }, $pass, $fail);

        // ── 8. KEY COLUMNS ON STUDENT_NOTIFICATIONS ────────────────────────
        $notifCols = ['audience', 'type', 'recipient_type', 'lecturer_id'];
        $missingNotifCols = [];
        foreach ($notifCols as $col) {
            if (!DB::getSchemaBuilder()->hasColumn('student_notifications', $col)) {
                $missingNotifCols[] = $col;
            }
        }
        $results['notification_columns'] = $this->test('Notification columns complete', function () use ($missingNotifCols) {
            return empty($missingNotifCols) ? 'All present' : 'MISSING: ' . implode(', ', $missingNotifCols);
        }, $pass, $fail);

        // ── 9. DATA EXISTS ─────────────────────────────────────────────────
        $results['users_count'] = $this->test('Users in tenant DB', function () {
            $c = DB::table('users')->count();
            return $c > 0 ? "{$c} users" : 'EMPTY';
        }, $pass, $fail);

        $results['students_count'] = $this->test('Students in tenant DB', function () {
            $c = DB::table('students')->count();
            return $c > 0 ? "{$c} students" : 'EMPTY';
        }, $pass, $fail);

        $results['programs_count'] = $this->test('Programs in tenant DB', function () {
            $c = DB::table('programs')->count();
            return $c > 0 ? "{$c} programs" : 'EMPTY';
        }, $pass, $fail);

        $results['site_settings_row'] = $this->test('Site settings row exists', function () {
            $row = DB::table('site_settings')->first();
            return $row ? $row->site_name ?? 'Row exists' : 'NO ROW';
        }, $pass, $fail);

        $results['academic_sessions'] = $this->test('Academic sessions exist', function () {
            $c = DB::table('academic_sessions')->count();
            return $c > 0 ? "{$c} sessions" : 'EMPTY';
        }, $pass, $fail);

        // ── 10. MESSAGING CREDITS ──────────────────────────────────────────
        $results['messaging_credits_balance'] = $this->test('Messaging credits balance', function () use ($tenant) {
            DB::setDefaultConnection('pgsql');
            $credit = DB::table('messaging_credits')->where('tenant_id', $tenant->id)->first();
            DB::setDefaultConnection('tenant');
            return $credit ? "SMS: {$credit->sms_credits}, WA: {$credit->whatsapp_credits}" : 'NO CREDIT RECORD';
        }, $pass, $fail);

        // ── 11. WEBSITE SETTINGS ───────────────────────────────────────────
        $results['website_theme'] = $this->test('Website theme saved', function () {
            $row = DB::table('site_settings')->first();
            if (!$row) return 'NO SITE SETTINGS ROW';
            $theme = $row->website_theme ?? $row->theme_colors ?? null;
            return $theme ? 'Theme configured' : 'NO THEME';
        }, $pass, $fail);

        // ── 12. EXAM SCORES ────────────────────────────────────────────────
        $results['exam_scores'] = $this->test('Exam scores exist', function () {
            $c = DB::table('exam_scores')->count();
            return $c > 0 ? "{$c} scores" : 'EMPTY';
        }, $pass, $fail);

        // ── 13. ATTENDANCE ─────────────────────────────────────────────────
        $results['attendance'] = $this->test('Attendance records exist', function () {
            $c = DB::table('attendances')->count();
            return $c > 0 ? "{$c} records" : 'EMPTY';
        }, $pass, $fail);

        // ── 14. FEES ──────────────────────────────────────────────────────
        $results['term_fee_bills'] = $this->test('Term fee bills exist', function () {
            $c = DB::table('term_fee_bills')->count();
            return $c > 0 ? "{$c} bills" : 'EMPTY';
        }, $pass, $fail);

        $results['payments'] = $this->test('Payments exist', function () {
            $c = DB::table('payments')->count();
            return $c > 0 ? "{$c} payments" : 'EMPTY';
        }, $pass, $fail);

        // ── 15. STAFF PORTAL USERS ─────────────────────────────────────────
        $results['staff_portal_users'] = $this->test('Staff portal users', function () {
            $c = DB::table('staff_portal_users')->count();
            return "{$c} staff user(s)";
        }, $pass, $fail);

        // Reset
        DB::setDefaultConnection('pgsql');

        // ── SUMMARY ───────────────────────────────────────────────────────
        return response()->json([
            'tenant' => $tenant->name,
            'summary' => "{$pass} passed, {$fail} failed",
            'pass' => $pass,
            'fail' => $fail,
            'results' => $results,
        ], 200, [], JSON_PRETTY_PRINT);
    }

    private function test(string $name, callable $fn, int &$pass, int &$fail): array
    {
        try {
            $result = $fn();
            $ok = !str_contains(strtolower($result), 'missing') &&
                  !str_contains(strtolower($result), 'not found') &&
                  !str_contains(strtolower($result), 'no row') &&
                  !str_contains(strtolower($result), 'no credit') &&
                  !str_contains(strtolower($result), 'no theme') &&
                  !str_contains(strtolower($result), 'empty');
            if ($ok) $pass++; else $fail++;
            return ['status' => $ok ? 'PASS' : 'FAIL', 'detail' => $result];
        } catch (\Throwable $e) {
            $fail++;
            return ['status' => 'FAIL', 'detail' => $e->getMessage()];
        }
    }
}
