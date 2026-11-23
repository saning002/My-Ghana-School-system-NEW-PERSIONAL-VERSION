<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\StoreProgramFeeRequest;
use App\Models\Payment;
use App\Models\Program;
use App\Models\ProgramFee;
use App\Models\StudentFee;
use App\Models\Student;
use App\Exports\FeeTemplateExport;
use App\Imports\FeeImport;
use App\Services\FeeCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class FeesController extends Controller
{
    public function __construct(private FeeCalculationService $feeService) {}

    public function index()
    {
        try {
            $programs = Program::with('programFees')->orderBy('sequence')->get();
            $students = Student::with(['user', 'program', 'payments'])->latest()->paginate(15);

            try { $students->load('customFee'); } catch (\Throwable $e) {}

            $global         = $this->feeService->getSummary();
            $branchSummaries = [];
            $churchBranches = \App\Models\ChurchBranch::orderBy('name')->get();
            foreach ($churchBranches as $branch) {
                $branchSummaries[] = ['branch' => $branch, 'summary' => $this->feeService->getSummary($branch->id)];
            }

            // Fee mode settings
            $feeMode    = \App\Models\Setting::get('fee_mode', 'program_fees');
            $dailyRate  = (float) \App\Models\Setting::get('daily_fee_rate', 5);

            return view('admin.fees.index', compact('programs', 'students', 'global', 'branchSummaries', 'feeMode', 'dailyRate'));
        } catch (\Throwable $e) {
            \Log::error('FeesController@index error: ' . $e->getMessage());
            return response()->view('errors.500', ['message' => 'Fees page failed to load: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Save fee mode (program_fees or daily_fees) and daily rate.
     */
    public function saveFeeMode(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'fee_mode'       => 'required|in:program_fees,program_fees_with_feeding,daily_fees',
            'daily_fee_rate' => 'nullable|numeric|min:0|max:9999',
        ]);
        \App\Models\Setting::set('fee_mode',       $request->fee_mode);
        \App\Models\Setting::set('daily_fee_rate', $request->daily_fee_rate ?? 5);
        $modeLabel = match($request->fee_mode) {
            'daily_fees'                  => 'Daily Fees',
            'program_fees_with_feeding'   => 'Program + Feeding Fees',
            default                       => 'Program Fees',
        };
        return back()->with('success', 'Fee mode updated to: ' . $modeLabel);
    }

    public function template(Request $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;
        $studentType = $request->query('student_type', 'all');
        $studentType = in_array($studentType, ['all', 'precollege', 'first_semester']) ? $studentType : 'all';

        $students = Student::with(['user', 'program.programFees'])
            ->when($branchId, fn($query) => $query->where('church_branch_id', $branchId))
            ->when($studentType === 'precollege', fn($query) => $query->whereHas('program', fn($q) => $q->where('sequence', 1)->orWhere('name', 'like', '%Pre-College%')))
            ->when($studentType === 'first_semester', fn($query) => $query->whereHas('program', fn($q) => $q->where('sequence', 2)->orWhere('name', 'like', '%First Semester%')))
            ->orderBy('student_id')
            ->get();

        $filename = 'Fees_Template_' . (
            $studentType === 'precollege' ? 'PreCollege_' : (
                $studentType === 'first_semester' ? 'FirstSemester_' : 'All_'
            )
        ) . date('Ymd') . '.xlsx';

        return Excel::download(new FeeTemplateExport($students), $filename);
    }

    public function import(Request $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;

        $request->validate([
            'fees_file' => 'required|file|mimes:xlsx,csv,xls',
        ]);

        try {
            Excel::import(new FeeImport($branchId), $request->file('fees_file'));

            return redirect()->route('admin.fees.index')
                ->with('success', 'Fees file imported successfully.');
        } catch (\Throwable $e) {
            return back()->withErrors(['fees_file' => 'Failed to import fees file: ' . $e->getMessage()]);
        }
    }

    public function setFee(StoreProgramFeeRequest $request)
    {
        $branchId = auth()->check() && auth()->user()->isBranchAdmin() ? auth()->user()->church_branch_id : null;

        ProgramFee::updateOrCreate(
            ['program_id' => $request->program_id, 'church_branch_id' => $branchId],
            [
                'amount'      => $request->amount,
                'exam_fee'    => $request->exam_fee ?? 0.00,
                'feeding_fee' => $request->feeding_fee ?? 0.00,
            ]
        );
        return back()->with('success', 'Program fee configurations updated successfully.');
    }

    public function store(StorePaymentRequest $request)
    {
        Payment::create($request->validated());
        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return back()->with('success', 'Payment removed successfully.');
    }

    public function resetStudent(Student $student)
    {
        $count = $student->payments()->count();
        $student->payments()->delete();
        
        $student->loadMissing('user');
        $studentName = $student->user?->full_name ?? 'this student';
        return back()->with('success', "Fees reset successfully. Removed {$count} payment record(s) for {$studentName}.");
    }

    public function resetBulk(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $studentIds = $request->input('student_ids');
        $totalPayments = 0;
        $students = Student::whereIn('id', $studentIds)->get();

        foreach ($students as $student) {
            $count = $student->payments()->count();
            $totalPayments += $count;
            $student->payments()->delete();
        }

        $studentCount = count($studentIds);
        return back()->with('success', "Fees reset successfully for {$studentCount} student(s). Removed {$totalPayments} payment record(s).");
    }

    public function setStudentFee(Student $student, Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0|max:999999.99',
            'exam_fee' => 'nullable|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:500',
        ]);

        $validated['exam_fee'] = $validated['exam_fee'] ?? 0;

        StudentFee::updateOrCreate(
            ['student_id' => $student->id],
            $validated
        );

        $student->loadMissing('user');
        $studentName = $student->user?->full_name ?? 'this student';
        return back()->with('success', "Custom fee set for {$studentName}: GH₵ " . number_format($validated['amount'] + $validated['exam_fee'], 2));
    }

    public function setBulkFees(Request $request)
    {
        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'amount' => 'required|numeric|min:0|max:999999.99',
            'exam_fee' => 'nullable|numeric|min:0|max:999999.99',
            'notes' => 'nullable|string|max:500',
        ]);

        $studentIds = $validated['student_ids'];
        $amount = $validated['amount'];
        $examFee = $validated['exam_fee'] ?? 0;
        $notes = $validated['notes'] ?? null;

        $students = Student::whereIn('id', $studentIds)->get();

        foreach ($students as $student) {
            StudentFee::updateOrCreate(
                ['student_id' => $student->id],
                [
                    'amount' => $amount,
                    'exam_fee' => $examFee,
                    'notes' => $notes,
                ]
            );
        }

        $studentCount = count($studentIds);
        $totalFee = $amount + $examFee;
        
        return back()->with('success', "Custom fees set for {$studentCount} student(s): GH₵ " . number_format($totalFee, 2) . " each.");
    }

    public function clearStudentFee(Student $student)
    {
        $student->loadMissing('user');
        $studentName = $student->user?->full_name ?? 'this student';
        $student->customFee()?->delete();
        return back()->with('success', "Custom fee cleared for {$studentName}. Will now use program default.");
    }

    public function clearBulkFees(Request $request)
    {
        $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $studentIds = $request->input('student_ids');
        StudentFee::whereIn('student_id', $studentIds)->delete();

        $studentCount = count($studentIds);
        return back()->with('success', "Custom fees cleared for {$studentCount} student(s). Will now use program defaults.");
    }

    /**
     * Payment Records — filterable list of all payments.
     */
    public function paymentRecords(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();

        $query = Payment::with(['student.user', 'student.program'])
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at');

        if ($request->filled('program_id')) {
            $query->whereHas('student', fn($q) => $q->where('program_id', $request->program_id));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->to);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->paginate(30)->withQueryString();
        $total    = $query->sum('amount_paid');

        // Students for filter dropdown
        $students = Student::with('user')->orderBy('id')->get();

        return view('admin.fees.payment-records', compact(
            'payments', 'programs', 'students', 'total'
        ));
    }

    /**
     * Print-friendly payment records.
     */
    public function paymentRecordsPrint(Request $request)
    {
        $programs = Program::orderBy('sequence')->get();

        $query = Payment::with(['student.user', 'student.program'])
            ->orderByDesc('payment_date');

        if ($request->filled('program_id')) {
            $query->whereHas('student', fn($q) => $q->where('program_id', $request->program_id));
        }
        if ($request->filled('student_id')) {
            $query->where('student_id', $request->student_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('payment_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('payment_date', '<=', $request->to);
        }
        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        $payments = $query->get();
        $total    = $payments->sum('amount_paid');
        $schoolName    = \App\Models\Setting::get('school_name', config('app.name'));
        $schoolSubtitle= \App\Models\Setting::get('school_subtitle', '');

        $filterLabel = '';
        if ($request->filled('program_id')) {
            $prog = $programs->find($request->program_id);
            $filterLabel = $prog ? 'Programme: ' . $prog->name : '';
        }
        if ($request->filled('student_id')) {
            $st = Student::with('user')->find($request->student_id);
            $filterLabel = $st ? 'Student: ' . $st->user?->full_name . ' (' . $st->student_id . ')' : '';
        }

        return view('admin.fees.payment-records-print', compact(
            'payments', 'total', 'schoolName', 'schoolSubtitle', 'filterLabel'
        ));
    }
