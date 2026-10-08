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
        'admin.dashboard'           => 'dashboard',
        'admin.students'            => 'students',
        'admin.lecturers'           => 'lecturers',
        'admin.class-teachers'      => 'class_teachers',
        'admin.work-monitoring'     => 'work_logs',        'admin.student-reports'     => 'student_reports',
        'admin.programs'            => 'programs',
        'admin.courses'             => 'courses',
        'admin.attendance'          => 'attendance',
        'admin.academic-sessions'   => 'academic_sessions',
        'admin.timetable'           => 'timetable',
        'admin.scheme-of-learning'  => 'scheme_of_learning',
        'admin.calendar'            => 'calendar',
        'admin.exams'               => 'exams',
        'admin.exam-questions'      => 'exam_questions',
        'admin.fees'                => 'fees',
        'admin.daily-fees'          => 'daily_fees',
        'admin.reports'             => 'reports',
        'admin.notifications'       => 'notifications',
        'admin.promotions'          => 'promotions',
        'admin.branches'            => 'branches',
        'admin.settings'            => 'settings',
        'admin.profile'             => '__always__',
    ];

    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

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

        // ── No auth at all — redirect to login ───────────────────────────────
        Log::warning('EnsureUserHasRole: No authenticated user found');
        return redirect()->route('login');
    }
}
