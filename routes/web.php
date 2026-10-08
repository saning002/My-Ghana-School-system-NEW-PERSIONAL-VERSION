<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebsiteController;
use App\Http\Controllers\Admin\WebsiteContentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\ProgramController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\LecturerController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\FeesController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\PortalSettingsController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\BranchAdminController;
use App\Http\Controllers\Admin\AdminManagementController;
use App\Http\Controllers\Lecturer\DashboardController  as LecturerDashboardController;
use App\Http\Controllers\Lecturer\AttendanceController as LecturerAttendanceController;
use App\Http\Controllers\Lecturer\ProfileController    as LecturerProfileController;
use App\Http\Controllers\Lecturer\NotificationController as LecturerNotificationController;
use App\Http\Controllers\Lecturer\ExamController       as LecturerExamController;
use App\Http\Controllers\Lecturer\ReportController     as LecturerReportController;
use App\Http\Controllers\Lecturer\AuthController       as LecturerAuthController;
use App\Http\Controllers\Student\PortalController;

// ── Debug routes removed for security ───────────────────────────────────

// ── Cloudinary Diagnostic (requires login) ───────────────────────────────
Route::match(['get','post'], '/diagnostic/cloudinary', function (\Illuminate\Http\Request $request) {
    $report = ['timestamp' => now()->toDateTimeString()];

    // 1. Check credentials via both getenv() and env()
    $cloudUrl = getenv('CLOUDINARY_URL') ?: env('CLOUDINARY_URL', '');
    // Parse individual parts FROM the URL so the diagnostic is informative
    $parsedCloudName = '';
    $parsedApiKey    = '';
    if (is_string($cloudUrl) && str_starts_with($cloudUrl, 'cloudinary://')) {
        $stripped = substr($cloudUrl, strlen('cloudinary://'));
        if (str_contains($stripped, '@')) {
            [$creds, $parsedCloudName] = explode('@', $stripped, 2);
            if (str_contains($creds, ':')) {
                [$parsedApiKey] = explode(':', $creds, 2);
            }
        }
    }
    $report['credentials'] = [
        'CLOUDINARY_URL_getenv'        => strlen((string) getenv('CLOUDINARY_URL')) > 10 ? '✅ SET ('.strlen(getenv('CLOUDINARY_URL')).' chars)' : '❌ NOT SET',
        'CLOUDINARY_URL_env()'         => strlen((string) env('CLOUDINARY_URL', '')) > 10 ? '✅ SET' : '❌ NOT SET',
        'CLOUD_NAME_from_URL'          => $parsedCloudName ?: '❌ could not parse',
        'API_KEY_from_URL'             => $parsedApiKey ? '✅ SET ('.strlen($parsedApiKey).' chars)' : '❌ could not parse',
        'CLOUDINARY_CLOUD_NAME_getenv' => getenv('CLOUDINARY_CLOUD_NAME') ?: '(not set separately — OK if CLOUDINARY_URL is set)',
        'CLOUDINARY_API_KEY_getenv'    => strlen((string) getenv('CLOUDINARY_API_KEY')) > 3 ? '✅ SET' : '(not set separately — OK if CLOUDINARY_URL is set)',
        'CLOUDINARY_API_SECRET_getenv' => strlen((string) getenv('CLOUDINARY_API_SECRET')) > 3 ? '✅ SET' : '(not set separately — OK if CLOUDINARY_URL is set)',
        'config_cloud_url_set'         => strlen((string) config('cloudinary.cloud_url', '')) > 20 ? '✅ SET' : '❌ NOT SET',
    ];
    $report['is_configured'] = \App\Services\CloudinaryService::isConfigured();

    if ($request->hasFile('test_image')) {
        try {
            $file = $request->file('test_image');
            $report['upload'] = [
                'file_name' => $file->getClientOriginalName(),
                'size_kb'   => round($file->getSize() / 1024, 1),
                'mime'      => $file->getMimeType(),
            ];
            $url = \App\Services\CloudinaryService::upload($file, 'diagnostic_test');
            if ($url) {
                $report['upload']['status'] = 'SUCCESS';
                $report['upload']['url']    = $url;
                $report['upload']['is_cloudinary_url'] = str_contains($url, 'cloudinary.com');
                try {
                    $ch = curl_init($url);
                    curl_setopt_array($ch, [CURLOPT_NOBODY => true, CURLOPT_FOLLOWLOCATION => true, CURLOPT_TIMEOUT => 10]);
                    curl_exec($ch);
                    $report['upload']['http_status'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    curl_close($ch);
                } catch (\Throwable $ce) { $report['upload']['http_check_error'] = $ce->getMessage(); }
            } else {
                $report['upload']['status']  = 'FAILED';
                $report['upload']['message'] = 'upload() returned null — check laravel.log below for CloudinaryService errors';
            }
        } catch (\Throwable $e) {
            $report['upload'] = ['status' => 'EXCEPTION', 'message' => $e->getMessage()];
        }
    }

    try {
        $log = storage_path('logs/laravel.log');
        if (file_exists($log)) {
            $lines = array_filter(explode("\n", file_get_contents($log)),
                fn($l) => str_contains(strtolower($l), 'cloudinary'));
            $report['recent_logs'] = array_values(array_slice($lines, -20));
        }
    } catch (\Throwable $e) { $report['recent_logs'] = 'Error: ' . $e->getMessage(); }

    foreach (['public', 'local'] as $disk) {
        try {
            $p = \Illuminate\Support\Facades\Storage::disk($disk)->path('');
            $report['storage'][$disk] = ['path' => $p, 'exists' => is_dir($p), 'writable' => is_writable($p)];
        } catch (\Throwable $e) { $report['storage'][$disk] = $e->getMessage(); }
    }

    $esc = 'htmlspecialchars';
    $css = 'body{font:14px/1.7 system-ui,sans-serif;max-width:1100px;margin:40px auto;padding:20px;background:#f8fafc}
            h2{color:#334155;border-left:4px solid #3b82f6;padding-left:10px;margin-top:28px}
            table{width:100%;border-collapse:collapse;background:#fff;margin:8px 0;border-radius:8px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.08)}
            th,td{border:1px solid #e2e8f0;padding:9px 12px;text-align:left;font-size:13px}th{background:#f1f5f9;font-weight:700}
            .ok{background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;padding:12px;border-radius:6px;margin:10px 0}
            .err{background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px;border-radius:6px;margin:10px 0}
            .warn{background:#fffbeb;border:1px solid #fde68a;color:#92400e;padding:12px;border-radius:6px;margin:10px 0}
            pre{background:#1e293b;color:#e2e8f0;padding:14px;border-radius:8px;overflow-x:auto;font-size:12px;margin:10px 0}
            .form{background:#fff;border:1px solid #e2e8f0;padding:20px;border-radius:8px;margin:16px 0}
            button{background:#4CAF50;color:#fff;border:none;padding:10px 24px;border-radius:6px;cursor:pointer;font-size:14px;font-weight:700}
            input[type=file]{margin:10px 0;display:block}img{max-width:360px;border:1px solid #e2e8f0;border-radius:6px;margin-top:8px}';

    $html  = "<html><head><title>Cloudinary Diagnostic</title><style>{$css}</style></head><body>";
    $html .= '<h1 style="border-bottom:3px solid #4CAF50;padding-bottom:8px">🔍 Cloudinary Diagnostic — ' . $report['timestamp'] . '</h1>';

    $html .= '<h2>1. Credential Check</h2>';
    $html .= $report['is_configured']
        ? '<div class="ok">✅ Cloudinary IS configured — uploads should work</div>'
        : '<div class="err">❌ Cloudinary NOT configured — uploads fall back to local disk (lost on Render deploys)</div>';

    if (! $report['is_configured']) {
        $html .= '<div class="warn"><strong>To fix on Render.com:</strong><br>';
        $html .= 'Dashboard → your service → <strong>Environment</strong> tab → Add variable:<br><br>';
        $html .= '<code>CLOUDINARY_URL = cloudinary://YOUR_API_KEY:YOUR_API_SECRET@YOUR_CLOUD_NAME</code><br><br>';
        $html .= 'Get your credentials from <a href="https://cloudinary.com/console" target="_blank">cloudinary.com/console</a> → API Keys</div>';
    }

    $html .= '<table><tr><th>Check</th><th>Result</th></tr>';
    foreach ($report['credentials'] as $k => $v) {
        $html .= "<tr><td>" . $esc($k) . "</td><td>" . $esc($v) . "</td></tr>";
    }
    $html .= '</table>';

    $html .= '<h2>2. Upload Test</h2>';
    $html .= '<div class="form"><form method="post" enctype="multipart/form-data">' . csrf_field();
    $html .= '<label><strong>Select a test image:</strong></label>';
    $html .= '<input type="file" name="test_image" accept="image/*" required>';
    $html .= '<button type="submit">🚀 Run Upload Test</button></form></div>';

    if (isset($report['upload'])) {
        $u = $report['upload'];
        $class = $u['status'] === 'SUCCESS' ? 'ok' : 'err';
        $html .= "<div class=\"{$class}\"><strong>" . $esc($u['status']) . "</strong>" . (isset($u['message']) ? ': ' . $esc($u['message']) : '') . "</div>";
        $html .= '<table><tr><th>Key</th><th>Value</th></tr>';
        foreach ($u as $k => $v) {
            if ($k === 'url') {
                $html .= '<tr><td>url</td><td><a href="' . $esc($v) . '" target="_blank">' . $esc($v) . '</a></td></tr>';
            } else {
                $html .= '<tr><td>' . $esc($k) . '</td><td>' . $esc((string)$v) . '</td></tr>';
            }
        }
        $html .= '</table>';
        if (isset($u['url'])) {
            $html .= '<p><strong>Preview:</strong></p><img src="' . $esc($u['url']) . '" alt="uploaded" onerror="this.after(document.createTextNode(\'⚠️ Image not loading — check URL above\'))">';
        }
    }

    $html .= '<h2>3. Storage Disks</h2><table><tr><th>Disk</th><th>Path</th><th>Exists</th><th>Writable</th></tr>';
    foreach (($report['storage'] ?? []) as $disk => $info) {
        if (is_array($info)) {
            $html .= "<tr><td>{$disk}</td><td>" . $esc($info['path']) . '</td><td>' . ($info['exists'] ? '✅' : '❌') . '</td><td>' . ($info['writable'] ? '✅' : '❌') . '</td></tr>';
        } else {
            $html .= "<tr><td>{$disk}</td><td colspan='3'>" . $esc($info) . '</td></tr>';
        }
    }
    $html .= '</table>';

    $html .= '<h2>4. Recent Cloudinary Log Entries</h2>';
    if (!empty($report['recent_logs']) && is_array($report['recent_logs'])) {
        $html .= '<pre>' . implode("\n", array_map($esc, $report['recent_logs'])) . '</pre>';
    } else {
        $html .= '<p style="color:#64748b">No Cloudinary log entries found. Run an upload test above to generate some.</p>';
    }

    $html .= '<h2>5. Full JSON</h2><pre>' . $esc(json_encode($report, JSON_PRETTY_PRINT)) . '</pre>';
    $html .= '</body></html>';
    return response($html)->header('Content-Type', 'text/html');
})->middleware('auth')->name('diagnostic.cloudinary');

// ── Live diagnostics ──────────────────────────────────────────────────────
Route::get('/diag', function () {
    $out = [];

    // 1. Programs
    try {
        $programs = \App\Models\Program::orderBy('sequence')->get(['id','name','sequence']);
        $out['programs'] = ['count' => $programs->count(), 'list' => $programs];
    } catch (\Throwable $e) { $out['programs'] = ['error' => $e->getMessage()]; }

    // 2. Academic sessions & periods
    try {
        $sessions = \App\Models\AcademicSession::withCount('periods')->get();
        $out['academic_sessions'] = ['count' => $sessions->count(), 'list' => $sessions];
        $out['academic_periods']  = ['count' => \App\Models\AcademicPeriod::count()];
    } catch (\Throwable $e) { $out['academic_sessions'] = ['error' => $e->getMessage()]; }

    // 3. DB counts
    try {
        $out['students_count']   = \App\Models\Student::count();
        $out['attendance_count'] = \App\Models\Attendance::count();
        $out['payments_count']   = \App\Models\Payment::count();
        $out['program_fees']     = \Illuminate\Support\Facades\DB::table('program_fees')->get(['id','program_id','amount'])->toArray();
        $out['fee_summary']      = app(\App\Services\FeeCalculationService::class)->getSummary();
    } catch (\Throwable $e) { $out['db_counts_error'] = $e->getMessage(); }

    // 4. student_notifications columns (key for lecturer notifications migration)
    try {
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('student_notifications');
        $out['has_recipient_type'] = in_array('recipient_type', $cols) ? 'YES' : 'NO - MIGRATION NOT RUN';
        $out['has_lecturer_id']    = in_array('lecturer_id', $cols)    ? 'YES' : 'NO - MIGRATION NOT RUN';
    } catch (\Throwable $e) { $out['notifications_cols_error'] = $e->getMessage(); }

    // 5. Admin layout vars (Setting / SiteSetting)
    try {
        $out['school_name']       = \App\Models\Setting::get('school_name', 'default');
        $out['lecturer_can_att']  = \App\Models\Setting::lecturerCan('attendance') ? 'yes' : 'no';
        if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            $t = \App\Models\Website\SiteSetting::instance()->theme;
            $out['theme_primary'] = $t['primary'] ?? 'missing';
        }
    } catch (\Throwable $e) { $out['admin_vars_error'] = $e->getMessage(); }

    // 6. Try to render the reports view directly (catches Blade compile errors)
    try {
        $feeService = app(\App\Services\FeeCalculationService::class);
        $summary = [
            'total_students' => \App\Models\Student::count(),
            'active_students'=> \App\Models\Student::where('status','active')->count(),
            'graduated'      => \App\Models\Student::where('status','graduated')->count(),
            'suspended'      => 0, 'manifestation' => 0,
            'total_programs' => \App\Models\Program::count(),
            'total_courses'  => \App\Models\Course::count(),
            'attendance_rate'=> 0,
            'fees'           => $feeService->getSummary(),
        ];
        $programStats = \App\Models\Program::withCount('students')->with('courses')->orderBy('sequence')->get()
            ->map(function($p) { return ['program'=>['name'=>$p->name],'enrolled'=>$p->students_count,'graduated'=>0]; });
        $statusDistribution = \App\Models\Student::select('status', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('status')->get()->mapWithKeys(function($r) { return [$r->status => $r->total]; });
        $courseEnrollmentBuckets = \App\Models\Course::withCount('enrollments')->get()->groupBy(function($c) {
            $n = (int)$c->enrollments_count;
            if ($n===0) return '0'; if ($n<=5) return '1-5'; if ($n<=10) return '6-10'; if ($n<=20) return '11-20'; return '21+';
        })->map->count();
        $bucketOrder = ['0','1-5','6-10','11-20','21+'];
        $courseEnrollmentBucketLabels = $bucketOrder;
        $courseEnrollmentBucketCounts = collect($bucketOrder)->map(function($l) use ($courseEnrollmentBuckets) { return (int)($courseEnrollmentBuckets->get($l,0)); })->values();
        $html = view('admin.reports.index', compact('summary','programStats','statusDistribution','courseEnrollmentBuckets','courseEnrollmentBucketLabels','courseEnrollmentBucketCounts'))->render();
        $out['reports_view_render'] = 'OK - '.strlen($html).' bytes';
    } catch (\Throwable $e) {
        $out['reports_view_error'] = $e->getMessage();
        $out['reports_view_file']  = $e->getFile().':'.$e->getLine();
        $out['reports_view_trace'] = array_slice(explode("\n", $e->getTraceAsString()), 0, 8);
    }

    // 7. Site settings row
    try {
        $row = \Illuminate\Support\Facades\DB::table('site_settings')->first();
        $out['site_name_in_db'] = $row ? $row->site_name : 'NO ROW';
    } catch (\Throwable $e) { $out['site_settings_error'] = $e->getMessage(); }

    return response()->json($out, 200, [], JSON_PRETTY_PRINT);
});

// ── Emergency cache clear (run once after deploy if views are stale) ──────
Route::get('/clear-views', function () {
    $cleared = [];
    try { \Artisan::call('view:clear');  $cleared[] = 'views cleared'; }  catch (\Throwable $e) { $cleared[] = 'view:clear failed: '.$e->getMessage(); }
    try { \Artisan::call('cache:clear'); $cleared[] = 'cache cleared'; }  catch (\Throwable $e) { $cleared[] = 'cache:clear failed: '.$e->getMessage(); }
    try { \Artisan::call('view:cache');  $cleared[] = 'views recompiled'; } catch (\Throwable $e) { $cleared[] = 'view:cache failed: '.$e->getMessage(); }
    return response()->json(['done' => true, 'steps' => $cleared]);
});

// ── Fix academic periods (run once if attendance periods are empty) ────────
Route::get('/fix-periods', function () {
    $now = now();
    $years = ['2024/2025', '2025/2026', '2026/2027'];
    $created = 0;
    foreach ($years as $year) {
        if (!\Illuminate\Support\Facades\DB::table('academic_sessions')->where('year', $year)->exists()) {
            $sessionId = \Illuminate\Support\Facades\DB::table('academic_sessions')->insertGetId(['year' => $year, 'created_at' => $now, 'updated_at' => $now]);
            foreach (['January','February','March','April','May','June','July','August','September','October','November','December'] as $m) {
                \Illuminate\Support\Facades\DB::table('academic_periods')->insert(['academic_session_id' => $sessionId, 'month' => $m, 'created_at' => $now, 'updated_at' => $now]);
                $created++;
            }
        }
    }
    $total = \Illuminate\Support\Facades\DB::table('academic_periods')->count();
    return response()->json(['message' => "Done. Created {$created} periods. Total periods: {$total}"]);
});

// ── Root ──────────────────────────────────────────────────────────────────
Route::get('/', fn() => redirect()->route('login'));

// ═══════════════════════════════════════════════════════════════════════════
// LECTURER PORTAL
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/teachers-portal/login',  [LecturerAuthController::class, 'loginForm'])->name('teachers-portal.login');
Route::post('/teachers-portal/login', [LecturerAuthController::class, 'login'])->name('teachers-portal.login.submit');
Route::get('/teacher-portal/login',   fn() => redirect()->route('teachers-portal.login'));

Route::prefix('lecturer')->name('lecturer.')->group(function () {
    Route::get('/login',  [LecturerAuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [LecturerAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout',[LecturerAuthController::class, 'logout'])->name('logout');

    Route::middleware(['auth', 'role:lecturer'])->group(function () {
        Route::get('/dashboard', [LecturerDashboardController::class, 'index'])->name('dashboard');

        Route::get('/profile',  [LecturerProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [LecturerProfileController::class, 'update'])->name('profile.update');

        Route::get('/attendance/create', [LecturerAttendanceController::class, 'create'])->name('attendance.create');
        Route::post('/attendance',       [LecturerAttendanceController::class, 'store'])->name('attendance.store');

        Route::get('/exams',        [LecturerExamController::class, 'index'])->name('exams.index');
        Route::get('/exams/create', [LecturerExamController::class, 'create'])->name('exams.create');
        Route::post('/exams',       [LecturerExamController::class, 'store'])->name('exams.store');

        // Exam Question Document Upload (lecturer)
        Route::get('/exam-questions',                         [\App\Http\Controllers\Lecturer\ExamQuestionController::class, 'index'])->name('exam-questions.index');
        Route::post('/exam-questions',                        [\App\Http\Controllers\Lecturer\ExamQuestionController::class, 'store'])->name('exam-questions.store');
        Route::get('/exam-questions/{examQuestion}/download', [\App\Http\Controllers\Lecturer\ExamQuestionController::class, 'download'])->name('exam-questions.download');
        Route::get('/exam-questions/{examQuestion}/view',     [\App\Http\Controllers\Lecturer\ExamQuestionController::class, 'view'])->name('exam-questions.view');
        Route::delete('/exam-questions/{examQuestion}',       [\App\Http\Controllers\Lecturer\ExamQuestionController::class, 'destroy'])->name('exam-questions.destroy');

        // Report card downloads
        Route::get('/exams/report-card',          [LecturerExamController::class, 'downloadReportCard'])->name('exams.report-card');
        Route::post('/exams/bulk-report-cards',   [LecturerExamController::class, 'bulkDownloadReportCards'])->name('exams.bulk-report-cards');

        // Class teacher reports
        Route::get('/class-performance',          [LecturerExamController::class, 'classPerformance'])->name('class-performance.index');
        Route::get('/class-performance/pdf',      [LecturerExamController::class, 'classPerformancePdf'])->name('class-performance.pdf');

        // Batch score entry
        Route::get('/courses/{courseId}/scores/{programId}',         [LecturerExamController::class, 'batchScores'])->name('courses.scores');
        Route::post('/courses/{courseId}/scores/{programId}',        [LecturerExamController::class, 'batchScoresSave'])->name('courses.scores.save');
        Route::get('/courses/{courseId}/scores/{programId}/template',[LecturerExamController::class, 'batchTemplate'])->name('courses.scores.template');

        Route::get('/reports',                     [LecturerReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/student/{student}',   [LecturerReportController::class, 'student'])->name('reports.student');

        Route::get('/notifications',  [LecturerNotificationController::class, 'index'])->name('notifications.index');
        Route::post('/notifications', [LecturerNotificationController::class, 'store'])->name('notifications.store');

        // Timetable (read-only)
        Route::get('/timetable', [\App\Http\Controllers\Lecturer\TimetableController::class, 'index'])->name('timetable.index');
        // Calendar (read-only)
        Route::get('/calendar',  [\App\Http\Controllers\Lecturer\TimetableController::class, 'calendar'])->name('calendar.index');
        // Scheme of Learning
        Route::get('/scheme-of-learning/courses', [\App\Http\Controllers\Lecturer\SchemeOfLearningController::class, 'coursesByProgram'])->name('scheme-of-learning.courses');
        Route::get('/scheme-of-learning/{schemeOfLearning}/pdf', [\App\Http\Controllers\Lecturer\SchemeOfLearningController::class, 'downloadPdf'])->name('scheme-of-learning.pdf');
        Route::resource('scheme-of-learning', \App\Http\Controllers\Lecturer\SchemeOfLearningController::class)->only(['index','create','store','edit','update','destroy']);
        // Class register (class teachers only)
        Route::get('/class-register',  [\App\Http\Controllers\Lecturer\ClassRegisterController::class, 'index'])->name('class-register.index');
        Route::post('/class-register', [\App\Http\Controllers\Lecturer\ClassRegisterController::class, 'store'])->name('class-register.store');

        // Work Logs
        Route::get('/work-logs',                    [\App\Http\Controllers\Lecturer\TeacherWorkLogController::class, 'index'])->name('work-logs.index');
        Route::post('/work-logs',                   [\App\Http\Controllers\Lecturer\TeacherWorkLogController::class, 'store'])->name('work-logs.store');
        Route::delete('/work-logs/{teacherWorkLog}',[\App\Http\Controllers\Lecturer\TeacherWorkLogController::class, 'destroy'])->name('work-logs.destroy');

        // Student Reports (class teacher fills conduct/attitude/ratings)
        Route::get('/student-reports',                  [\App\Http\Controllers\Lecturer\StudentReportController::class, 'index'])->name('student-reports.index');
        Route::get('/student-reports/{student}/edit',   [\App\Http\Controllers\Lecturer\StudentReportController::class, 'edit'])->name('student-reports.edit');
        Route::post('/student-reports/{student}',       [\App\Http\Controllers\Lecturer\StudentReportController::class, 'save'])->name('student-reports.save');
        Route::post('/student-reports/bulk',            [\App\Http\Controllers\Lecturer\StudentReportController::class, 'bulkSave'])->name('student-reports.bulk-save');

        // API: students by program (used by JS dropdowns in exams/report page)
        Route::get('/api/programs/{programId}/students', function($programId) {
            $students = \App\Models\Student::with('user')
                ->where('program_id', $programId)
                ->forExams()
                ->orderBy('student_id')
                ->get()
                ->map(fn($s) => [
                    'id'        => $s->id,
                    'student_id'=> $s->student_id,
                    'full_name' => $s->user->full_name ?? '',
                ]);
            return response()->json($students);
        })->name('lecturer.api.program.students');
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// ADMIN PANEL
// ═══════════════════════════════════════════════════════════════════════════
Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::get('/profile',  [\App\Http\Controllers\Admin\AdminProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [\App\Http\Controllers\Admin\AdminProfileController::class, 'update'])->name('profile.update');

    // Settings
    Route::get('/settings',                    [\App\Http\Controllers\Admin\AdminSettingsController::class, 'show'])->name('settings');
    Route::post('/settings',                   [\App\Http\Controllers\Admin\AdminSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/login-background',  [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateLoginBackground'])->name('settings.update-background');
    Route::post('/settings/grading',           [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateGrading'])->name('settings.update-grading');
    Route::post('/settings/permissions',       [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updatePermissions'])->name('settings.update-permissions');
    Route::post('/settings/sba',               [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateSba'])->name('settings.update-sba');
    Route::post('/settings/academic-expectations', [\App\Http\Controllers\Admin\AdminSettingsController::class, 'updateAcademicExpectations'])->name('settings.update-academic-expectations');

    // Theme
    Route::post('/theme',       [\App\Http\Controllers\Admin\ThemeController::class, 'save'])->name('theme.save');
    Route::post('/theme/reset', [\App\Http\Controllers\Admin\ThemeController::class, 'reset'])->name('theme.reset');

    // Students — static/named routes MUST come before Route::resource so that
    // literal path segments like "bulk-promote" are not swallowed by {student}.
    Route::get('/students/template',                       [StudentController::class, 'template'])->name('students.template');
    Route::post('/students/import',                        [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/search',                         [StudentController::class, 'search'])->name('students.search');
    Route::get('/students/next-id',                        [StudentController::class, 'nextStudentId'])->name('students.next-id');
    Route::get('/students/bulk-promote',                   [PromotionController::class, 'bulkPromotePage'])->name('students.bulk-promote');
    Route::post('/students/bulk-promote',                  [PromotionController::class, 'bulkPromote'])->name('students.bulk-promote.submit');
    Route::get('/students/bulk-promotion',                 [PromotionController::class, 'bulkIndex'])->name('students.bulk-promotion');
    Route::post('/students/bulk-repeat',                   [PromotionController::class, 'bulkRepeat'])->name('students.bulk-repeat');
    Route::post('/students/bulk-approve',                  [StudentController::class, 'bulkApprove'])->name('students.bulk-approve');
    Route::delete('/students/bulk-delete',                 [StudentController::class, 'bulkDelete'])->name('students.bulk-delete');
    Route::resource('students', StudentController::class)->only(['index','create','store','show','edit','update','destroy']);
    Route::get('/students/{student}/photo',                [StudentController::class, 'photo'])->name('students.photo');
    Route::post('/students/{student}/reset-password',      [StudentController::class, 'resetPassword'])->name('students.reset-password');
    Route::post('/students/{student}/approve',             [StudentController::class, 'approve'])->name('students.approve');
    Route::post('/students/{student}/promote',             [PromotionController::class, 'promote'])->name('students.promote');
    Route::post('/students/{student}/demote',              [PromotionController::class, 'demote'])->name('students.demote');

    // Class Teacher Assignments
    Route::get('/class-teachers',            [\App\Http\Controllers\Admin\ClassTeacherController::class, 'index'])->name('class-teachers.index');
    Route::post('/class-teachers/assign',    [\App\Http\Controllers\Admin\ClassTeacherController::class, 'assign'])->name('class-teachers.assign');
    Route::post('/class-teachers/remove',    [\App\Http\Controllers\Admin\ClassTeacherController::class, 'remove'])->name('class-teachers.remove');

    // Student Reports — conduct, attitude, personality ratings
    Route::get('/student-reports',                                      [\App\Http\Controllers\Admin\StudentReportController::class, 'reportIndex'])->name('student-reports.index');
    Route::get('/student-reports/{student}/edit',                       [\App\Http\Controllers\Admin\StudentReportController::class, 'reportEdit'])->name('student-reports.edit');
    Route::post('/student-reports/{student}',                           [\App\Http\Controllers\Admin\StudentReportController::class, 'reportSave'])->name('student-reports.save');
    // Attribute Definitions (admin manages the list)
    Route::get('/student-reports/attributes',                           [\App\Http\Controllers\Admin\StudentReportController::class, 'attributeIndex'])->name('student-reports.attributes');
    Route::post('/student-reports/attributes',                          [\App\Http\Controllers\Admin\StudentReportController::class, 'attributeStore'])->name('student-reports.attributes.store');
    Route::put('/student-reports/attributes/{attributeDefinition}',     [\App\Http\Controllers\Admin\StudentReportController::class, 'attributeUpdate'])->name('student-reports.attributes.update');
    Route::delete('/student-reports/attributes/{attributeDefinition}',  [\App\Http\Controllers\Admin\StudentReportController::class, 'attributeDestroy'])->name('student-reports.attributes.destroy');
    Route::post('/student-reports/attributes/reorder',                  [\App\Http\Controllers\Admin\StudentReportController::class, 'attributeReorder'])->name('student-reports.attributes.reorder');

    // Staff Portal Management (Accountant, Headmaster, etc.)
    Route::get('/staff-portal-users',                              [\App\Http\Controllers\Admin\StaffPortalController::class, 'index'])->name('staff-portal-users.index');
    Route::post('/staff-portal-users',                             [\App\Http\Controllers\Admin\StaffPortalController::class, 'store'])->name('staff-portal-users.store');
    Route::put('/staff-portal-users/{staffPortalUser}',            [\App\Http\Controllers\Admin\StaffPortalController::class, 'update'])->name('staff-portal-users.update');
    Route::delete('/staff-portal-users/{staffPortalUser}',         [\App\Http\Controllers\Admin\StaffPortalController::class, 'destroy'])->name('staff-portal-users.destroy');

    // Lecturers
    Route::resource('lecturers', LecturerController::class)->only(['index','create','store','show','edit','update','destroy']);

    // Teacher Work Monitoring
    Route::get('/work-monitoring', [\App\Http\Controllers\Admin\TeacherWorkLogController::class, 'index'])->name('work-monitoring.index');
    Route::get('/work-monitoring/logs/{log}', [\App\Http\Controllers\Admin\TeacherWorkLogController::class, 'show'])->name('work-monitoring.log.show');
    Route::post('/work-monitoring/courses/{course}/expectations', [\App\Http\Controllers\Admin\TeacherWorkLogController::class, 'updateExpectations'])->name('work-monitoring.expectations');
    Route::post('/work-monitoring/logs/{log}/comment', [\App\Http\Controllers\Admin\TeacherWorkLogController::class, 'comment'])->name('work-monitoring.comment');
    Route::delete('/work-monitoring/logs/{log}', [\App\Http\Controllers\Admin\TeacherWorkLogController::class, 'destroyLog'])->name('work-monitoring.log.destroy');

    // Programs
    Route::resource('programs', ProgramController::class)->only(['index','create','store','show','edit','update','destroy']);
    Route::get('/api/programs/{program}/courses',  [ProgramController::class, 'courses'])->name('api.program.courses');
    Route::get('/api/programs/{program}/students', [ProgramController::class, 'students'])->name('api.program.students');

    // Branches & Admins
    Route::resource('branches',        BranchController::class)->except(['show']);
    Route::resource('branches.admins', BranchAdminController::class)->except(['show'])->scoped();
    Route::resource('admins',          AdminManagementController::class)->except(['show']);

    // Courses
    Route::resource('courses', CourseController::class)->only(['index','create','store','show','edit','update','destroy']);

    // ── Scheme of Learning ─────────────────────────────────────────────────────
    Route::get('/scheme-of-learning/courses',          [\App\Http\Controllers\Admin\SchemeOfLearningController::class, 'coursesByProgram'])->name('scheme-of-learning.courses');
    Route::get('/scheme-of-learning/{schemeOfLearning}/pdf', [\App\Http\Controllers\Admin\SchemeOfLearningController::class, 'downloadPdf'])->name('scheme-of-learning.pdf');
    Route::resource('scheme-of-learning', \App\Http\Controllers\Admin\SchemeOfLearningController::class)->only(['index','create','store','edit','update','destroy']);

    // ── Timetable ──────────────────────────────────────────────────────────────
    Route::get('/timetable',                   [\App\Http\Controllers\Admin\TimetableController::class, 'index'])->name('timetable.index');
    Route::post('/timetable',                  [\App\Http\Controllers\Admin\TimetableController::class, 'store'])->name('timetable.store');
    Route::delete('/timetable/{timetable}',    [\App\Http\Controllers\Admin\TimetableController::class, 'destroy'])->name('timetable.destroy');
    Route::get('/timetable/json',              [\App\Http\Controllers\Admin\TimetableController::class, 'json'])->name('timetable.json');
    Route::get('/timetable/courses',           [\App\Http\Controllers\Admin\TimetableController::class, 'coursesByProgram'])->name('timetable.courses');

    // ── School Calendar ────────────────────────────────────────────────────────
    Route::get('/calendar',                    [\App\Http\Controllers\Admin\SchoolEventController::class, 'index'])->name('calendar.index');
    Route::post('/calendar',                   [\App\Http\Controllers\Admin\SchoolEventController::class, 'store'])->name('calendar.store');
    Route::put('/calendar/{event}',            [\App\Http\Controllers\Admin\SchoolEventController::class, 'update'])->name('calendar.update');
    Route::delete('/calendar/{event}',         [\App\Http\Controllers\Admin\SchoolEventController::class, 'destroy'])->name('calendar.destroy');
    Route::get('/calendar/feed',               [\App\Http\Controllers\Admin\SchoolEventController::class, 'feed'])->name('calendar.feed');

    // ── Daily Fees ─────────────────────────────────────────────────────────────
    Route::get('/daily-fees',                  [\App\Http\Controllers\Admin\DailyFeesController::class, 'index'])->name('daily-fees.index');
    Route::post('/daily-fees',                 [\App\Http\Controllers\Admin\DailyFeesController::class, 'store'])->name('daily-fees.store');
    Route::post('/daily-fees/unmark',          [\App\Http\Controllers\Admin\DailyFeesController::class, 'unmark'])->name('daily-fees.unmark');
    Route::post('/daily-fees/exemption',       [\App\Http\Controllers\Admin\DailyFeesController::class, 'setExemption'])->name('daily-fees.exemption');
    Route::post('/daily-fees/exemption/remove',[\App\Http\Controllers\Admin\DailyFeesController::class, 'removeExemption'])->name('daily-fees.exemption.remove');
    Route::get('/daily-fees/report',           [\App\Http\Controllers\Admin\DailyFeesController::class, 'dailyReport'])->name('daily-fees.report');
    Route::get('/daily-fees/monthly',          [\App\Http\Controllers\Admin\DailyFeesController::class, 'monthlyReport'])->name('daily-fees.monthly');
    Route::get('/daily-fees/pdf',              [\App\Http\Controllers\Admin\DailyFeesController::class, 'dailyReportPdf'])->name('daily-fees.pdf');

    // Academic Sessions (required for attendance period selection)
    Route::get('/academic-sessions',                      [\App\Http\Controllers\Admin\AcademicSessionController::class, 'index'])->name('academic-sessions.index');
    Route::post('/academic-sessions',                     [\App\Http\Controllers\Admin\AcademicSessionController::class, 'store'])->name('academic-sessions.store');
    Route::delete('/academic-sessions/{session}',         [\App\Http\Controllers\Admin\AcademicSessionController::class, 'destroy'])->name('academic-sessions.destroy');
    Route::post('/academic-sessions/set-active',          [\App\Http\Controllers\Admin\AcademicSessionController::class, 'setActivePeriod'])->name('academic-sessions.set-active');
    Route::post('/academic-sessions/deactivate',          [\App\Http\Controllers\Admin\AcademicSessionController::class, 'deactivatePeriod'])->name('academic-sessions.deactivate');

    // Attendance
    Route::get('/attendance',          [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/create',   [AttendanceController::class, 'create'])->name('attendance.create');
    Route::get('/attendance/template', [AttendanceController::class, 'template'])->name('attendance.template');
    Route::post('/attendance',         [AttendanceController::class, 'store'])->name('attendance.store');
    Route::post('/attendance/import',  [AttendanceController::class, 'import'])->name('attendance.import');
    Route::get('/attendance/students', [AttendanceController::class, 'searchStudents'])->name('attendance.students');

    // Exams & Report Cards
    Route::get('/exams',                           [ExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/bulk',                      [ExamController::class, 'bulkDownload'])->name('exams.bulk');
    Route::post('/exams/bulk-pdf',                 [ExamController::class, 'bulkPdf'])->name('exams.bulk.pdf');
    Route::get('/exams/create',                    [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams',                          [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{student}/{program}',       [ExamController::class, 'show'])->name('exams.show');
    Route::get('/exams/{student}/{program}/pdf',   [ExamController::class, 'pdf'])->name('exams.pdf');

    // Admin — Exam Question Documents (uploaded by teachers)
    Route::get('/exam-questions',                              [\App\Http\Controllers\Admin\ExamQuestionController::class, 'index'])->name('exam-questions.index');
    Route::post('/exam-questions/{examQuestion}/approve',      [\App\Http\Controllers\Admin\ExamQuestionController::class, 'approve'])->name('exam-questions.approve');
    Route::get('/exam-questions/{examQuestion}/download',      [\App\Http\Controllers\Admin\ExamQuestionController::class, 'download'])->name('exam-questions.download');
    Route::get('/exam-questions/{examQuestion}/view',          [\App\Http\Controllers\Admin\ExamQuestionController::class, 'view'])->name('exam-questions.view');
    Route::post('/exam-questions/{examQuestion}/comment',      [\App\Http\Controllers\Admin\ExamQuestionController::class, 'comment'])->name('exam-questions.comment');
    Route::delete('/exam-questions/{examQuestion}',            [\App\Http\Controllers\Admin\ExamQuestionController::class, 'destroy'])->name('exam-questions.destroy');
    Route::get('/exams/{student}/{program}/excel', [ExamController::class, 'excel'])->name('exams.excel');

    // Exam Sheet
    Route::get('/exams-sheet',                   [\App\Http\Controllers\Admin\ExamSheetController::class, 'index'])->name('exams.sheet');
    Route::post('/exams-sheet',                  [\App\Http\Controllers\Admin\ExamSheetController::class, 'store'])->name('exams.sheet.store');
    Route::get('/exams-sheet/export',            [\App\Http\Controllers\Admin\ExamSheetController::class, 'export'])->name('exams.sheet.export');
    Route::get('/exams-sheet/merit',             [\App\Http\Controllers\Admin\ExamSheetController::class, 'exportMerit'])->name('exams.sheet.merit');
    Route::post('/exams-sheet/import',           [\App\Http\Controllers\Admin\ExamSheetController::class, 'import'])->name('exams.sheet.import');
    Route::post('/exams-sheet/percentages',      [\App\Http\Controllers\Admin\ExamSheetController::class, 'savePercentages'])->name('exams.sheet.percentages');
    Route::get('/exams-sheet/download-sba',      [\App\Http\Controllers\Admin\ExamSheetController::class, 'downloadSba'])->name('exams.sheet.download-sba');
    Route::get('/exams-sheet/download-sba-pdf',  [\App\Http\Controllers\Admin\ExamSheetController::class, 'downloadSbaPdf'])->name('exams.sheet.download-sba-pdf');

    // Fees
    Route::get('/fees',                              [FeesController::class, 'index'])->name('fees.index');
    Route::get('/fees/template',                     [FeesController::class, 'template'])->name('fees.template');
    Route::post('/fees/import',                      [FeesController::class, 'import'])->name('fees.import');
    Route::post('/fees/set-fee',                     [FeesController::class, 'setFee'])->name('fees.set-fee');
    Route::post('/fees/mode',                        [FeesController::class, 'saveFeeMode'])->name('fees.mode');
    Route::post('/fees/payments',                    [FeesController::class, 'store'])->name('fees.store');
    Route::delete('/fees/payments/{payment}',        [FeesController::class, 'destroy'])->name('fees.destroy');
    Route::delete('/fees/reset/{student}',           [FeesController::class, 'resetStudent'])->name('fees.reset-student');
    Route::post('/fees/reset-bulk',                  [FeesController::class, 'resetBulk'])->name('fees.reset-bulk');
    Route::post('/fees/set-student/{student}',       [FeesController::class, 'setStudentFee'])->name('fees.set-student');
    Route::post('/fees/set-bulk',                    [FeesController::class, 'setBulkFees'])->name('fees.set-bulk');
    Route::delete('/fees/clear-student/{student}',   [FeesController::class, 'clearStudentFee'])->name('fees.clear-student');
    Route::post('/fees/clear-bulk',                  [FeesController::class, 'clearBulkFees'])->name('fees.clear-bulk');

    // Portal Settings
    Route::get('/portal-settings',  [PortalSettingsController::class, 'index'])->name('portal-settings');
    Route::post('/portal-settings', [PortalSettingsController::class, 'toggle'])->name('portal-settings.toggle');

    // Admin Guide
    Route::get('/guide', [\App\Http\Controllers\Admin\GuideController::class, 'index'])->name('guide');

    // Notifications
    Route::get('/notifications',                    [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications',                   [NotificationController::class, 'store'])->name('notifications.store');
    Route::delete('/notifications/{notification}',  [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // Reports
    Route::get('/reports',                    [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/students',           [ReportController::class, 'students'])->name('reports.students');
    Route::get('/reports/attendance',         [ReportController::class, 'attendance'])->name('reports.attendance');
    Route::get('/reports/financial',          [ReportController::class, 'financial'])->name('reports.financial');
    Route::get('/reports/programs',           [ReportController::class, 'programs'])->name('reports.programs');
    Route::get('/reports/courses',            [ReportController::class, 'courses'])->name('reports.courses');
    Route::get('/reports/financial/pdf',      [ReportController::class, 'financialPdf'])->name('reports.financial.pdf');
    Route::get('/reports/students/pdf',       [ReportController::class, 'studentsPdf'])->name('reports.students.pdf');
    Route::get('/reports/attendance/pdf',     [ReportController::class, 'attendancePdf'])->name('reports.attendance.pdf');
    Route::get('/reports/programs/pdf',       [ReportController::class, 'programsPdf'])->name('reports.programs.pdf');
    Route::get('/reports/courses/pdf',        [ReportController::class, 'coursesPdf'])->name('reports.courses.pdf');
    Route::get('/reports/financial/excel',    [ReportController::class, 'financialExcel'])->name('reports.financial.excel');
    Route::get('/reports/students/excel',     [ReportController::class, 'studentsExcel'])->name('reports.students.excel');
    Route::get('/reports/attendance/excel',   [ReportController::class, 'attendanceExcel'])->name('reports.attendance.excel');
    Route::get('/reports/programs/excel',     [ReportController::class, 'programsExcel'])->name('reports.programs.excel');
    Route::get('/reports/courses/excel',      [ReportController::class, 'coursesExcel'])->name('reports.courses.excel');
    Route::get('/reports/students/preview',   [ReportController::class, 'studentsPreview'])->name('reports.students.preview');
    Route::get('/reports/attendance/preview', [ReportController::class, 'attendancePreview'])->name('reports.attendance.preview');
    Route::get('/reports/financial/preview',  [ReportController::class, 'financialPreview'])->name('reports.financial.preview');
    Route::get('/reports/programs/preview',   [ReportController::class, 'programsPreview'])->name('reports.programs.preview');
    Route::get('/reports/courses/preview',    [ReportController::class, 'coursesPreview'])->name('reports.courses.preview');

    // ── New comprehensive report routes ─────────────────────────────────────
    Route::get('/reports/hub',                     [ReportController::class, 'hub'])->name('reports.hub');

    // Class performance
    Route::get('/reports/class-performance',       [ReportController::class, 'classPerformance'])->name('reports.class-performance');
    Route::get('/reports/class-performance/pdf',   [ReportController::class, 'classPerformancePdf'])->name('reports.class-performance.pdf');

    // Student academic report + transcript
    Route::get('/reports/student-academic',        [ReportController::class, 'studentAcademic'])->name('reports.student-academic');
    Route::get('/reports/student-academic/pdf',    [ReportController::class, 'studentAcademicPdf'])->name('reports.student-academic.pdf');
    Route::get('/reports/transcript',              [ReportController::class, 'transcript'])->name('reports.transcript');
    Route::get('/reports/transcript/pdf',          [ReportController::class, 'transcriptPdf'])->name('reports.transcript.pdf');

    // Attendance summary
    Route::get('/reports/attendance-summary',      [ReportController::class, 'attendanceSummary'])->name('reports.attendance-summary');
    Route::get('/reports/attendance-summary/pdf',  [ReportController::class, 'attendanceSummaryPdf'])->name('reports.attendance-summary.pdf');

    // Teacher mark submission
    Route::get('/reports/mark-submission',         [ReportController::class, 'markSubmission'])->name('reports.mark-submission');

    // Fee statement
    Route::get('/reports/fee-statement',           [ReportController::class, 'feeStatement'])->name('reports.fee-statement');
    Route::get('/reports/fee-statement/pdf',       [ReportController::class, 'feeStatementPdf'])->name('reports.fee-statement.pdf');

    // Student performance
    Route::get('/reports/student-performance',      [ReportController::class, 'studentPerformance'])->name('reports.student-performance');
    Route::get('/reports/student-performance/pdf',  [ReportController::class, 'studentPerformancePdf'])->name('reports.student-performance.pdf');

    // Bulk PDF download
    Route::post('/reports/bulk-academic-pdf',       [ReportController::class, 'bulkAcademicPdf'])->name('reports.bulk-academic-pdf');
    Route::post('/reports/bulk-attendance-pdf',     [ReportController::class, 'bulkAttendancePdf'])->name('reports.bulk-attendance-pdf');

    // Document history / audit trail
    Route::get('/reports/documents',               [ReportController::class, 'documentHistory'])->name('reports.documents');

    // ── Website CMS ────────────────────────────────────────────────────────
    Route::prefix('website')->name('website.')->group(function () {
        Route::get('/',              [WebsiteContentController::class, 'dashboard'])->name('dashboard');
        Route::get('/site-settings', [WebsiteContentController::class, 'siteSettings'])->name('site-settings');
        Route::post('/site-settings',[WebsiteContentController::class, 'siteSettingsSave'])->name('site-settings.save');
        Route::get('/blog',              [WebsiteContentController::class, 'blogIndex'])->name('blog.index');
        Route::get('/blog/create',       [WebsiteContentController::class, 'blogCreate'])->name('blog.create');
        Route::post('/blog',             [WebsiteContentController::class, 'blogStore'])->name('blog.store');
        Route::get('/blog/{post}/edit',  [WebsiteContentController::class, 'blogEdit'])->name('blog.edit');
        Route::put('/blog/{post}',       [WebsiteContentController::class, 'blogUpdate'])->name('blog.update');
        Route::delete('/blog/{post}',    [WebsiteContentController::class, 'blogDestroy'])->name('blog.destroy');
        Route::get('/events',             [WebsiteContentController::class, 'eventsIndex'])->name('events.index');
        Route::get('/events/create',      [WebsiteContentController::class, 'eventsCreate'])->name('events.create');
        Route::post('/events',            [WebsiteContentController::class, 'eventsStore'])->name('events.store');
        Route::get('/events/{event}/edit',[WebsiteContentController::class, 'eventsEdit'])->name('events.edit');
        Route::put('/events/{event}',     [WebsiteContentController::class, 'eventsUpdate'])->name('events.update');
        Route::delete('/events/{event}',  [WebsiteContentController::class, 'eventsDestroy'])->name('events.destroy');
        Route::get('/gallery',                   [WebsiteContentController::class, 'galleryIndex'])->name('gallery.index');
        Route::post('/gallery/upload',           [WebsiteContentController::class, 'galleryUpload'])->name('gallery.upload');
        Route::delete('/gallery/{image}',        [WebsiteContentController::class, 'galleryDestroy'])->name('gallery.destroy');
        Route::post('/gallery/categories',       [WebsiteContentController::class, 'galleryCategoryStore'])->name('gallery.category.store');
        Route::get('/staff',              [WebsiteContentController::class, 'staffIndex'])->name('staff.index');
        Route::get('/staff/create',       [WebsiteContentController::class, 'staffCreate'])->name('staff.create');
        Route::post('/staff',             [WebsiteContentController::class, 'staffStore'])->name('staff.store');
        Route::get('/staff/{member}/edit',[WebsiteContentController::class, 'staffEdit'])->name('staff.edit');
        Route::put('/staff/{member}',     [WebsiteContentController::class, 'staffUpdate'])->name('staff.update');
        Route::delete('/staff/{member}',  [WebsiteContentController::class, 'staffDestroy'])->name('staff.destroy');
        Route::get('/testimonials',                        [WebsiteContentController::class, 'testimonialsIndex'])->name('testimonials.index');
        Route::post('/testimonials',                       [WebsiteContentController::class, 'testimonialsStore'])->name('testimonials.store');
        Route::patch('/testimonials/{testimonial}/toggle', [WebsiteContentController::class, 'testimonialsToggle'])->name('testimonials.toggle');
        Route::delete('/testimonials/{testimonial}',       [WebsiteContentController::class, 'testimonialsDestroy'])->name('testimonials.destroy');
        Route::get('/programs',               [WebsiteContentController::class, 'programsIndex'])->name('programs.index');
        Route::get('/programs/create',        [WebsiteContentController::class, 'programsCreate'])->name('programs.create');
        Route::post('/programs',              [WebsiteContentController::class, 'programsStore'])->name('programs.store');
        Route::post('/programs/sync',         [WebsiteContentController::class, 'programsSync'])->name('programs.sync');
        Route::get('/programs/{program}/edit',[WebsiteContentController::class, 'programsEdit'])->name('programs.edit');
        Route::put('/programs/{program}',     [WebsiteContentController::class, 'programsUpdate'])->name('programs.update');
        Route::delete('/programs/{program}',  [WebsiteContentController::class, 'programsDestroy'])->name('programs.destroy');
        Route::get('/messages',         [WebsiteContentController::class, 'messagesIndex'])->name('messages.index');
        Route::get('/messages/{message}',[WebsiteContentController::class, 'messagesShow'])->name('messages.show');
        Route::delete('/messages/{message}',[WebsiteContentController::class, 'messagesDestroy'])->name('messages.destroy');
        Route::get('/applications',                      [WebsiteContentController::class, 'applicationsIndex'])->name('applications.index');
        Route::get('/applications/{application}',        [WebsiteContentController::class, 'applicationsShow'])->name('applications.show');
        Route::patch('/applications/{application}/status',[WebsiteContentController::class, 'applicationsUpdateStatus'])->name('applications.status');
        Route::delete('/applications/{application}',     [WebsiteContentController::class, 'applicationsDestroy'])->name('applications.destroy');
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// STAFF PORTAL (Accountant, Headmaster, Headteacher, Deputy)
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('staff-portal')->name('staff-portal.')->group(function () {
    // Auth — no middleware on these
    Route::get('/login',               [\App\Http\Controllers\StaffPortal\AuthController::class, 'loginForm'])->name('login');
    Route::get('/login/{role}',        [\App\Http\Controllers\StaffPortal\AuthController::class, 'loginForm'])->name('login.role');
    Route::post('/login',              [\App\Http\Controllers\StaffPortal\AuthController::class, 'login'])->name('login.submit');
    Route::post('/logout',             [\App\Http\Controllers\StaffPortal\AuthController::class, 'logout'])->name('logout');

    // All authenticated staff portal routes
    Route::middleware('staff.auth')->group(function () {
        Route::get('/dashboard',            [\App\Http\Controllers\StaffPortal\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/student-reports',      [\App\Http\Controllers\StaffPortal\SectionController::class, 'studentReports'])->name('student-reports');
        Route::get('/profile',              [\App\Http\Controllers\StaffPortal\ProfileController::class, 'edit'])->name('profile');
        Route::post('/profile',             [\App\Http\Controllers\StaffPortal\ProfileController::class, 'update'])->name('profile.update');

        // Direct section access (branch-scoped, permission-gated)
        Route::get('/students',             [\App\Http\Controllers\StaffPortal\SectionController::class, 'students'])->name('students');
        Route::get('/attendance',           [\App\Http\Controllers\StaffPortal\SectionController::class, 'attendance'])->name('attendance');
        Route::get('/exams',                [\App\Http\Controllers\StaffPortal\SectionController::class, 'exams'])->name('exams');
        Route::get('/exam-questions',       [\App\Http\Controllers\StaffPortal\SectionController::class, 'examQuestions'])->name('exam-questions');
        Route::get('/fees',                 [\App\Http\Controllers\StaffPortal\SectionController::class, 'fees'])->name('fees');
        Route::get('/fees/statement/pdf',   [\App\Http\Controllers\StaffPortal\SectionController::class, 'feeStatementPdf'])->name('fees.statement.pdf');
        Route::get('/reports',              [\App\Http\Controllers\StaffPortal\SectionController::class, 'reports'])->name('reports');
        Route::get('/reports/class',        [\App\Http\Controllers\StaffPortal\SectionController::class, 'classPerformance'])->name('reports.class');
        Route::get('/reports/class/pdf',    [\App\Http\Controllers\StaffPortal\SectionController::class, 'classPerformancePdf'])->name('reports.class.pdf');
        Route::get('/daily-fees',           [\App\Http\Controllers\StaffPortal\SectionController::class, 'dailyFees'])->name('daily-fees');
        Route::get('/programs',             [\App\Http\Controllers\StaffPortal\SectionController::class, 'programs'])->name('programs');
        Route::get('/courses',              [\App\Http\Controllers\StaffPortal\SectionController::class, 'courses'])->name('courses');
        Route::get('/lecturers',            [\App\Http\Controllers\StaffPortal\SectionController::class, 'lecturers'])->name('lecturers');
        Route::get('/class-teachers',       [\App\Http\Controllers\StaffPortal\SectionController::class, 'classTeachers'])->name('class-teachers');
        Route::get('/scheme-of-learning',   [\App\Http\Controllers\StaffPortal\SectionController::class, 'schemeOfLearning'])->name('scheme-of-learning');
        Route::get('/work-logs',            [\App\Http\Controllers\StaffPortal\SectionController::class, 'workLogs'])->name('work-logs');
        Route::get('/timetable',            [\App\Http\Controllers\StaffPortal\SectionController::class, 'timetable'])->name('timetable');
        Route::get('/calendar',             [\App\Http\Controllers\StaffPortal\SectionController::class, 'calendar'])->name('calendar');
        Route::get('/notifications',        [\App\Http\Controllers\StaffPortal\SectionController::class, 'notifications'])->name('notifications');
        Route::get('/promotions',           [\App\Http\Controllers\StaffPortal\SectionController::class, 'promotions'])->name('promotions');
        Route::get('/academic-sessions',    [\App\Http\Controllers\StaffPortal\SectionController::class, 'academicSessions'])->name('academic-sessions');
        Route::get('/branches',             [\App\Http\Controllers\StaffPortal\SectionController::class, 'branches'])->name('branches');
        Route::get('/settings',             [\App\Http\Controllers\StaffPortal\SectionController::class, 'settings'])->name('settings');
    });
});

// Convenience aliases used in portal-settings page
Route::get('/accountant-portal/login',  fn() => redirect()->route('staff-portal.login.role', 'accountant'))->name('accountant-portal.login');
Route::get('/headmaster-portal/login',  fn() => redirect()->route('staff-portal.login.role', 'headmaster'))->name('headmaster-portal.login');
Route::get('/headteacher-portal/login', fn() => redirect()->route('staff-portal.login.role', 'headteacher'))->name('headteacher-portal.login');
Route::get('/secretary-portal/login',   fn() => redirect()->route('staff-portal.login.role', 'secretary'))->name('secretary-portal.login');

// ═══════════════════════════════════════════════════════════════════════════
// OWNER PANEL DEBUG — shows real error on school show/edit
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/owner-debug/school/{id}', function(int $id) {
    try {
        $tenant = \App\Models\Tenant::findOrFail($id);
        $out = ['tenant' => $tenant->only(['id','name','slug','status','plan_id'])];

        try {
            $out['plan'] = $tenant->plan?->name ?? 'no plan';
        } catch (\Throwable $e) { $out['plan_error'] = $e->getMessage(); }

        try {
            $out['features_count'] = \App\Models\Feature::count();
        } catch (\Throwable $e) { $out['features_error'] = $e->getMessage(); }

        try {
            $out['tenant_features'] = \Illuminate\Support\Facades\DB::table('tenant_features')
                ->where('tenant_id', $id)->count();
        } catch (\Throwable $e) { $out['tenant_features_error'] = $e->getMessage(); }

        try {
            $out['payments_count'] = $tenant->payments()->count();
        } catch (\Throwable $e) { $out['payments_error'] = $e->getMessage(); }

        try {
            $out['backups_count'] = $tenant->backups()->count();
        } catch (\Throwable $e) { $out['backups_error'] = $e->getMessage(); }

        try {
            $features = \App\Models\Feature::orderBy('group')->orderBy('sort_order')->get()->groupBy('group');
            $out['feature_groups'] = $features->keys()->toArray();
        } catch (\Throwable $e) { $out['feature_groups_error'] = $e->getMessage(); }

        return response()->json($out, 200, [], JSON_PRETTY_PRINT);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'file'  => $e->getFile() . ':' . $e->getLine(),
            'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
        ], 500);
    }
})->middleware('owner.auth');

// ═══════════════════════════════════════════════════════════════════════════
// TENANT DB SETUP — run migrations + create admin for a specific school
// Visit: /setup-tenant/{slug}/{secret}
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/setup-tenant/{slug}/{secret}', function(string $slug, string $secret) {
    $validSecret = env('ADMIN_SETUP_SECRET', 'setup-admin-2026');

    if ($secret !== $validSecret) {
        abort(403, 'Invalid secret.');
    }

    $tenant = \App\Models\Tenant::where('slug', $slug)
        ->orWhere('subdomain', $slug)
        ->first();

    if (!$tenant) {
        return response()->json(['status' => 'error', 'message' => "Tenant '{$slug}' not found."], 404);
    }

    $results = [];

    // 1. Switch to tenant's DB
    try {
        \Illuminate\Support\Facades\DB::purge('tenant');
        \Illuminate\Support\Facades\Config::set('database.connections.tenant', $tenant->dbConfig());
        \Illuminate\Support\Facades\DB::setDefaultConnection('tenant');
        \Illuminate\Support\Facades\DB::connection('tenant')->getPdo();
        $results['db_connection'] = 'OK — connected to ' . $tenant->db_name;
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\DB::setDefaultConnection('pgsql');
        return response()->json([
            'status'  => 'error',
            'message' => 'Could not connect to tenant database: ' . $e->getMessage(),
        ], 500);
    }

    // 2. Run migrations against tenant DB
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', [
            '--force'    => true,
            '--database' => 'tenant',
        ]);
        $results['migrations'] = trim(\Illuminate\Support\Facades\Artisan::output()) ?: 'Migrations ran (no output)';
    } catch (\Exception $e) {
        $results['migrations'] = 'FAILED: ' . $e->getMessage();
    }

    // 3. Seed SettingsSeeder against tenant DB
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class'    => 'SettingsSeeder',
            '--database' => 'tenant',
            '--force'    => true,
        ]);
        $results['settings_seeder'] = 'OK';
    } catch (\Exception $e) {
        $results['settings_seeder'] = 'FAILED: ' . $e->getMessage();
    }

    // 4. Create the school's super admin
    $name     = request('name', env('TENANT_ADMIN_NAME', ''));
    $email    = request('email', env('TENANT_ADMIN_EMAIL', ''));
    $password = request('password', env('TENANT_ADMIN_PASSWORD', ''));

    if (empty($name) || empty($email) || empty($password)) {
        $results['admin'] = 'SKIPPED — pass ?name=X&email=X&password=X in the URL, or set TENANT_ADMIN_NAME/EMAIL/PASSWORD env vars';
    } else {
        try {
            $exists = \Illuminate\Support\Facades\DB::connection('tenant')
                ->table('users')
                ->where('email', $email)
                ->exists();

            if ($exists) {
                $results['admin'] = "SKIPPED — admin '{$email}' already exists in tenant DB";
            } else {
                \Illuminate\Support\Facades\DB::connection('tenant')->table('users')->insert([
                    'full_name'      => $name,
                    'email'          => $email,
                    'password'       => \Illuminate\Support\Facades\Hash::make($password),
                    'role'           => 'admin',
                    'is_super_admin' => true,
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ]);
                $results['admin'] = "OK — super admin '{$email}' created in tenant DB";
            }
        } catch (\Exception $e) {
            $results['admin'] = 'FAILED: ' . $e->getMessage();
        }
    }

    // 5. Reset to central DB
    \Illuminate\Support\Facades\DB::setDefaultConnection('pgsql');
    \Illuminate\Support\Facades\DB::purge('tenant');

    return response()->json([
        'status'       => 'done',
        'school'       => $tenant->name,
        'login_url'    => url('/school/' . $tenant->slug . '/login'),
        'results'      => $results,
    ], 200, [], JSON_PRETTY_PRINT);
});

// ═══════════════════════════════════════════════════════════════════════════
// ONE-TIME OWNER ACCOUNT CREATION — visit URL then remove from code
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/create-owner/{secret}', function(string $secret) {
    $validSecret = env('ADMIN_SETUP_SECRET', 'setup-admin-2026');

    if ($secret !== $validSecret) {
        abort(403, 'Invalid secret.');
    }

    $name     = env('OWNER_NAME', '');
    $email    = env('OWNER_EMAIL', '');
    $password = env('OWNER_PASSWORD', '');

    if (empty($name) || empty($email) || empty($password)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'OWNER_NAME, OWNER_EMAIL and OWNER_PASSWORD env vars must be set on Render.',
        ], 400);
    }

    if (\App\Models\OwnerUser::where('email', $email)->exists()) {
        return response()->json([
            'status'  => 'already_exists',
            'message' => "Owner with email '{$email}' already exists. Login at /owner/login.",
        ]);
    }

    $owner = \App\Models\OwnerUser::create([
        'name'     => $name,
        'email'    => $email,
        'password' => \Illuminate\Support\Facades\Hash::make($password),
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => "Owner account '{$email}' created. Go to /owner/login and sign in. REMOVE this route after use.",
        'id'      => $owner->id,
    ]);
});

// ═══════════════════════════════════════════════════════════════════════════
// ONE-TIME ADMIN CREATION — visit URL then remove from code
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/create-admin/{secret}', function(string $secret) {
    // Change this secret to something only you know
    $validSecret = env('ADMIN_SETUP_SECRET', 'setup-admin-2026');

    if ($secret !== $validSecret) {
        abort(403, 'Invalid secret.');
    }

    $name     = env('INITIAL_SUPER_ADMIN_NAME', '');
    $email    = env('INITIAL_SUPER_ADMIN_EMAIL', '');
    $password = env('INITIAL_SUPER_ADMIN_PASSWORD', '');

    if (empty($name) || empty($email) || empty($password)) {
        return response()->json([
            'status'  => 'error',
            'message' => 'INITIAL_SUPER_ADMIN_NAME, INITIAL_SUPER_ADMIN_EMAIL and INITIAL_SUPER_ADMIN_PASSWORD env vars must be set on Render.',
        ], 400);
    }

    if (\App\Models\User::where('email', $email)->exists()) {
        return response()->json([
            'status'  => 'already_exists',
            'message' => "Admin with email '{$email}' already exists. Login at /login.",
        ]);
    }

    $user = \App\Models\User::create([
        'full_name'      => $name,
        'email'          => $email,
        'password'       => \Illuminate\Support\Facades\Hash::make($password),
        'role'           => 'admin',
        'is_super_admin' => true,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => "Super admin '{$email}' created. Go to /login and sign in. REMOVE this route after use.",
        'id'      => $user->id,
    ]);
});

// ═══════════════════════════════════════════════════════════════════════════
// ONE-TIME SETUP ROUTE — remove after use
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/setup-school/{secret}', function(string $secret) {
    if ($secret !== 'focschool2026reset') {
        abort(403, 'Invalid secret.');
    }

    // Prevent running twice — if a super admin already exists, block it
    if (\App\Models\User::where('is_super_admin', true)->exists()) {
        return response()->json(['status' => 'blocked', 'message' => 'Setup already completed. Super admin already exists.'], 403);
    }

    try {
        // Wipe all data but keep structure
        \Illuminate\Support\Facades\DB::statement('SET session_replication_role = replica'); // disable FK checks (Postgres)
        $tables = \Illuminate\Support\Facades\DB::select("SELECT tablename FROM pg_tables WHERE schemaname = 'public'");
        foreach ($tables as $t) {
            if ($t->tablename === 'migrations') continue;
            try { \Illuminate\Support\Facades\DB::table($t->tablename)->truncate(); } catch (\Throwable $e) {}
        }
        \Illuminate\Support\Facades\DB::statement('SET session_replication_role = DEFAULT');

        // Run migrations
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

        // Seed settings only
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SettingsSeeder', '--force' => true]);

        // Create super admin
        $user = \App\Models\User::create([
            'full_name'      => 'Administrator',
            'email'          => 'admin@focschool.com',
            'password'       => bcrypt('admin123'),
            'role'           => 'admin',
            'is_super_admin' => true,
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'School system reset and ready. Login with admin@focschool.com / admin123',
            'migrate' => \Illuminate\Support\Facades\Artisan::output(),
            'admin_id'=> $user->id,
            'WARNING' => 'Remove this route from routes/web.php immediately after use!',
        ]);

    } catch (\Throwable $e) {
        return response()->json(['status'=>'error','message'=>$e->getMessage()], 500);
    }
});

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC DOCUMENT VERIFICATION
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/verify/{uuid}', [\App\Http\Controllers\DocumentVerificationController::class, 'show'])->name('verify.document');

// ═══════════════════════════════════════════════════════════════════════════
// AUTH
// ═══════════════════════════════════════════════════════════════════════════
require __DIR__.'/auth.php';

// ═══════════════════════════════════════════════════════════════════════════
// STUDENT PORTAL
// ═══════════════════════════════════════════════════════════════════════════
Route::get('/student-portal/login',  [PortalController::class, 'loginForm'])->name('student-portal.login');
Route::post('/student-portal/login', [PortalController::class, 'login'])->name('student-portal.login.submit');

Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('/login',  [PortalController::class, 'loginForm'])->name('login');
    Route::post('/login', [PortalController::class, 'login'])->name('login.submit');
    Route::post('/logout',[PortalController::class, 'logout'])->name('logout');

    Route::middleware('portal.auth')->group(function () {
        Route::get('/dashboard',               [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/photo/{student}',         [PortalController::class, 'photo'])->name('photo');
        Route::get('/background/{student}',    [PortalController::class, 'backgroundPhoto'])->name('background');
        Route::get('/report-card/{program}',   [PortalController::class, 'reportCard'])->name('report-card');
        Route::get('/report-card/{program}/pdf',[PortalController::class, 'reportCardPdf'])->name('report-card.pdf');
        Route::post('/notifications/read-all', [PortalController::class, 'markAllRead'])->name('notifications.read-all');
        Route::get('/change-password',         [PortalController::class, 'changePasswordForm'])->name('change-password');
        Route::post('/change-password',        [PortalController::class, 'updatePassword'])->name('change-password.update');
    });
});

// ═══════════════════════════════════════════════════════════════════════════
// PUBLIC SCHOOL WEBSITE
// ═══════════════════════════════════════════════════════════════════════════
Route::name('website.')->group(function () {
    Route::get('/website',             [WebsiteController::class, 'home'])->name('home');
    Route::get('/website/about',       [WebsiteController::class, 'about'])->name('about');
    Route::get('/website/academics',   [WebsiteController::class, 'academics'])->name('academics');
    Route::get('/website/admissions',  [WebsiteController::class, 'admissions'])->name('admissions');
    Route::get('/website/apply',       [WebsiteController::class, 'applyForm'])->name('apply');
    Route::post('/website/apply',      [WebsiteController::class, 'applySubmit'])->name('apply.submit');
    Route::get('/website/gallery',     [WebsiteController::class, 'gallery'])->name('gallery');
    Route::get('/website/events',      [WebsiteController::class, 'events'])->name('events');
    Route::get('/website/blog',        [WebsiteController::class, 'blog'])->name('blog');
    Route::get('/website/blog/{slug}', [WebsiteController::class, 'blogDetail'])->name('blog.detail');
    Route::get('/website/staff',       [WebsiteController::class, 'staff'])->name('staff');
    Route::get('/website/contact',     [WebsiteController::class, 'contact'])->name('contact');
    Route::post('/website/contact',    [WebsiteController::class, 'contactSubmit'])->name('contact.submit');
    Route::get('/website/portal',      [WebsiteController::class, 'portal'])->name('portal');
});

// ═══════════════════════════════════════════════════════════════════════════
// SCHOOL SLUG-BASED LOGIN (Option A — single Render URL, multiple schools)
// Each school accesses: /school/{slug}/login
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('school')->name('school.')->group(function () {
    Route::get('/{slug}/login',  [\App\Http\Controllers\Owner\SchoolAccessController::class, 'showLogin'])->name('login');
    Route::post('/{slug}/login', [\App\Http\Controllers\Owner\SchoolAccessController::class, 'login'])->name('login.submit');
    Route::post('/logout',       [\App\Http\Controllers\Owner\SchoolAccessController::class, 'logout'])->name('logout');
});

// ═══════════════════════════════════════════════════════════════════════════
// OWNER PANEL
// ═══════════════════════════════════════════════════════════════════════════
Route::prefix('owner')->name('owner.')->group(function () {

    // Public — auth pages (no middleware)
    Route::get('/login',  [\App\Http\Controllers\Owner\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Owner\AuthController::class, 'login'])->name('login.submit');

    // Protected — all owner dashboard routes
    Route::middleware('owner.auth')->group(function () {
        Route::post('/logout', [\App\Http\Controllers\Owner\AuthController::class, 'logout'])->name('logout');

        // Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Owner\DashboardController::class, 'index'])->name('dashboard');

        // Schools (Tenants)
        Route::resource('schools', \App\Http\Controllers\Owner\TenantController::class);
        Route::post('/schools/{tenant}/status',   [\App\Http\Controllers\Owner\TenantController::class, 'updateStatus'])->name('schools.status');
        Route::post('/schools/{tenant}/features', [\App\Http\Controllers\Owner\TenantController::class, 'updateFeatures'])->name('schools.features');

        // Plans
        Route::resource('plans', \App\Http\Controllers\Owner\PlanController::class);

        // Features
        Route::resource('features', \App\Http\Controllers\Owner\FeatureController::class)->only(['index', 'store', 'update', 'destroy']);

        // Payments
        Route::get('/payments',                         [\App\Http\Controllers\Owner\PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create',                  [\App\Http\Controllers\Owner\PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments',                        [\App\Http\Controllers\Owner\PaymentController::class, 'store'])->name('payments.store');
        Route::get('/payments/{payment}',               [\App\Http\Controllers\Owner\PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/receipt',       [\App\Http\Controllers\Owner\PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::post('/payments/{payment}/email-receipt',[\App\Http\Controllers\Owner\PaymentController::class, 'emailReceipt'])->name('payments.email-receipt');
        Route::delete('/payments/{payment}',            [\App\Http\Controllers\Owner\PaymentController::class, 'destroy'])->name('payments.destroy');

        // Backups
        Route::get('/backups',                          [\App\Http\Controllers\Owner\BackupController::class, 'index'])->name('backups.index');
        Route::post('/backups/{tenant}',                [\App\Http\Controllers\Owner\BackupController::class, 'create'])->name('backups.create');
        Route::get('/backups/{backup}/download',        [\App\Http\Controllers\Owner\BackupController::class, 'download'])->name('backups.download');
        Route::post('/backups/{backup}/cloud-push',     [\App\Http\Controllers\Owner\BackupController::class, 'cloudPush'])->name('backups.cloud-push');
        Route::post('/backups/{backup}/email',          [\App\Http\Controllers\Owner\BackupController::class, 'email'])->name('backups.email');
        Route::post('/backups/{backup}/restore',        [\App\Http\Controllers\Owner\BackupController::class, 'restore'])->name('backups.restore');
        Route::delete('/backups/{backup}',              [\App\Http\Controllers\Owner\BackupController::class, 'destroy'])->name('backups.destroy');

        // Audit Logs
        Route::get('/audit-logs', [\App\Http\Controllers\Owner\AuditLogController::class, 'index'])->name('audit-logs.index');

        // Owner Profile
        Route::get('/profile',  [\App\Http\Controllers\Owner\ProfileController::class, 'edit'])->name('profile.edit');
        Route::post('/profile', [\App\Http\Controllers\Owner\ProfileController::class, 'update'])->name('profile.update');
    });
});
