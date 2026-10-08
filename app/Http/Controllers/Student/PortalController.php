<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\ExamScore;
use App\Models\Setting;
use App\Models\StudentNotification;
use App\Services\ReportCardService;
use App\Services\FeeCalculationService;
use App\Support\ReportCardPdfAssets;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class PortalController extends Controller
{
    public function __construct(
        private ReportCardService $reportCardService,
        private FeeCalculationService $feeService
    ) {}

    /** Show the student portal login page */
    public function loginForm()
    {
        $portalOpen    = Setting::portalIsOpen();
        $portalMessage = Setting::get('portal_message', 'The student portal is currently closed.');

        if ($portalOpen && Session::has('portal_student_id')) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login', compact('portalOpen', 'portalMessage'));
    }

    /** Authenticate student by registration number + full name + password */
    public function login(Request $request)
    {
        // Block login if portal is closed
        if (!Setting::portalIsOpen()) {
            return back()->with('portal_closed', true);
        }
        $request->validate([
            'student_id' => 'required|string',
            'full_name'  => 'required|string',
            'password'   => 'required|string',
        ], [
            'student_id.required' => 'Please enter your registration number.',
            'full_name.required'  => 'Please enter your full name.',
            'password.required'   => 'Please enter your password.',
        ]);

        // Normalize input student ID by removing punctuation and whitespace
        $normalizedInputId = $this->normalizeStudentId($request->student_id);
        $normalizedStudentIdSql = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(student_id), '/', ''), '-', ''), '_', ''), ' ', ''), '.', ''), ':', ''), ',', '')";

        // 1. Try exact trimmed case-insensitive match (fast, uses database indexes)
        $student = Student::whereRaw('LOWER(TRIM(student_id)) = ?', [strtolower(trim($request->student_id))])->first();

        // 2. Fall back to stripping delimiters from student_id columns to capture typos or alternate formatting
        if (!$student) {
            $student = Student::whereRaw($normalizedStudentIdSql . ' = ?', [$normalizedInputId])->first();
        }

        // 3. Fall back to branch-agnostic matching:
        // If the student enters an ID with the branch code (e.g. KMC/KA/PC/001) but the database stored it
        // without the branch code (e.g. KMC/PC/001), we query all branch codes and strip it for matching.
        if (!$student) {
            try {
                $branchCodes = \App\Models\ChurchBranch::pluck('code')
                    ->filter()
                    ->map(fn($code) => strtolower(trim($code)))
                    ->toArray(); // e.g. ['ka', 'ho']

                // If input starts with 'kmc' + one of the branch codes, strip that branch code
                $branchStrippedInputId = $normalizedInputId;
                foreach ($branchCodes as $code) {
                    if (str_starts_with($normalizedInputId, 'kmc' . $code)) {
                        // Strip the branch code (e.g. 'kmckapc001' -> 'kmcpc001')
                        $branchStrippedInputId = 'kmc' . substr($normalizedInputId, 3 + strlen($code));
                        break;
                    }
                }

                if ($branchStrippedInputId !== $normalizedInputId) {
                    $student = Student::whereRaw($normalizedStudentIdSql . ' = ?', [$branchStrippedInputId])->first();
                }

                // 4. If the database has the branch code (e.g. KMC/KA/PC/001) but the student entered it without
                // the branch code (e.g. KMC/PC/001), let's construct a pattern matching that inserts the branch code
                if (!$student) {
                    foreach ($branchCodes as $code) {
                        if (str_starts_with($normalizedInputId, 'kmc') && !str_starts_with($normalizedInputId, 'kmc' . $code)) {
                            $branchInsertedId = 'kmc' . $code . substr($normalizedInputId, 3);
                            $student = Student::whereRaw($normalizedStudentIdSql . ' = ?', [$branchInsertedId])->first();
                            if ($student) {
                                break;
                            }
                        }
                    }
                }
            } catch (\Throwable $e) {
                \Log::error('Branch-agnostic matching failed: ' . $e->getMessage());
            }
        }

        if (!$student) {
            // Final permissive fallback: try flexible lookup by normalized contains
            try {
                $student = $this->findStudentByFlexibleId($request->student_id);
            } catch (\Throwable $e) {
                \Log::warning('Flexible student lookup failed: ' . $e->getMessage());
            }
        }

        if (!$student) {
            return back()->withErrors([
                'credentials' => 'Invalid registration number. Please check and try again.',
            ])->withInput(['student_id' => $request->student_id, 'full_name' => $request->full_name]);
        }


        // Load the related user after finding the student
        $student->load('user');
        
        if (!$student->user) {
            Log::error('Student found but no associated user', ['student_id' => $student->id]);
            return back()->withErrors([
                'credentials' => 'System error: Student profile incomplete. Please contact administrator.',
            ])->withInput(['student_id' => $request->student_id]);
        }

        // Robust name matching:
        // Convert to lowercase and trim
        $registeredName = strtolower(trim($student->user->full_name));
        $inputName = strtolower(trim($request->full_name));

        // Get array of individual words (filtering empty items) by splitting by whitespace or punctuation
        $registeredWords = array_filter(preg_split('/[\s,\.\-_]+/', $registeredName));
        $inputWords = array_filter(preg_split('/[\s,\.\-_]+/', $inputName));

        // We check if all input words are contained within the registered words.
        // This handles:
        // - Different name order (e.g., "Doris Assin Abakah" vs "Abakah Assin Doris")
        // - Omission of middle names (e.g., "Doris Abakah")
        // - Punctuation difference (e.g., "Abakah, Doris")
        $isMatch = true;
        if (empty($inputWords)) {
            $isMatch = false;
        } else {
            foreach ($inputWords as $word) {
                if (!in_array($word, $registeredWords)) {
                    $isMatch = false;
                    break;
                }
            }
        }

        if (!$isMatch) {
            return back()->withErrors([
                'credentials' => 'The name entered does not match the registration number. Please check and try again.',
            ])->withInput(['student_id' => $request->student_id, 'full_name' => $request->full_name]);
        }

        // Verify password against student record first, then fall back to linked user record
        $providedPassword = $request->password;
        $validPassword = false;

        if (!empty($student->password) && $this->passwordMatches($providedPassword, $student->password)) {
            $validPassword = true;
        }

        if (!$validPassword && $student->user && !empty($student->user->password) && $this->passwordMatches($providedPassword, $student->user->password)) {
            $validPassword = true;
        }

        if (!$validPassword) {
            return back()->withErrors([
                'credentials' => 'Invalid password. Please check and try again.',
            ])->withInput(['student_id' => $request->student_id, 'full_name' => $request->full_name]);
        }

        // Rehash any legacy plain-text password values after successful login
        if (!empty($student->password) && $this->isLegacyPlainPassword($providedPassword, $student->password)) {
            $student->password = $providedPassword;
            $student->save();
        }
        if ($student->user && !empty($student->user->password) && $this->isLegacyPlainPassword($providedPassword, $student->user->password)) {
            $student->user->password = $providedPassword;
            $student->user->save();
        }

        Session::put('portal_student_id', $student->id);
        Session::put('portal_student_name', $student->user->full_name);

        return redirect()->route('portal.dashboard');
    }

    private function normalizeStudentId(string $studentId): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(trim($studentId)));
    }

    /**
     * Attempt a flexible student lookup using relaxed normalization and numeric suffix matching.
     */
    private function findStudentByFlexibleId(string $input): ?\App\Models\Student
    {
        $normalized = $this->normalizeStudentId($input);
        $normSql = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(LOWER(student_id), '/', ''), '-', ''), '_', ''), ' ', ''), '.', ''), ':', ''), ',', '')";

        // 1) find any student whose normalized ID contains the normalized input
        $candidates = \App\Models\Student::whereRaw($normSql . ' LIKE ?', ["%{$normalized}%"])->with('user')->get();
        if ($candidates->isNotEmpty()) {
            // Prefer a candidate whose normalized id ends with the same numeric suffix (if present)
            if (preg_match('/(\d{2,})$/', $normalized, $m)) {
                $digits = $m[1];
                foreach ($candidates as $c) {
                    $candNorm = $this->normalizeStudentId($c->student_id);
                    if (str_ends_with($candNorm, $digits)) {
                        return $c;
                    }
                }
            }
            return $candidates->first();
        }

        // 2) try raw LIKE on student_id
        $rawCandidates = \App\Models\Student::where('student_id', 'like', '%' . trim($input) . '%')->with('user')->get();
        if ($rawCandidates->isNotEmpty()) {
            return $rawCandidates->first();
        }

        return null;
    }

    private function passwordMatches(string $providedPassword, ?string $storedPassword): bool
    {
        if (empty($storedPassword)) {
            return false;
        }

        if (Hash::check($providedPassword, $storedPassword)) {
            return true;
        }

        return hash_equals($storedPassword, $providedPassword);
    }

    private function isLegacyPlainPassword(string $providedPassword, string $storedPassword): bool
    {
        return !empty($storedPassword)
            && !Hash::check($providedPassword, $storedPassword)
            && hash_equals($storedPassword, $providedPassword);
    }

    /** Student portal dashboard */
    public function dashboard()
    {
        $student = $this->getPortalStudent();

        $student->load([
            'user', 'program', 'churchBranch',
            'payments',
            'attendances.course',
            'enrollments.course',
            'examScores.course',
        ]);

        // Fee summary
        $fees = $this->feeService->calculate($student);

        // All programs this student has exam scores in (for history view)
        $programIds = ExamScore::where('student_id', $student->id)
            ->distinct()
            ->pluck('program_id')
            ->merge(collect([$student->program_id]))
            ->filter()
            ->unique();
        $programs = Program::whereIn('id', $programIds)->orderBy('sequence')->get();

        // Most recent report card preview — find the latest program the student has scores in, or fallback to current program
        $latestScore = ExamScore::where('student_id', $student->id)->latest('id')->first();
        $mostRecentProgram = $latestScore
            ? $programs->firstWhere('id', $latestScore->program_id)
            : null;
        if (!$mostRecentProgram) {
            $mostRecentProgram = $programs->sortByDesc('sequence')->first();
        }

        $reportCardPreview  = null;
        $previewAttempt     = 1;
        if ($mostRecentProgram) {
            $previewAttempt = ExamScore::where('student_id', $student->id)
                ->where('program_id', $mostRecentProgram->id)
                ->max('attempt') ?? 1;
            $reportCardPreview = $this->reportCardService->generate($student, $mostRecentProgram, $previewAttempt);
        }

        $cgpa = $this->reportCardService->calculateCgpa($student);

        // Attendance summary
        $attendanceSummary = $student->attendances
            ->groupBy(fn($a) => $a->course->code ?? 'Unknown')
            ->map(fn($records) => [
                'course'     => $records->first()->course->name ?? 'Unknown',
                'code'       => $records->first()->course->code ?? '—',
                'present'    => $records->where('status', 'present')->count(),
                'absent'     => $records->where('status', 'absent')->count(),
                'total'      => $records->count(),
                'percentage' => $records->count() > 0
                    ? round($records->where('status', 'present')->count() / $records->count() * 100, 1)
                    : 0,
            ]);

        // Notifications for this student
        $notifications = StudentNotification::forStudent($student->id)
            ->latest()->get();
        $unreadCount = $notifications->where('is_read', false)->count();

        return view('portal.dashboard', compact('student', 'fees', 'programs', 'reportCardPreview', 'previewAttempt', 'attendanceSummary', 'notifications', 'unreadCount', 'cgpa'));
    }

    /** View report card for a specific program */
    public function reportCard(Program $program, Request $request)
    {
        $student = $this->getPortalStudent();
        $student->load(['user', 'program', 'churchBranch']);

        if ($student->results_blocked) {
            return redirect()->route('portal.dashboard')->with('error', 'Your academic results are currently locked. Please contact the administration office.');
        }

        $attempts = ExamScore::where('student_id', $student->id)
            ->where('program_id', $program->id)
            ->distinct()
            ->orderBy('attempt')
            ->pluck('attempt');

        $currentAttempt = $request->filled('attempt')
            ? (int) $request->attempt
            : ($attempts->max() ?? 1);

        $data = $this->reportCardService->generate($student, $program, $currentAttempt);
        $data['attempts']       = $attempts;
        $data['currentAttempt'] = $currentAttempt;

        return view('portal.report-card', $data);
    }

    /** Download report card as PDF */
    public function reportCardPdf(Program $program, Request $request)
    {
        try {
            $student = $this->getPortalStudent();
            $student->load(['user', 'program', 'churchBranch']);

            if ($student->results_blocked) {
                return redirect()->route('portal.dashboard')->with('error', 'Your academic results are currently locked. Please contact the administration office.');
            }

            $attempt = $request->filled('attempt') ? (int) $request->attempt : 1;
            $data = $this->reportCardService->generate($student, $program, $attempt);
        } catch (\Throwable $e) {
            Log::error('Portal report card data generation failed', ['exception' => $e->getMessage()]);
            return response('Unable to generate report card. Please try again later.', 500);
        }

        if (empty($data['has_scores'])) {
            return redirect()->route('portal.report-card', $program)
                ->with('error', 'No scores are available yet. PDF cannot be generated.');
        }

        $data['logoBase64']  = ReportCardPdfAssets::logoBase64();
        $data['photoBase64'] = ReportCardPdfAssets::studentPhotoBase64($student);

        try {
            @ini_set('memory_limit', '256M');
            @set_time_limit(120);

            $html = view('pdf.report_card', $data)->render();

            $fontDir = sys_get_temp_dir() . '/dompdf_fonts';
            if (! is_dir($fontDir)) {
                @mkdir($fontDir, 0755, true);
            }

            $options = new \Dompdf\Options();
            $options->setIsHtml5ParserEnabled(true);
            $options->setIsRemoteEnabled(false);
            $options->setDefaultFont('DejaVu Sans');
            $options->setChroot([base_path(), public_path()]);
            $options->setFontDir($fontDir);
            $options->setFontCache($fontDir);
            $options->setTempDir(sys_get_temp_dir());

            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('a4', 'portrait');
            $dompdf->render();

            $filename = 'report_card_' . $student->student_id . '_' . Str::slug($program->name) . '.pdf';
            $output   = $dompdf->output();

            return response($output, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Content-Length'      => strlen($output),
            ]);
        } catch (\Throwable $e) {
            Log::error('Portal report card PDF generation failed', ['exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response('PDF generation failed. Please contact support.', 500);
        }
    }

    public function photo(Student $student)
    {
        return $this->servePortalStudentMedia($student, 'photo');
    }

    /** Background image for portal dashboard (same auth as photo). */
    public function backgroundPhoto(Student $student)
    {
        return $this->servePortalStudentMedia($student, 'background_photo');
    }

    /**
     * Stream a student image file for the logged-in portal user only.
     * Avoids relying on the public/storage symlink or APP_URL for /storage URLs.
     */
    private function servePortalStudentMedia(Student $student, string $column): \Symfony\Component\HttpFoundation\Response
    {
        $portalStudentId = Session::get('portal_student_id');
        if (! $portalStudentId || (int) $portalStudentId !== (int) $student->id) {
            throw new HttpResponseException(redirect()->route('portal.login'));
        }

        $raw = $student->{$column};
        if (! $raw || ! is_string($raw)) {
            abort(404);
        }

        // Any external URL — redirect directly
        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return redirect($raw);
        }

        $path = str_replace('\\', '/', trim($raw));

        if (str_starts_with($path, 'public/')) {
            $path = substr($path, strlen('public/'));
        }
        if (str_starts_with($path, '/storage/')) {
            $path = ltrim(substr($path, strlen('/storage/')), '/');
        } elseif (str_starts_with($path, 'storage/')) {
            $path = ltrim(substr($path, strlen('storage/')), '/');
        }

        if (Storage::disk('public')->exists($path)) {
            return response()->file(Storage::disk('public')->path($path));
        }

        $onDiskPath = Storage::disk('public')->path($path);
        if (File::exists($onDiskPath)) {
            return response()->file($onDiskPath);
        }

        $publicPath = public_path($path);
        if (File::exists($publicPath)) {
            return response()->file($publicPath);
        }

        abort(404);
    }

    /** Logout from portal */
    public function logout()
    {
        Session::forget(['portal_student_id', 'portal_student_name']);
        return redirect()->route('portal.login')->with('success', 'You have been logged out.');
    }

    /** Mark all notifications as read */
    public function markAllRead()
    {
        $student = $this->getPortalStudent();
        StudentNotification::forStudent($student->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);
        return back();
    }

    /** Show change password form */
    public function changePasswordForm()
    {
        $student = $this->getPortalStudent();
        return view('portal.change-password', compact('student'));
    }

    /** Update student password */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Current password is required.',
            'new_password.required'     => 'New password is required.',
            'new_password.min'          => 'New password must be at least 6 characters.',
            'new_password.confirmed'    => 'Password confirmation does not match.',
        ]);

        $student = $this->getPortalStudent();

        // Verify current password
        if (!password_verify($request->current_password, $student->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        // Update to new password
        $student->update(['password' => $request->new_password]);

        return redirect()->route('portal.dashboard')
            ->with('success', 'Password updated successfully! You can use your new password on your next login.');
    }

    /** Get the currently authenticated portal student */
    private function getPortalStudent(): Student
    {
        $id = Session::get('portal_student_id');
        if (!$id) {
            throw new HttpResponseException(redirect()->route('portal.login'));
        }
        return Student::with('user')->findOrFail($id);
    }
}
