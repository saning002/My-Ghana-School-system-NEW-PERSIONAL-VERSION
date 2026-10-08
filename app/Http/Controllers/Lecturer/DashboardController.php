<?php

namespace App\Http\Controllers\Lecturer;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CourseAssignment;
use App\Models\ExamScore;
use App\Models\Enrollment;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $lecturer = auth()->user();

        $assignedCourses = CourseAssignment::with(['course', 'program'])
            ->where('lecturer_id', $lecturer->id)
            ->get();

        $programIds = $assignedCourses->pluck('program_id')->unique()->filter()->values();
        $courseIds = $assignedCourses->pluck('course_id')->unique()->filter()->values();

        $assignedStudentIds = Enrollment::whereIn('course_id', $courseIds)
            ->whereIn('program_id', $programIds)
            ->pluck('student_id')
            ->unique()
            ->values();

        $students = Student::with('user')
            ->whereIn('id', $assignedStudentIds)
            ->when($lecturer->church_branch_id, fn($q) => $q->where('church_branch_id', $lecturer->church_branch_id))
            ->get();

        $classAssignments = \App\Models\ClassTeacherAssignment::where('lecturer_id', $lecturer->id)->get();
        $classProgramIds = $classAssignments->pluck('program_id')->unique()->filter()->values();

        $classStudentsCount = Student::whereIn('program_id', $classProgramIds)
            ->whereNotIn('status', Student::EXAM_EXCLUDED_STATUSES)
            ->when($lecturer->church_branch_id, fn($q) => $q->where('church_branch_id', $lecturer->church_branch_id))
            ->count();

        $studentStatusDistribution = $students->groupBy('status')
            ->map(fn($group) => $group->count())
            ->toArray();

        $attendanceQuery = Attendance::whereIn('program_id', $classProgramIds)
            ->whereNull('course_id')
            ->when($lecturer->church_branch_id, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $lecturer->church_branch_id)));

        $attendanceTotals = (clone $attendanceQuery)
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        // Set the total attendance card to reflect the class register total students
        $attendanceTotals['class_register_total'] = $classStudentsCount;

        $trendRecords = (clone $attendanceQuery)
            ->where('date', '>=', Carbon::today()->subDays(13)->toDateString())
            ->get(['date', 'status']);

        $grouped = $trendRecords->groupBy(fn($row) => Carbon::parse($row->date)->format('Y-m-d'));

        $curr = Carbon::today()->subDays(13);
        $end  = Carbon::today();

        $attendanceTrend = collect();
        while ($curr->lte($end)) {
            $dStr = $curr->format('Y-m-d');
            $recs = $grouped->get($dStr, collect());
            $attendanceTrend->push([
                'day'     => $curr->format('M d'),
                'present' => $recs->where('status', 'present')->count(),
                'absent'  => $recs->where('status', 'absent')->count(),
            ]);
            $curr->addDay();
        }

        $scoreDistribution = ExamScore::with('course')
            ->whereIn('course_id', $courseIds)
            ->whereIn('program_id', $programIds)
            ->when($lecturer->church_branch_id, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $lecturer->church_branch_id)))
            ->get()
            ->groupBy('course_id')
            ->map(function ($group) {
                return [
                    'count' => $group->count(),
                    'course_name' => $group->first()?->course?->name ?? 'Unknown',
                ];
            });

        $recentAttendances = Attendance::query()
            ->whereIn('program_id', $classProgramIds)
            ->whereNull('course_id')
            ->when($lecturer->church_branch_id, fn($q) => $q->whereHas('student', fn($sq) => $sq->where('church_branch_id', $lecturer->church_branch_id)))
            ->with(['student.user'])
            ->latest('date')
            ->limit(6)
            ->get();

        return view('lecturer.dashboard', compact(
            'assignedCourses',
            'students',
            'studentStatusDistribution',
            'attendanceTotals',
            'attendanceTrend',
            'scoreDistribution',
            'recentAttendances'
        ));
    }
}
