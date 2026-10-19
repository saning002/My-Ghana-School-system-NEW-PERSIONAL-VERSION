<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use App\Models\Student;
use App\Services\FeeReminderService;
use App\Services\TermFeeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FeeReminderController extends Controller
{
    public function __construct(
        private FeeReminderService $reminderService,
        private TermFeeService     $feeService
    ) {}

    /**
     * Show the fee reminders page — list students with their fee status.
     */
    public function index(Request $request)
    {
        $sessions        = AcademicSession::orderByDesc('year')->get();
        $selectedSession = $request->filled('session_id')
            ? AcademicSession::find($request->session_id)
            : $sessions->first();

        $summaries = collect();
        if ($selectedSession) {
            $summaries = $this->feeService->getAllStudentsSummary($selectedSession->id, true); // owing only
        }

        return view('admin.fees.reminders', compact('sessions', 'selectedSession', 'summaries'));
    }

    /**
     * Preview the personalised message for a single student before sending.
     */
    public function preview(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'session_id' => 'required|exists:academic_sessions,id',
        ]);

        $student = Student::with(['user', 'program'])->findOrFail($request->student_id);
        $message = $this->reminderService->buildMessage($student, (int) $request->session_id);
        $summary = $this->feeService->getStudentTermSummary($student, (int) $request->session_id);

        return response()->json([
            'student_name' => $student->user?->full_name,
            'student_id'   => $student->student_id,
            'email'        => $student->user?->email ?? $student->guardian_email ?? null,
            'phone'        => $student->user?->phone ?? $student->guardian_phone ?? $student->phone ?? null,
            'balance'      => $summary['balance'],
            'message'      => $message,
        ]);
    }

    /**
     * Send reminders — one student, selected students, or all owing.
     */
    public function send(Request $request)
    {
        $validated = $request->validate([
            'session_id'     => 'required|exists:academic_sessions,id',
            'mode'           => 'required|in:one,selected,all',
            'student_ids'    => 'nullable|array',
            'student_ids.*'  => 'exists:students,id',
            'channels'       => 'required|array|min:1',
            'channels.*'     => 'in:email,sms,whatsapp',
            'custom_message' => 'nullable|string|max:2000',
        ]);

        $sessionId     = (int) $validated['session_id'];
        $channels      = $validated['channels'];
        $sentBy        = auth()->user()?->full_name ?? 'Admin';
        $customMessage = !empty($validated['custom_message']) ? trim($validated['custom_message']) : null;

        // Determine which students to send to
        if ($validated['mode'] === 'all') {
            // All students who owe fees
            $summaries  = $this->feeService->getAllStudentsSummary($sessionId, true);
            $studentIds = $summaries->pluck('student.id')->toArray();
        } elseif ($validated['mode'] === 'selected') {
            $studentIds = $validated['student_ids'] ?? [];
        } else {
            // Single student
            $studentIds = $validated['student_ids'] ?? [];
        }

        if (empty($studentIds)) {
            return back()->with('error', 'No students selected or no students owe fees for this session.');
        }

        $results = $this->reminderService->sendBulkReminders($studentIds, $sessionId, $channels, $sentBy, $customMessage ?? null);

        $message = "Reminders sent: {$results['sent']} successful, {$results['failed']} failed.";
        if (!empty($results['errors'])) {
            $message .= ' Errors: ' . implode('; ', array_slice($results['errors'], 0, 3));
        }

        return back()->with($results['failed'] === 0 ? 'success' : 'warning', $message);
    }

    /**
     * Show reminder log history.
     */
    public function log(Request $request)
    {
        $reminders = DB::table('fee_reminders')
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('admin.fees.reminder-log', compact('reminders'));
    }
}
