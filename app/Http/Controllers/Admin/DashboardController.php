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

        // Overall attendance rate — based on status column ('present'/'absent')
        // attendances table has: student_id, date, status (enum: present|absent)
        // No present_days/absent_days columns exist.
        try {
            if ($isBranchAdmin) {
                $totalRows = \DB::selectOne("
                    SELECT COUNT(*) as cnt
                    FROM attendances a
                    INNER JOIN students s ON s.id = a.student_id
                    WHERE s.church_branch_id = ?
                ", [$branchId])->cnt ?? 0;

                $presentRows = \DB::selectOne("
                    SELECT COUNT(*) as cnt
                    FROM attendances a
                    INNER JOIN students s ON s.id = a.student_id
                    WHERE s.church_branch_id = ?
                      AND a.status = 'present'
                ", [$branchId])->cnt ?? 0;
            } else {
                $totalRows = \DB::selectOne("
                    SELECT COUNT(*) as cnt FROM attendances
                ")->cnt ?? 0;

                $presentRows = \DB::selectOne("
                    SELECT COUNT(*) as cnt FROM attendances WHERE status = 'present'
                ")->cnt ?? 0;
            }
            $attendanceRate = $totalRows > 0 ? min(100, round(($presentRows / $totalRows) * 100)) : 0;
        } catch (\Throwable $e) {
            \Log::warning('Attendance rate query failed: ' . $e->getMessage());
            try {
                $q = Attendance::when($isBranchAdmin, fn($q) => $q->whereHas('student',
                    fn($s) => $s->where('church_branch_id', $branchId)));
                $total   = (clone $q)->count();
                $present = (clone $q)->where('status', 'present')->count();
                $attendanceRate = $total > 0 ? min(100, round(($present / $total) * 100)) : 0;
            } catch (\Throwable $e2) {
                $attendanceRate = 0;
            }
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

            // Count rows by status — schema has status enum('present','absent'), no present_days column
            if ($isBranchAdmin) {
                $rows = \DB::select("
                    SELECT
                        a.date,
                        COUNT(CASE WHEN a.status = 'present' THEN 1 END) AS present_count,
                        COUNT(CASE WHEN a.status = 'absent'  THEN 1 END) AS absent_count
                    FROM attendances a
                    INNER JOIN students s ON s.id = a.student_id
                    WHERE a.date >= ?
                      AND s.church_branch_id = ?
                    GROUP BY a.date
                    ORDER BY a.date ASC
                ", [$since, $branchId]);
            } else {
                $rows = \DB::select("
                    SELECT
                        date,
                        COUNT(CASE WHEN status = 'present' THEN 1 END) AS present_count,
                        COUNT(CASE WHEN status = 'absent'  THEN 1 END) AS absent_count
                    FROM attendances
                    WHERE date >= ?
                    GROUP BY date
                    ORDER BY date ASC
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

        // Recent attendances — most recent by date
        $recentAttendances = collect();
        try {
            $recentAttendances = Attendance::with(['student.user', 'student.program'])
                ->when($isBranchAdmin, fn($q) => $q->whereHas('student',
                    fn($s) => $s->where('church_branch_id', $branchId)))
                ->orderByDesc('id')
                ->take(8)
                ->get();
        } catch (\Throwable $e) {
            \Log::warning('Recent attendances query failed: ' . $e->getMessage());
        }

        // ── Recent Activities — unified feed from all system actions ──────
        $recentActivities = $this->buildRecentActivities($isBranchAdmin ? $branchId : null);

        $storageHealth = $this->buildStorageHealth();

        return view('admin.dashboard', compact(
            'stats',
            'attendanceRate',
            'fees',
            'recentStudents',
            'recentAttendances',
            'recentActivities',
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

    /**
     * Build a unified recent activities feed from all system tables.
     * Pulls from students, payments, exam_scores, attendances,
     * notifications, and teacher_work_logs — merged by time.
     */
    private function buildRecentActivities(?int $branchId): \Illuminate\Support\Collection
    {
        $activities = collect();
        $branchWhere = $branchId ? "AND s.church_branch_id = {$branchId}" : '';

        // 1. New student registrations
        try {
            $rows = \DB::select("
                SELECT s.id, s.student_id, u.full_name, s.status, s.created_at,
                       p.name as program_name
                FROM students s
                LEFT JOIN users u ON u.id = s.user_id
                LEFT JOIN programs p ON p.id = s.program_id
                WHERE 1=1 {$branchWhere}
                ORDER BY s.created_at DESC LIMIT 5
            ");
            foreach ($rows as $r) {
                $activities->push([
                    'type'    => 'student',
                    'icon'    => 'fa-user-graduate',
                    'color'   => '#3b82f6',
                    'bg'      => '#eff6ff',
                    'title'   => $r->full_name ?? 'New Student',
                    'detail'  => $r->student_id . ' · ' . ($r->program_name ?? '—'),
                    'badge'   => ucfirst($r->status),
                    'time'    => \Carbon\Carbon::parse($r->created_at),
                ]);
            }
        } catch (\Throwable $e) {}

        // 2. Fee payments
        try {
            $rows = \DB::select("
                SELECT p.id, p.amount_paid, p.payment_method, p.receipt_number,
                       p.created_at, u.full_name, s.student_id
                FROM payments p
                LEFT JOIN students s ON s.id = p.student_id
                LEFT JOIN users u ON u.id = s.user_id
                WHERE 1=1 {$branchWhere}
                ORDER BY p.created_at DESC LIMIT 5
            ");
            foreach ($rows as $r) {
                $activities->push([
                    'type'    => 'payment',
                    'icon'    => 'fa-money-bill-wave',
                    'color'   => '#16a34a',
                    'bg'      => '#f0fdf4',
                    'title'   => 'GHS ' . number_format($r->amount_paid, 2) . ' paid',
                    'detail'  => ($r->full_name ?? 'Student') . ' · ' . ucfirst(str_replace('_', ' ', $r->payment_method ?? 'cash')),
                    'badge'   => $r->receipt_number ?? '',
                    'time'    => \Carbon\Carbon::parse($r->created_at),
                ]);
            }
        } catch (\Throwable $e) {}

        // 3. Exam scores entered
        try {
            $rows = \DB::select("
                SELECT e.id, e.exam_score, e.sba_score, e.created_at,
                       u.full_name, s.student_id, c.name as course_name
                FROM exam_scores e
                LEFT JOIN students s ON s.id = e.student_id
                LEFT JOIN users u ON u.id = s.user_id
                LEFT JOIN courses c ON c.id = e.course_id
                WHERE 1=1 {$branchWhere}
                ORDER BY e.created_at DESC LIMIT 5
            ");
            foreach ($rows as $r) {
                $total = ($r->sba_score ?? 0) + ($r->exam_score ?? 0);
                $activities->push([
                    'type'    => 'exam',
                    'icon'    => 'fa-clipboard-check',
                    'color'   => '#a855f7',
                    'bg'      => '#faf5ff',
                    'title'   => 'Score entered: ' . ($r->full_name ?? 'Student'),
                    'detail'  => ($r->course_name ?? '—') . ' · Total: ' . number_format($total, 1),
                    'badge'   => '',
                    'time'    => \Carbon\Carbon::parse($r->created_at),
                ]);
            }
        } catch (\Throwable $e) {}

        // 4. Attendance marked
        try {
            $rows = \DB::select("
                SELECT a.id, a.status, a.date, a.created_at,
                       u.full_name, c.code as course_code
                FROM attendances a
                LEFT JOIN students s ON s.id = a.student_id
                LEFT JOIN users u ON u.id = s.user_id
                LEFT JOIN courses c ON c.id = a.course_id
                WHERE 1=1 {$branchWhere}
                ORDER BY a.created_at DESC LIMIT 5
            ");
            foreach ($rows as $r) {
                $activities->push([
                    'type'    => 'attendance',
                    'icon'    => $r->status === 'present' ? 'fa-check-circle' : 'fa-times-circle',
                    'color'   => $r->status === 'present' ? '#16a34a' : '#ef4444',
                    'bg'      => $r->status === 'present' ? '#f0fdf4' : '#fef2f2',
                    'title'   => ($r->full_name ?? 'Student') . ' marked ' . $r->status,
                    'detail'  => ($r->course_code ?? '—') . ' · ' . \Carbon\Carbon::parse($r->date)->format('d M'),
                    'badge'   => ucfirst($r->status),
                    'time'    => \Carbon\Carbon::parse($r->created_at),
                ]);
            }
        } catch (\Throwable $e) {}

        // 5. Notifications sent
        try {
            $rows = \DB::select("
                SELECT id, title, recipient_type, created_at
                FROM student_notifications
                ORDER BY created_at DESC LIMIT 3
            ");
            foreach ($rows as $r) {
                $activities->push([
                    'type'    => 'notification',
                    'icon'    => 'fa-bell',
                    'color'   => '#f59e0b',
                    'bg'      => '#fffbeb',
                    'title'   => 'Notification: ' . \Illuminate\Support\Str::limit($r->title, 40),
                    'detail'  => 'Sent to ' . ucfirst($r->recipient_type ?? 'students'),
                    'badge'   => '',
                    'time'    => \Carbon\Carbon::parse($r->created_at),
                ]);
            }
        } catch (\Throwable $e) {}

        // 6. Teacher work logs
        try {
            $rows = \DB::select("
                SELECT w.id, w.topic_covered, w.date, w.created_at,
                       u.full_name, c.code as course_code
                FROM teacher_work_logs w
                LEFT JOIN users u ON u.id = w.lecturer_id
                LEFT JOIN courses c ON c.id = w.course_id
                ORDER BY w.created_at DESC LIMIT 3
            ");
            foreach ($rows as $r) {
                $activities->push([
                    'type'    => 'worklog',
                    'icon'    => 'fa-book',
                    'color'   => '#0891b2',
                    'bg'      => '#ecfeff',
                    'title'   => ($r->full_name ?? 'Teacher') . ' logged work',
                    'detail'  => ($r->course_code ?? '—') . ' · ' . \Illuminate\Support\Str::limit($r->topic_covered, 30),
                    'badge'   => '',
                    'time'    => \Carbon\Carbon::parse($r->created_at),
                ]);
            }
        } catch (\Throwable $e) {}

        // Sort by time descending and take top 12
        return $activities->sortByDesc('time')->take(12)->values();
    }
}
