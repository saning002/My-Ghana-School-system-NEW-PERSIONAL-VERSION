<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use App\Models\StaffPortalUser;

class EnsureUserHasRole
{
    /**
     * Map admin route name prefixes → staff portal permission keys.
     * If a staff portal user has the permission, they can access that route group.
     */
    private const STAFF_PERMISSION_MAP = [
        // ── Core ──────────────────────────────────────────────────────────────
        'admin.dashboard'                   => 'dashboard',
        'admin.profile'                     => '__always__',

        // ── Students ──────────────────────────────────────────────────────────
        'admin.students'                    => 'students',
        'admin.id-cards'                    => 'students_id_card',

        // ── Staff & Teachers ──────────────────────────────────────────────────
        'admin.lecturers'                   => 'lecturers',
        'admin.class-teachers'              => 'class_teachers',
        'admin.admins'                      => 'admins',
        'admin.staff-portal'                => 'admins',

        // ── Attendance ────────────────────────────────────────────────────────
        'admin.attendance'                  => 'attendance',

        // ── Exams & Reports ───────────────────────────────────────────────────
        'admin.exams'                       => 'exams',
        'admin.exam-questions'              => 'exam_questions',
        'admin.reports'                     => 'reports',

        // ── Fees & Payments ───────────────────────────────────────────────────
        'admin.fees'                        => 'fees',
        'admin.daily-fees'                  => 'daily_fees',
        'admin.fee-reminders'               => 'fee_reminders',

        // ── Academic Structure ────────────────────────────────────────────────
        'admin.programs'                    => 'programs',
        'admin.courses'                     => 'courses',
        'admin.academic-sessions'           => 'academic_sessions',

        // ── Scheme, Timetable, Calendar ───────────────────────────────────────
        'admin.scheme-of-learning'          => 'scheme_of_learning',
        'admin.timetable'                   => 'timetable',
        'admin.calendar'                    => 'calendar',

        // ── Notifications & Messaging ─────────────────────────────────────────
        'admin.notifications.templates'     => 'message_templates',
        'admin.notifications'               => 'notifications',
        'admin.sms'                         => 'notifications_sms',

        // ── Work Logs & Student Reports ───────────────────────────────────────
        'admin.work-monitoring'             => 'work_logs',
        'admin.student-reports'             => 'student_reports',

        // ── Promotions & Branches ─────────────────────────────────────────────
        'admin.promotions'                  => 'promotions',
        'admin.branches'                    => 'branches',

        // ── Website CMS ───────────────────────────────────────────────────────
        'admin.website'                     => 'website',
        'admin.website-settings'            => 'website_settings',
        'admin.admissions'                  => 'website_applications',

        // ── Settings ──────────────────────────────────────────────────────────
        'admin.settings'                    => 'settings',
        'admin.portal-settings'             => 'portal_settings',

        // ── Subscription ──────────────────────────────────────────────────────
        'admin.subscription'                => 'subscription',

        // ── Reports hub ───────────────────────────────────────────────────────
        'admin.reports.hub'                 => 'reports',
        'admin.reports.fee-statement'       => 'fees_reports',
        'admin.reports.student-academic'    => 'reports_academic',
        'admin.reports.attendance-summary'  => 'reports_attendance',
        'admin.reports.financial'           => 'reports_financial',
    ];

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // ── If no user found, try switching to tenant DB and re-fetching ─────
        // This handles the case where the session has tenant_slug but the
        // default DB connection hasn't been switched yet
        if (!$user && session()->has('tenant_slug')) {
            $slug   = session('tenant_slug');
            $tenant = \App\Models\Tenant::on('pgsql')->where('slug', $slug)->first();
            if ($tenant) {
                \Illuminate\Support\Facades\DB::purge('tenant');
                \Illuminate\Support\Facades\Config::set('database.connections.tenant', $tenant->dbConfig());
                \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');
                $user = $request->user(); // retry after switching DB
            }
        }

        // ── Check normal user auth ────────────────────────────────────────────
        if ($user) {
            $userRole = $user->role ?? 'unknown';
            if ($userRole !== $role) {
                Log::warning("EnsureUserHasRole: User {$user->id} has role '{$userRole}' but required role is '{$role}'");
                abort(403, "Unauthorized Access. Required role: {$role}, your role: {$userRole}");
            }
            return $next($request);
        }

        // ── No regular user — check staff portal session ──────────────────────
        // Staff portal users (accountant, headmaster, etc.) are NOT in the users
        // table. They log in via session. If they have an active session AND their
        // permissions include the section being accessed, let them through.
        if ($role === 'admin' && session()->has('staff_portal_user_id')) {
            $staffUser = StaffPortalUser::with('permissions')
                ->find(session('staff_portal_user_id'));

            if ($staffUser && $staffUser->is_active) {
                // Get the current route name and find the matching permission
                $routeName = $request->route()?->getName() ?? '';
                $allowed   = false;

                foreach (self::STAFF_PERMISSION_MAP as $prefix => $permission) {
                    if (str_starts_with($routeName, $prefix)) {
                        $allowed = ($permission === '__always__') ? true : $staffUser->can_access($permission);
                        break;
                    }
                }

                // dashboard is accessible to anyone with any permission
                if (!$allowed && str_starts_with($routeName, 'admin.dashboard')) {
                    $allowed = $staffUser->permissions()->exists();
                }

                if ($allowed) {
                    // Inject staff user into view so layouts can display their name
                    view()->share('staffPortalUser', $staffUser);
                    // Make auth()->user() return the staff user!
                    \Illuminate\Support\Facades\Auth::shouldUse('staff');
                    \Illuminate\Support\Facades\Auth::guard('staff')->setUser($staffUser);
                    return $next($request);
                }

                // Staff user is logged in but doesn't have this permission
                abort(403, "Your {$staffUser->role_label} account does not have access to this section.");
            }

            // Session exists but user not found — clear stale session
            session()->forget(['staff_portal_user_id','staff_portal_user_name','staff_portal_user_role']);
        }

        // ── No auth at all — redirect to correct login ───────────────────────
        Log::warning('EnsureUserHasRole: No authenticated user found');

        // If this is a school tenant session, redirect to the school's login page
        $tenantSlug = session('tenant_slug');
        if ($tenantSlug) {
            return redirect()->route('school.admin.login', $tenantSlug);
        }

        return redirect()->route('login');
    }
}
