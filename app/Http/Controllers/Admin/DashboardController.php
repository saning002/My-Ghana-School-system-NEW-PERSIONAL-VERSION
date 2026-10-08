<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Program;
use App\Models\Course;
use App\Models\Attendance;
use App\Services\FeeCalculationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function __construct(private FeeCalculationService $feeService) {}

    public function index()
    {
        // Guard: ensure user is authenticated
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $isBranchAdmin = auth()->user()->isBranchAdmin();
        $branchId = $isBranchAdmin ? auth()->user()->church_branch_id : null;

        $lecturerCount = $this->countLecturers($isBranchAdmin ? $branchId : null);

        $stats = [
            'total_students'  => $isBranchAdmin ? Student::where('church_branch_id', $branchId)->count() : Student::count(),
            'active_students' => $isBranchAdmin ? Student::where('church_branch_id', $branchId)->where('status', 'active')->count() : Student::where('status', 'active')->count(),
            'total_lecturers' => $lecturerCount,
            'total_programs'  => Program::count(),
            'total_courses'   => Course::count(),
        ];

        // Overall attendance rate — deduplicated per student per date using a safe subquery
        try {
            if ($isBranchAdmin) {
                $totalDeduped = \DB::selectOne("
                    SELECT COUNT(*) as cnt FROM (
                        SELECT student_id, date
                        FROM attendances
                        INNER JOIN students ON students.id = attendances.student_id
                        WHERE students.church_branch_id = ?
                        GROUP BY student_id, date
                    ) sub
                ", [$branchId])->cnt ?? 0;

                $presentDeduped = \DB::selectOne("
                    SELECT COUNT(*) as cnt FROM (
                        SELECT student_id, date
                        FROM attendances
                        INNER JOIN students ON students.id = attendances.student_id
                        WHERE students.church_branch_id = ?
                        GROUP BY student_id, date
                        HAVING MAX(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) = 1
                    ) sub
                ", [$branchId])->cnt ?? 0;
            } else {
                $totalDeduped = \DB::selectOne("
                    SELECT COUNT(*) as cnt FROM (
                        SELECT student_id, date FROM attendances GROUP BY student_id, date
                    ) sub
                ")->cnt ?? 0;

                $presentDeduped = \DB::selectOne("
                    SELECT COUNT(*) as cnt FROM (
                        SELECT student_id, date FROM attendances
                        GROUP BY student_id, date
                        HAVING MAX(CASE WHEN status = 'present' THEN 1 ELSE 0 END) = 1
                    ) sub
                ")->cnt ?? 0;
            }
            $attendanceRate = $totalDeduped > 0 ? round(($presentDeduped / $totalDeduped) * 100) : 0;
        } catch (\Throwable $e) {
            \Log::warning('Attendance rate query failed: ' . $e->getMessage());
            // Simple fallback
            $total = Attendance::when($isBranchAdmin, fn($q) => $q->whereHas('student', fn($s) => $s->where('church_branch_id', $branchId)))->count();
            $present = Attendance::where('status', 'present')->when($isBranchAdmin, fn($q) => $q->whereHas('student', fn($s) => $s->where('church_branch_id', $branchId)))->count();
            $attendanceRate = $total > 0 ? round(($present / $total) * 100) : 0;
        }

        $fees = $this->feeService->getSummary($isBranchAdmin ? $branchId : null);

        $statusDistribution = Student::select('status', DB::raw('count(*) as total'))
            ->when($isBranchAdmin, fn($q) => $q->where('church_branch_id', $branchId))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $programEnrollment = Program::withCount(['students' => fn($q) => $isBranchAdmin ? $q->where('church_branch_id', $branchId) : $q])
            ->orderBy('sequence')
            ->get();

        $attendanceTrend = collect();
        try {
            $since = now()->subDays(13)->toDateString();

            // Raw SQL subquery — works on PostgreSQL and MySQL without mergeBindings
            if ($isBranchAdmin) {
                $rows = \DB::select("
                    SELECT date,
                        SUM(CASE WHEN max_present = 1 THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN max_present = 0 THEN 1 ELSE 0 END) as absent_count
                    FROM (
                        SELECT attendances.student_id, attendances.date,
                            MAX(CASE WHEN attendances.status = 'present' THEN 1 ELSE 0 END) as max_present
                        FROM attendances
                        INNER JOIN students ON students.id = attendances.student_id
                        WHERE attendances.date >= ?
                          AND students.church_branch_id = ?
                        GROUP BY attendances.student_id, attendances.date
                    ) sub
                    GROUP BY date
                ", [$since, $branchId]);
            } else {
                $rows = \DB::select("
                    SELECT date,
                        SUM(CASE WHEN max_present = 1 THEN 1 ELSE 0 END) as present_count,
                        SUM(CASE WHEN max_present = 0 THEN 1 ELSE 0 END) as absent_count
                    FROM (
                        SELECT student_id, date,
                            MAX(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as max_present
                        FROM attendances
                        WHERE date >= ?
                        GROUP BY student_id, date
                    ) sub
                    GROUP BY date
                ", [$since]);
            }

            $byDate = collect($rows)->keyBy('date');

            $curr = \Carbon\Carbon::today()->subDays(13);
            $end  = \Carbon\Carbon::today();
            while ($curr->lte($end)) {
                $d   = $curr->format('Y-m-d');
                $row = $byDate->get($d);
                $attendanceTrend->push([
                    'day'     => $curr->format('M d'),
                    'present' => $row ? (int) $row->present_count : 0,
                    'absent'  => $row ? (int) $row->absent_count  : 0,
                ]);
                $curr->addDay();
            }
        } catch (\Throwable $e) {
            \Log::error('Attendance trend query failed: ' . $e->getMessage());
            $attendanceTrend = collect(range(13, 0))->map(fn($d) => [
                'day'     => \Carbon\Carbon::now()->subDays($d)->format('M d'),
                'present' => 0,
                'absent'  => 0,
            ])->values();
        }

        $recentStudents = $isBranchAdmin
            ? Student::with(['user', 'program'])->where('church_branch_id', $branchId)->latest()->take(5)->get()
            : Student::with(['user', 'program'])->latest()->take(5)->get();

        // Recent attendances — one record per student per date (most recent dates first)
        // Uses the highest-priority status: present beats absent for the same student+date
        $recentAttendances = Attendance::with(['student.user', 'student.program', 'course'])
            ->when($isBranchAdmin, fn($q) => $q->whereHas('student',
                fn($s) => $s->where('church_branch_id', $branchId)))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get()
            // Deduplicate: keep one entry per student+date, preferring 'present'
            ->groupBy(fn($a) => $a->student_id . '_' . $a->date)
            ->map(fn($group) => $group->sortByDesc(fn($a) => $a->status === 'present' ? 1 : 0)->first())
            ->sortByDesc(fn($a) => $a->date . '_' . $a->id)
            ->take(8)
            ->values();

        $storageHealth = $this->buildStorageHealth();

        return view('admin.dashboard', compact(
            'stats',
            'attendanceRate',
            'fees',
            'recentStudents',
            'recentAttendances',
            'storageHealth',
            'statusDistribution',
            'programEnrollment',
            'attendanceTrend'
        ));
    }

    private function countLecturers(?int $branchId = null): int
    {
        $query = User::query()
            ->where(function($q) {
                // Check all possible role variations
                $q->whereIn('role', ['lecturer', 'lecturers', 'teacher'])
                  ->orWhere('role', 'like', '%lecturer%')
                  ->orWhere('role', 'like', '%teacher%');
            });

        if ($branchId) {
            $query->where('church_branch_id', $branchId);
        }

        return $query->count();
    }

    private function buildStorageHealth(): array
    {
        $publicDiskPath = Storage::disk('public')->path('');
        $uploadsPath = public_path('uploads');

        if (! File::exists($publicDiskPath)) {
            File::ensureDirectoryExists($publicDiskPath, 0755, true);
        }

        if (! File::exists($uploadsPath)) {
            File::ensureDirectoryExists($uploadsPath, 0755, true);
        }

        return [
            'public_disk_path' => $publicDiskPath,
            'public_disk_writable' => File::isWritable($publicDiskPath),
            'uploads_path' => $uploadsPath,
            'uploads_writable' => File::isWritable($uploadsPath),
        ];
    }
}
