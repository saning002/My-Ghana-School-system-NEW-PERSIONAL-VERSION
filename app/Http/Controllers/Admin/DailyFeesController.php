<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DailyFeeRecord;
use App\Models\Student;
use App\Models\StudentFeeExemption;
use App\Models\Setting;
use App\Models\Program;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DailyFeesController extends Controller
{
    // ── Mark daily fees ───────────────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
        $today      = $request->date ?? today()->toDateString();
        $programs   = Program::orderBy('sequence')->get();
        $globalRate = (float) Setting::get('daily_fee_rate', 5);

        $query = Student::with(['user', 'program', 'feeExemption'])->forExams();
        if ($request->filled('program_id')) {
            $query->where('program_id', $request->program_id);
        }
        $students = $query->orderBy('student_id')->get();

        // Pre-load program fees keyed by program_id for fast lookup
        $programFees = \App\Models\ProgramFee::all()->keyBy('program_id');

        // Build per-student daily rate:
        // If the student's programme has a feeding_fee configured → use that (programme+feeding mode)
        // Otherwise fall back to the global daily_fee_rate (standalone daily fee mode)
        $studentRates = $students->mapWithKeys(function($s) use ($programFees, $globalRate) {
            $pfee = $s->program_id ? $programFees->get($s->program_id) : null;
            $rate = ($pfee && (float)$pfee->feeding_fee > 0)
                ? (float) $pfee->feeding_fee
                : $globalRate;
            return [$s->id => $rate];
        });

        // Pre-load today's records keyed by student_id
        $paid = DailyFeeRecord::where('date', $today)
            ->whereIn('student_id', $students->pluck('id'))
            ->get()->keyBy('student_id');

        // Summary for today
        $summary = [
            'expected'        => $students->count(),
            'paid'            => $paid->count(),
            'exempted'        => $students->filter(fn($s) => $s->feeExemption?->isActive())->count(),
            'unpaid'          => $students->filter(fn($s) => !$paid->has($s->id) && !$s->feeExemption?->isActive())->count(),
            'total_collected' => $paid->sum('amount'),
        ];

        return view('admin.daily-fees.index', compact(
            'students', 'paid', 'today', 'globalRate', 'studentRates', 'programs', 'summary', 'programFees'
        ));
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'daily_fee_records') || str_contains($e->getMessage(), 'student_fee_exemptions')) {
                return view('admin.daily-fees.index', [
                    'students'      => collect(),
                    'paid'          => collect(),
                    'today'         => today()->toDateString(),
                    'globalRate'    => 5.0,
                    'studentRates'  => collect(),
                    'programFees'   => collect(),
                    'programs'      => Program::orderBy('sequence')->get(),
                    'summary'       => ['expected'=>0,'paid'=>0,'exempted'=>0,'unpaid'=>0,'total_collected'=>0],
                    'migrationNeeded' => true,
                ]);
            }
            throw $e;
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'date'          => 'required|date',
            'student_ids'   => 'required|array',
            'student_ids.*' => 'exists:students,id',
        ]);

        $globalRate  = (float) Setting::get('daily_fee_rate', 5);
        $programFees = \App\Models\ProgramFee::all()->keyBy('program_id');
        $count       = 0;

        // Load students with their programme for per-programme rate lookup
        $students = Student::whereIn('id', $request->student_ids)
            ->with('feeExemption')
            ->get()->keyBy('id');

        foreach ($request->student_ids as $sid) {
            // Skip if student has active full exemption
            $exemption = optional($students->get($sid))->feeExemption;
            if ($exemption && $exemption->isActive() && $exemption->isFull()) continue;

            // Determine daily rate:
            // 1. Exemption override (partial waiver)
            // 2. Programme feeding_fee if configured (programme + feeding option)
            // 3. Global daily_fee_rate (standalone daily fee option)
            if ($exemption && $exemption->isActive() && $exemption->daily_override !== null) {
                $amount = (float) $exemption->daily_override;
            } else {
                $student  = $students->get($sid);
                $programId = $student?->program_id;
                $pfee      = $programId ? $programFees->get($programId) : null;
                $amount    = ($pfee && (float)$pfee->feeding_fee > 0)
                    ? (float) $pfee->feeding_fee
                    : $globalRate;
            }

            DailyFeeRecord::updateOrCreate(
                ['student_id' => $sid, 'date' => $request->date],
                ['amount' => $amount, 'recorded_by' => auth()->id()]
            );
            $count++;
        }

        return back()->with('success', "{$count} student(s) marked as paid for " . Carbon::parse($request->date)->format('M d, Y') . '.');
    }

    public function unmark(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'date'       => 'required|date',
        ]);

        DailyFeeRecord::where('student_id', $request->student_id)
            ->where('date', $request->date)
            ->delete();

        return back()->with('success', 'Payment unmarked.');
    }

    // ── Exemptions ────────────────────────────────────────────────────────────

    public function setExemption(Request $request)
    {
        $request->validate([
            'student_id'     => 'required|exists:students,id',
            'exemption_type' => 'required|in:scholarship,waiver,partial,other',
            'daily_override' => 'nullable|numeric|min:0',
            'reason'         => 'nullable|string|max:300',
            'valid_from'     => 'nullable|date',
            'valid_until'    => 'nullable|date|after_or_equal:valid_from',
        ]);

        StudentFeeExemption::updateOrCreate(
            ['student_id' => $request->student_id],
            [
                'exemption_type' => $request->exemption_type,
                'daily_override' => $request->daily_override ?: null,
                'reason'         => $request->reason,
                'valid_from'     => $request->valid_from,
                'valid_until'    => $request->valid_until,
                'granted_by'     => auth()->id(),
            ]
        );

        $student = Student::with('user')->find($request->student_id);
        return back()->with('success', "Exemption set for {$student->user->full_name}.");
    }

    public function removeExemption(Request $request)
    {
        $request->validate(['student_id' => 'required|exists:students,id']);
        StudentFeeExemption::where('student_id', $request->student_id)->delete();
        return back()->with('success', 'Exemption removed. Student will be charged the standard daily rate.');
    }

    // ── Reports ───────────────────────────────────────────────────────────────

    public function dailyReport(Request $request)
    {
        $date      = $request->date ?? today()->toDateString();
        $dailyRate = (float) Setting::get('daily_fee_rate', 5);
        $programs  = Program::orderBy('sequence')->get();

        $records = DailyFeeRecord::with(['student.user','student.program'])
            ->where('date', $date)
            ->get();

        $allStudents = Student::with(['user','program','feeExemption'])->forExams()->get();
        $paid        = $records->keyBy('student_id');
        $exempted    = $allStudents->filter(fn($s) => $s->feeExemption?->isActive());
        $unpaid      = $allStudents->filter(fn($s) => !$paid->has($s->id) && !$s->feeExemption?->isActive());

        return view('admin.daily-fees.report', compact(
            'date','records','dailyRate','programs','allStudents','paid','exempted','unpaid'
        ));
    }

    public function monthlyReport(Request $request)
    {
        $month     = $request->month ?? now()->format('Y-m');
        [$year,$mon] = explode('-', $month);
        $dailyRate = (float) Setting::get('daily_fee_rate', 5);

        // Daily totals for the month
        $dailyTotals = DailyFeeRecord::selectRaw('date, SUM(amount) as total, COUNT(*) as students_paid')
            ->whereYear('date', $year)->whereMonth('date', $mon)
            ->groupBy('date')->orderBy('date')->get();

        $monthTotal      = $dailyTotals->sum('total');
        $daysWithPayment = $dailyTotals->count();

        return view('admin.daily-fees.monthly', compact(
            'month','year','mon','dailyTotals','monthTotal','daysWithPayment','dailyRate'
        ));
    }

    public function dailyReportPdf(Request $request)
    {
        $this->raisePdfMemory();
        $date    = $request->date ?? today()->toDateString();
        $records = DailyFeeRecord::with(['student.user','student.program'])->where('date',$date)->get();
        $allStudents = Student::with(['user','program','feeExemption'])->forExams()->get();
        $paid        = $records->keyBy('student_id');
        $unpaid      = $allStudents->filter(fn($s) => !$paid->has($s->id) && !$s->feeExemption?->isActive());
        $schoolName  = Setting::get('school_name', config('app.name'));
        $dailyRate   = (float) Setting::get('daily_fee_rate', 5);

        $pdf = Pdf::loadView('pdf.daily_fee_report', compact(
            'date','records','unpaid','schoolName','dailyRate'
        ))->setPaper('a4');

        return $pdf->download('daily_fees_' . $date . '.pdf');
    }

    private function raisePdfMemory(): void
    {
        if ((int) ini_get('memory_limit') < 256) {
            ini_set('memory_limit', '256M');
        }
    }
}
