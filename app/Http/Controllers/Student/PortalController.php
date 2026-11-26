<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\Program;
use App\Models\ExamScore;
use App\Models\Setting;
use App\Models\StudentNotification;
use App\Models\Tenant;
use App\Services\ReportCardService;
use App\Services\FeeCalculationService;
use App\Support\ReportCardPdfAssets;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
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

        // ── Pre-warm the Neon DB so login submit is instant ─────────────────
        // Neon serverless takes 1-3s on cold start. Running a cheap query HERE
        // (on the GET page load) means the connection is already warm by the
        // time the student fills in and submits the form.
        try {
            if (DB::getDefaultConnection() === 'tenant') {
                DB::select('SELECT 1');
            }
        } catch (\Throwable $e) { /* non-critical */ }

        // ── School branding — cached 5 min ──────────────────────────────────
        $tenantSlug = session('tenant_slug', 'default');
        $branding   = \Illuminate\Support\Facades\Cache::remember('portal_branding_' . $tenantSlug, 300, function () {
            $rawLogo = Setting::get('site_logo_url', '');
            return [
                'schoolName'     => Setting::get('school_name', config('app.name', 'School')),
                'schoolSubtitle' => Setting::get('school_subtitle', ''),
                'siteLogoUrl'    => $rawLogo
                    ? (filter_var($rawLogo, FILTER_VALIDATE_URL)
                        ? $rawLogo
                        : \Illuminate\Support\Facades\Storage::disk('public')->url($rawLogo))
                    : null,
            ];
        });

        $schoolName     = $branding['schoolName'];
        $schoolSubtitle = $branding['schoolSubtitle'];
        $siteLogoUrl    = $branding['siteLogoUrl'];

        return view('portal.login', compact(
            'portalOpen', 'portalMessage',
            'schoolName', 'schoolSubtitle', 'siteLogoUrl'
        ));
    }

    /** Authenticate student by registration number + full name + password */
    /** Redirect back to the login form preserving the ?tenant= param */
    private function backToLogin(Request $request, array $errors = [], array $input = [])
    {
        $tenantParam = $request->query('tenant') ?? session('tenant_slug');
        $url = route('student-portal.login') . ($tenantParam ? '?tenant=' . $tenantParam : '');
        return redirect($url)->withErrors($errors)->withInput($input);
    }

    public function login(Request $request)
    {
        // Ensure tenant DB is active
        $this->ensureTenantDb($request);

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

        // ── Single fast query — exact + normalised match, user eager-loaded ─
        $normSql   = "REGEXP_REPLACE(LOWER(TRIM(student_id)), '[^a-z0-9]', '', 'g')";
        $normInput = preg_replace('/[^a-z0-9]/', '', strtolower(trim($request->student_id)));

        $student = Student::with('user')
            ->where(function ($q) use ($request, $normSql, $normInput) {
                $q->whereRaw('LOWER(TRIM(student_id)) = ?', [strtolower(trim($request->student_id))])
                  ->orWhereRaw($normSql . ' = ?', [$normInput]);
            })
            ->first();

        if (!$student) {
            return $this->backToLogin($request, [
                'credentials' => 'Registration number not found. Please check and try again.',
            ], ['student_id' => $request->student_id, 'full_name' => $request->full_name]);
        }

        if (!$student->user) {
            return $this->backToLogin($request, [
                'credentials' => 'Student profile incomplete. Please contact your administrator.',
            ], ['student_id' => $request->student_id]);
        }

        // ── Name match ──────────────────────────────────────────────────────
        $registeredWords = array_filter(preg_split('/[\s,.\-_]+/', strtolower(trim($student->user->full_name))));
        $inputWords      = array_filter(preg_split('/[\s,.\-_]+/', strtolower(trim($request->full_name))));

        $isMatch = !empty($inputWords);
        foreach ($inputWords as $word) {
            if (!in_array($word, $registeredWords)) { $isMatch = false; break; }
        }

        if (!$isMatch) {
            return $this->backToLogin($request, [
                'credentials' => 'The name entered does not match this registration number.',
            ], ['student_id' => $request->student_id, 'full_name' => $request->full_name]);
        }

        // ── Password check ──────────────────────────────────────────────────
        $provided     = $request->password;
        $validPassword = false;

        if (!empty($student->password) && $this->passwordMatches($provided, $student->password)) {
            $validPassword = true;
        }
        if (!$validPassword && !empty($student->user->password) && $this->passwordMatches($provided, $student->user->password)) {
            $validPassword = true;
        }

        if (!$validPassword) {
            return $this->backToLogin($request, [
                'credentials' => 'Incorrect password. Please try again.',
            ], ['student_id' => $request->student_id, 'full_name' => $request->full_name]);
        }

        // ── Rehash legacy plain-text passwords using raw UPDATE (no extra SELECT) ─
        if (!empty($student->password) && $this->isLegacyPlainPassword($provided, $student->password)) {
            Student::where('id', $student->id)->update(['password' => bcrypt($provided)]);
        }
        if (!empty($student->user->password) && $this->isLegacyPlainPassword($provided, $student->user->password)) {
            \App\Models\User::where('id', $student->user->id)->update(['password' => bcrypt($provided)]);
        }

        Session::put('portal_student_id',   $student->id);
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

        // ── Cache key per student so each student gets their own cache ─────
        $cacheKey = 'portal_dashboard_' . $student->id;

        // ── Load only what's needed for the dashboard in one go ────────────
        $student->load([
            'user', 'program', 'churchBranch',
            'payments',
        ]);

        // ── Active session (cached 5 min) ──────────────────────────────────
        $activePeriod    = \Illuminate\Support\Facades\Cache::remember('active_period', 120, fn() => \App\Models\AcademicPeriod::where('is_active', true)->first());
        $activeSession   = $activePeriod?->session ?? null;
        $activeSessionId = $activeSession?->id ?? null;

        $selectedSessionId = request()->query('session_id', $activeSessionId);
        $selectedSession   = $selectedSessionId ? \App\Models\AcademicSession::find($selectedSessionId) : $activeSession;
        $selectedSession   = $selectedSession ?? $activeSession;
        $selectedSessionId = $selectedSession?->id ?? null;

        // All sessions (cached 5 min — rarely changes)
        $allSessions = \Illuminate\Support\Facades\Cache::remember('all_sessions', 300, fn() => \App\Models\AcademicSession::orderByDesc('id')->get());

        // ── Exam scores — only load IDs+program_id+attempt (not all columns) ─
        $examScoreQuery = ExamScore::where('student_id', $student->id);
        if ($selectedSessionId) {
            $examScoreQuery->where('academic_session_id', $selectedSessionId);
        }

        $programIds = (clone $examScoreQuery)->distinct()->pluck('program_id')
            ->merge(collect([$student->program_id]))->filter()->unique();
        $programs = Program::whereIn('id', $programIds)->orderBy('sequence')->get();

        // ── Attendance (only load for selected session, limit columns) ──────
        if ($selectedSessionId) {
            $periodIds = \App\Models\AcademicPeriod::where('academic_session_id', $selectedSessionId)
                ->pluck('id')->toArray();
            if (!empty($periodIds)) {
                $student->load(['attendances' => fn($q) => $q->whereIn('academic_period_id', $periodIds)->with('course:id,name,code')]);
            } else {
                $student->load(['attendances' => fn($q) => $q->with('course:id,name,code')]);
            }
        } else {
            $student->load(['attendances' => fn($q) => $q->with('course:id,name,code')]);
        }

        // ── Fees — always use FeeCalculationService (reliable across all setups) ──
        // TermFeeService requires a term_fee_bill to exist for the session;
        // FeeCalculationService correctly picks branch-specific program fees.
        try {
            $fees = $this->feeService->calculate($student);
        } catch (\Throwable $e) {
            $fees = ['program_total'=>0,'exam_total'=>0,'total'=>0,'paid'=>0,'balance'=>0,'is_owing'=>false,'is_paid'=>true];
        }

        // ── Report card preview — only generate if scores exist ────────────
        $latestScore       = (clone $examScoreQuery)->latest('id')->first();
        $mostRecentProgram = $latestScore
            ? $programs->firstWhere('id', $latestScore->program_id)
            : $programs->sortByDesc('sequence')->first();

        $reportCardPreview = null;
        $previewAttempt    = 1;
        if ($mostRecentProgram) {
            $attemptQuery = ExamScore::where('student_id', $student->id)
                ->where('program_id', $mostRecentProgram->id);
            if ($selectedSessionId) {
                $attemptQuery->where('academic_session_id', $selectedSessionId);
            }
            $previewAttempt    = $attemptQuery->max('attempt') ?? 1;
            $reportCardPreview = $this->reportCardService->generate($student, $mostRecentProgram, $previewAttempt);
        }

        // ── CGPA (cached per student, 5 min) ───────────────────────────────
        $cgpa = \Illuminate\Support\Facades\Cache::remember('cgpa_' . $student->id, 300,
            fn() => $this->reportCardService->calculateCgpa($student)
        );

        // ── Attendance summary ─────────────────────────────────────────────
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

        // ── Notifications (only unread count + latest 10) ──────────────────
        $notifications = StudentNotification::forStudent($student->id)
            ->latest()->limit(20)->get();
        $unreadCount = $notifications->where('is_read', false)->count();

        // ── Portal settings (cached) ───────────────────────────────────────
        $portalMode  = \App\Models\Setting::get('portal_mode',  'university');
        $portalTheme = \App\Models\Setting::get('portal_theme', 'gold');

        return view('portal.dashboard', compact(
            'student', 'fees', 'programs', 'reportCardPreview', 'previewAttempt',
            'attendanceSummary', 'notifications', 'unreadCount', 'cgpa',
            'portalMode', 'portalTheme',
            'activeSession', 'selectedSession', 'selectedSessionId', 'allSessions'
        ));
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
        $data['portalMode']     = \App\Models\Setting::get('portal_mode', 'university');
        $data['portalTheme']    = \App\Models\Setting::get('portal_theme', 'gold');

        return view('portal.report-card', $data);
    }

    /** Download report card as PDF */
    public function reportCardPdf(Program $program, Request $request)
    {
        try {
            $student = $this->getPortalStudent();
            $student->load(['user', 'program', 'churchBranch']);

            if ($student->results_blocked) {
                return redirect()->route('portal.dashboard')
                    ->with('error', 'Your academic results are currently locked. Please contact the administration office.');
            }

            $attempt = $request->filled('attempt') ? (int) $request->attempt : 1;
            $data    = $this->reportCardService->generate($student, $program, $attempt);
        } catch (\Throwable $e) {
            Log::error('Portal report card data generation failed', ['exception' => $e->getMessage()]);
            return response('Unable to generate report card. Please try again later.', 500);
        }

        if (empty($data['has_scores'])) {
            return redirect()->route('portal.report-card', $program)
                ->with('error', 'No scores are available yet. PDF cannot be generated.');
        }

        // ── Use same assets as admin PDF — cache logo for 10 min to avoid re-fetching ─
        $data['logoBase64']  = \Illuminate\Support\Facades\Cache::remember(
            'report_logo_b64_' . session('tenant_slug', 'default'), 600,
            fn() => \App\Services\ReportCardPdfAssets::logoBase64()
        );
        $data['photoBase64'] = \App\Services\ReportCardPdfAssets::studentPhotoBase64($student);

        // ── School info (same as admin PDF) ─────────────────────────────────
        $data['schoolName']     = \App\Models\Setting::get('school_name',    config('app.name'));
        $data['schoolSubtitle'] = \App\Models\Setting::get('school_subtitle', '');
        $data['schoolAddress']  = \App\Models\Setting::get('school_address',  '');
        $data['schoolPhone']    = \App\Models\Setting::get('school_phone',    '');
        $data['schoolEmail']    = \App\Models\Setting::get('school_email',    '');
        $data['siteLogoUrl']    = \App\Models\Setting::get('site_logo_url',   '');

        // ── Template — use same logic as admin ExamController::pdf() ────────
        $templateSetting = \App\Models\Setting::get('report_card_template', 'standard');
        $gradingPreset   = \App\Models\Setting::get('grading_preset', 'ghana_standard');
        $templateMap = [
            'basic' => 'pdf.report_card_basic',
            'jhs'   => 'pdf.report_card_jhs',
        ];
        // Auto-pick jhs template for JHS grading preset if no explicit override
        if (!isset($templateMap[$templateSetting]) && $gradingPreset === 'jhs') {
            $templateView = 'pdf.report_card_jhs';
        } else {
            $templateView = $templateMap[$templateSetting] ?? 'pdf.report_card';
        }

        try {
            @ini_set('memory_limit', '256M');
            @set_time_limit(120);

            $html = view($templateView, $data)->render();

            $fontDir = sys_get_temp_dir() . '/dompdf_fonts';
            if (!is_dir($fontDir)) @mkdir($fontDir, 0755, true);

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

            $filename = 'report_card_' . str_replace(['/', '\\', ' '], '-', $student->student_id) . '_' . Str::slug($program->name) . '.pdf';
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

    /** Ensure the tenant DB is active before querying student records */
    private function ensureTenantDb(Request $request): void
    {
        if (DB::getDefaultConnection() === 'tenant') return;
        $slug = session('tenant_slug') ?? $request->query('tenant');
        if (!$slug) return;
        $tenant = Tenant::on('pgsql')->where('slug', $slug)->first();
        if (!$tenant) return;
        DB::purge('tenant');
        Config::set('database.connections.tenant', $tenant->dbConfig());
        DB::setDefaultConnection('tenant');
        session(['tenant_slug' => $tenant->slug, 'tenant_id' => $tenant->id]);
    }
}
