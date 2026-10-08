<?php

namespace App\Http\Controllers\StaffPortal;

use App\Http\Controllers\Controller;
use App\Models\StaffPortalUser;
use App\Models\Student;
use App\Models\Attendance;
use App\Models\AcademicSession;
use App\Services\FeeCalculationService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private FeeCalculationService $feeService) {}

    public function index()
    {
        $staffUser = $this->getStaffUser();
        if (! $staffUser) return redirect()->route('staff-portal.login');

        $perms    = $staffUser->permission_list;
        $branchId = $staffUser->church_branch_id;

        // Build summary data based on permissions
        $stats = [];
        if (in_array('students', $perms) || in_array('dashboard', $perms)) {
            $stats['total_students']  = \App\Models\Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->count();
            $stats['active_students'] = \App\Models\Student::when($branchId, fn($q)=>$q->where('church_branch_id',$branchId))->where('status','active')->count();
        }
        if (in_array('fees', $perms) || in_array('dashboard', $perms)) {
            $stats['fees'] = $this->feeService->getSummary($branchId);
        }
        if (in_array('attendance', $perms) || in_array('dashboard', $perms)) {
            $q = \App\Models\Attendance::query()
                ->when($branchId, fn($q)=>$q->whereHas('student',fn($sq)=>$sq->where('church_branch_id',$branchId)));
            $total   = $q->count();
            $present = (clone $q)->where('status','present')->count();
            $stats['attendance_rate'] = $total > 0 ? round($present/$total*100) : 0;
        }

        $activePeriod = AcademicSession::activePeriod();

        // Build permission-filtered quick links pointing to staff-portal routes
        $allLinks = [
            'students'   => ['href'=>route('staff-portal.students'),    'icon'=>'fa-user-graduate',  'label'=>'Students',     'bg'=>'#fef9c3','ic'=>'#92680a'],
            'attendance' => ['href'=>route('staff-portal.attendance'),   'icon'=>'fa-clipboard-check','label'=>'Attendance',   'bg'=>'#dcfce7','ic'=>'#166534'],
            'exams'      => ['href'=>route('admin.exams.index'),         'icon'=>'fa-file-alt',        'label'=>'Exam Scores',  'bg'=>'#dbeafe','ic'=>'#1e40af'],
            'fees'       => ['href'=>route('staff-portal.fees'),         'icon'=>'fa-coins',           'label'=>'Fees',         'bg'=>'#fef3c7','ic'=>'#92400e'],
            'daily_fees' => ['href'=>route('staff-portal.daily-fees'),   'icon'=>'fa-calendar-check',  'label'=>'Daily Fees',   'bg'=>'#f0fdf4','ic'=>'#166534'],
            'reports'    => ['href'=>route('staff-portal.reports'),      'icon'=>'fa-chart-bar',       'label'=>'Reports',      'bg'=>'#fce7f3','ic'=>'#9d174d'],
            'timetable'  => ['href'=>route('staff-portal.timetable'),    'icon'=>'fa-table-cells',     'label'=>'Timetable',    'bg'=>'#f5f3ff','ic'=>'#6d28d9'],
            'calendar'   => ['href'=>route('staff-portal.calendar'),     'icon'=>'fa-calendar-days',   'label'=>'Calendar',     'bg'=>'#eff6ff','ic'=>'#1d4ed8'],
            'programs'   => ['href'=>route('admin.programs.index'),      'icon'=>'fa-graduation-cap',  'label'=>'Programs',     'bg'=>'#fdf2f8','ic'=>'#9d174d'],
            'courses'    => ['href'=>route('admin.courses.index'),       'icon'=>'fa-book-open',       'label'=>'Courses',      'bg'=>'#ecfdf5','ic'=>'#065f46'],
        ];
        $links = array_filter($allLinks, fn($k) => in_array($k, $perms), ARRAY_FILTER_USE_KEY);

        return view('staff-portal.dashboard', compact('staffUser','stats','perms','activePeriod','links','branchId'));
    }

    private function getStaffUser(): ?StaffPortalUser
    {
        $id = session('staff_portal_user_id');
        if (! $id) return null;
        return StaffPortalUser::with('permissions')->find($id);
    }
}
