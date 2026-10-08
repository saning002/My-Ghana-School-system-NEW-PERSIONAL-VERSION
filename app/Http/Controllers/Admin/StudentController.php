<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Program;
use App\Models\ChurchBranch;
use App\Models\Enrollment;
use App\Imports\StudentImport;
use App\Exports\StudentTemplateExport;
use App\Services\FeeCalculationService;
use App\Services\StudentIdService;
use App\Services\DemotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function __construct(
        private FeeCalculationService $feeService,
        private StudentIdService $studentIdService,
        private DemotionService $demotionService
    ) {}

    public function index()
    {
        $user   = auth()->user();
        $search = request('search');

        $query = Student::with(['user', 'program', 'churchBranch']);

        // Branch admin (has a branch assigned) only sees their own branch
        // Super admin / admin with no branch sees everyone
        if ($user && $user->isBranchAdmin()) {
            $query->where('church_branch_id', $user->church_branch_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u
                      ->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email',   'like', "%{$search}%"));
            });
        }

        if (request('program_id')) {
            $query->where('program_id', request('program_id'));
        }

        if (request('status')) {
            $query->where('status', request('status'));
        }

        $students = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $programs = \App\Models\Program::orderBy('sequence')->get();

        return view('admin.students.index', compact('students', 'programs'));
    }

    /**
     * Download an Excel template for bulk student import.
     */
    /**
     * Download an Excel template for bulk student import.
     */
    public function template(Request $request)
    {
        return Excel::download(new StudentTemplateExport(), 'Students_Template.xlsx');
    }

    protected function getFileHeaders($filePath, string $extension = 'xlsx')
    {
        try {
            // IOFactory::load() detects type from extension — if filePath is a raw
            // /tmp/phpXXXX path with no extension, we must specify the reader explicitly
            $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            if (empty($ext)) {
                $ext = strtolower($extension);
            }
            $readerType = match($ext) {
                'xlsx'  => 'Xlsx',
                'xls'   => 'Xls',
                'csv'   => 'Csv',
                default => 'Xlsx',
            };
            $reader    = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($readerType);
            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load($filePath);
            $worksheet   = $spreadsheet->getActiveSheet();
            $headers = [];
            foreach ($worksheet->getRowIterator(1, 1) as $row) {
                foreach ($row->getCellIterator() as $cell) {
                    $val = trim((string)$cell->getValue());
                    if ($val) {
                        $headers[] = strtolower($val);
                    }
                }
            }
            return $headers;
        } catch (\Throwable $e) {
            return [];
        }
    }

    protected function hasProgramField($headers)
    {
        $programVariants = ['program', 'program_name', 'program_id', 'program_code'];
        foreach ($headers as $h) {
            if (in_array($h, $programVariants)) {
                return true;
            }
        }
        return false;
    }

    public function import(Request $request)
    {
        $request->validate([
            'sheet_file'         => 'nullable|file|mimes:xlsx,csv,xls,zip',
            'sheet_temp_path'    => 'nullable|string',
            'default_program_id' => 'nullable|exists:programs,id',
        ]);

        try {
            $defaultProgramId = $request->input('default_program_id');
            $uploadedFile     = $request->file('sheet_file');

            if (! $uploadedFile) {
                return back()->withErrors(['sheet_file' => 'Please upload a file to import.']);
            }

            $originalFileName = $uploadedFile->getClientOriginalName();
            $extension        = strtolower($uploadedFile->getClientOriginalExtension());

            // ── For ZIP: extract to PHP's system /tmp (survives the request) ──
            $importFilePath  = null;
            $tempCleanupPath = null;

            if ($extension === 'zip') {
                // Use sys_get_temp_dir() — always writable on any host
                $tempDir = sys_get_temp_dir() . '/import_' . uniqid();
                @mkdir($tempDir, 0755, true);

                $zip = new \ZipArchive;
                if ($zip->open($uploadedFile->getRealPath()) !== true) {
                    @rmdir($tempDir);
                    return back()->withErrors(['error' => 'Failed to open the uploaded ZIP file.']);
                }
                $zip->extractTo($tempDir);
                $zip->close();

                $dataFile = null;
                foreach (\Illuminate\Support\Facades\File::allFiles($tempDir) as $f) {
                    if (in_array(strtolower($f->getExtension()), ['xlsx', 'xls', 'csv'])) {
                        $dataFile = $f->getPathname();
                        break;
                    }
                }

                if (! $dataFile) {
                    \Illuminate\Support\Facades\File::deleteDirectory($tempDir);
                    return back()->withErrors(['error' => 'No Excel or CSV file found inside the ZIP.']);
                }

                $importFilePath  = $dataFile;
                $tempCleanupPath = $tempDir;
            } else {
                // For XLSX/CSV: use PHP's uploaded-file temp path directly —
                // no need to copy it anywhere, getRealPath() is always available
                // during the same request on any host (including Render).
                $importFilePath = $uploadedFile->getRealPath();
            }

            // If no program provided and the file has no Program column,
            // ask the user — but since our modal always includes the program
            // selector this branch should rarely be hit.
            if (! $defaultProgramId) {
                $headers = $this->getFileHeaders($importFilePath, $extension);
                if (! $this->hasProgramField($headers)) {
                    $programs = Program::orderBy('sequence')->get();
                    // We can't round-trip the file, so store it in the system tmp
                    // directory and pass the path so the modal can re-submit it.
                    $tmpCopy = sys_get_temp_dir() . '/import_pending_' . uniqid() . '.' . $extension;
                    copy($importFilePath, $tmpCopy);
                    return back()->with('select_program_for_import', [
                        'file'            => $originalFileName,
                        'programs'        => $programs,
                        'sheet_temp_path' => $tmpCopy,
                    ])->withInput();
                }
            }

            // Pass the logged-in admin's branch so imported students belong to the right branch
            $adminBranchId = auth()->user()?->church_branch_id ?? null;
            $import = new StudentImport(null, $defaultProgramId, $adminBranchId);

            // When using getRealPath() the path has no extension (e.g. /tmp/phpABC123)
            // so we must pass the reader type explicitly — Maatwebsite/Excel detects
            // the type from the extension; without it, it throws "No ReaderType detected".
            $readerType = match($extension) {
                'xlsx'  => \Maatwebsite\Excel\Excel::XLSX,
                'xls'   => \Maatwebsite\Excel\Excel::XLS,
                'csv'   => \Maatwebsite\Excel\Excel::CSV,
                default => \Maatwebsite\Excel\Excel::XLSX,
            };

            Excel::import($import, $importFilePath, null, $readerType);

            // Clean up the ZIP extract directory if we created one
            if ($tempCleanupPath && is_dir($tempCleanupPath)) {
                \Illuminate\Support\Facades\File::deleteDirectory($tempCleanupPath);
            }

            $successCount = $import->getSuccessCount();
            $failureCount = $import->getFailureCount();
            $errors       = $import->getErrors();

            if ($successCount === 0 && $failureCount > 0) {
                $errorMsg = 'Failed to import any students. ';
                if (count($errors) > 0) {
                    $errorMsg .= 'Issues: ' . implode('; ', array_slice($errors, 0, 5));
                    if (count($errors) > 5) {
                        $errorMsg .= ' (and ' . (count($errors) - 5) . ' more)';
                    }
                }
                return back()->withErrors(['error' => $errorMsg]);
            }

            $message = "Students imported successfully! Imported: {$successCount}";
            if ($failureCount > 0) {
                $message .= " | Failed/Skipped: {$failureCount}";
            }

            $credentialsUrl = $import->getCredentialsPublicPath();
            if ($credentialsUrl) {
                return redirect()->route('admin.students.index')
                    ->with('success', $message)
                    ->with('import_credentials_url', url($credentialsUrl));
            }

            return redirect()->route('admin.students.index')->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to import students. Error: ' . $e->getMessage()]);
        }
    }

    public function create()
    {
        $programs = Program::with('courses')->orderBy('sequence')->get();
        $branches = ChurchBranch::all();
        return view('admin.students.create', compact('programs', 'branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email',
            'password'         => 'required|string|min:6',
            'portal_password'  => 'nullable|string|min:6',
            'date_of_birth'    => 'nullable|date',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'student_id'       => 'nullable|string|unique:students,student_id',
            'admission_date'   => 'required|date',
            'program_id'       => 'required|exists:programs,id',
            'church_branch_id' => 'nullable|exists:church_branches,id',
            'branch_new'       => 'nullable|string|max:255',
            'photo'            => 'nullable|image|max:2048',
            'background_photo' => 'nullable|image|max:5120',
            'signal'           => 'nullable|string|max:100',
            'position'         => 'nullable|string|max:100',
            'date_issued'      => 'nullable|date',
        ], [
            'portal_password.min' => 'Portal password must be at least 6 characters.',
        ]);

        // Resolve branch: use existing or create new
        $branchId = $request->church_branch_id;
        if (!$branchId && $request->filled('branch_new')) {
            $branch   = ChurchBranch::firstOrCreate(
                ['name' => trim($request->branch_new)],
                [
                    'location' => trim($request->branch_new),
                    'code'     => $request->filled('branch_code')
                        ? strtoupper(trim($request->branch_code))
                        : strtoupper(substr(trim($request->branch_new), 0, 2)),
                ]
            );
            $branchId = $branch->id;
        }
        if (!$branchId) {
            return back()->withErrors(['church_branch_id' => 'Please select or enter a School Branch.'])->withInput();
        }

        $photoPath = null;
        $backgroundPhotoPath = null;
        $createdStudent = null;

        DB::transaction(function () use ($request, $branchId, &$photoPath, &$backgroundPhotoPath, &$createdStudent) {
            $user = User::create([
                'full_name'      => $request->full_name,
                'email'          => $request->filled('email')
                    ? $request->email
                    : strtolower(str_replace(' ', '.', $request->full_name)) . '.' . time() . '@student.local',
                'password'       => Hash::make($request->password),
                'date_of_birth'  => $request->date_of_birth,
                'phone'          => $request->phone,
                'address'        => $request->address,
                'gender'         => $request->gender,
                'marital_status' => $request->marital_status,
                'nationality'    => $request->nationality,
                'role'           => 'student',
                // Admission form extra user fields
                'surname'        => $request->surname,
                'other_names'    => $request->other_names,
                'place_of_birth' => $request->place_of_birth,
                'religion'       => $request->religion,
                'first_language' => $request->first_language,
                'other_language' => $request->other_language,
            ]);

            $photoPath = $this->uploadStudentPhoto($request, 'photo', 'students/photos');
            $backgroundPhotoPath = $this->uploadStudentPhoto($request, 'background_photo', 'students/backgrounds');

            // Always generate student ID server-side using StudentIdService (new permanent format)
            // Build a temporary student object so the service can read admission_date + branch
            $tempStudent = new Student([
                'admission_date'   => $request->admission_date,
                'church_branch_id' => $branchId,
            ]);
            $program   = Program::find($request->program_id);
            $studentId = $this->studentIdService->generateStudentId($tempStudent, $program);

            $student = Student::create([
                'user_id'          => $user->id,
                'student_id'       => $studentId,
                'admission_date'   => $request->admission_date,
                'program_id'       => $request->program_id,
                'church_branch_id' => $branchId,
                'status'           => 'active',   // students are always active
                'is_approved'      => false,       // NEW badge shown until admin approves
                'class_applying_for'       => $request->class_applying_for,
                'passport_photo_attached'  => $request->boolean('passport_photo_attached'),
                'photo'            => $photoPath,
                'background_photo' => $backgroundPhotoPath,
                'signal'           => $request->signal ?? 'H+R=P',
                'position'         => $request->position ?? 'Student',
                'date_issued'      => $request->date_issued ?? now()->toDateString(),
                'qualifications'   => $request->qualifications,
                'native_town'      => $request->native_town,
                'enrollment_year'  => $request->enrollment_year ?? date('Y'),
                'study_mode'       => $request->study_mode ?? 'full_time',
                'profession'       => $request->profession,
                'password'         => $request->filled('portal_password') ? Hash::make($request->portal_password) : null,
                // Section B
                'prev_school_name'         => $request->prev_school_name,
                'prev_school_address'      => $request->prev_school_address,
                'last_class_completed'     => $request->last_class_completed,
                'reason_for_leaving'       => $request->reason_for_leaving,
                'prev_reports_attached'    => $request->boolean('prev_reports_attached'),
                'transfer_letter_attached' => $request->boolean('transfer_letter_attached'),
                // Section C
                'father_name'       => $request->father_name,
                'father_occupation' => $request->father_occupation,
                'father_employer'   => $request->father_employer,
                'father_address'    => $request->father_address,
                'father_phone'      => $request->father_phone,
                // Section D
                'mother_name'       => $request->mother_name,
                'mother_occupation' => $request->mother_occupation,
                'mother_employer'   => $request->mother_employer,
                'mother_address'    => $request->mother_address,
                'mother_phone'      => $request->mother_phone,
                // Section E
                'guardian_name'         => $request->guardian_name,
                'guardian_relationship' => $request->guardian_relationship,
                'guardian_occupation'   => $request->guardian_occupation,
                'guardian_address'      => $request->guardian_address,
                'guardian_phone'        => $request->guardian_phone,
                // Section G
                'medical_condition'        => $request->medical_condition,
                'allergies'                => $request->allergies,
                'blood_group'              => $request->blood_group,
                'special_educational_needs'=> $request->special_educational_needs,
                'family_doctor'            => $request->family_doctor,
                'doctor_phone'             => $request->doctor_phone,
                // Section H
                'pickup_name'         => $request->pickup_name,
                'pickup_relationship' => $request->pickup_relationship,
                'pickup_phone'        => $request->pickup_phone,
                // Section I
                'admission_fee_paid' => $request->boolean('admission_fee_paid'),
                'furniture_fee_paid' => $request->boolean('furniture_fee_paid'),
                'toiletries_paid'    => $request->boolean('toiletries_paid'),
                'uniform_paid'       => $request->boolean('uniform_paid'),
                // Section J
                'consent_signed'       => $request->boolean('consent_signed'),
                'consent_guardian_name'=> $request->consent_guardian_name,
                'consent_date'         => $request->consent_date,
            ]);

            $createdStudent = $student;
        });

        // Auto-enroll the new student in all courses belonging to their program
        if ($createdStudent) {
            $year = date('Y') . '/' . (date('Y') + 1);
            $programCourses = \App\Models\Course::where('program_id', $createdStudent->program_id)->pluck('id');
            foreach ($programCourses as $courseId) {
                DB::table('enrollments')->updateOrInsert(
                    ['student_id' => $createdStudent->id, 'course_id' => $courseId, 'program_id' => $createdStudent->program_id],
                    ['year' => $year, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }

        $warning = '';
        if ($request->hasFile('photo') && !$photoPath) {
            $warning .= ' (Note: Student photo upload failed due to storage configuration)';
        }
        if ($request->hasFile('background_photo') && !$backgroundPhotoPath) {
            $warning .= ' (Note: Background photo upload failed due to storage configuration)';
        }

        return redirect()->route('admin.students.index')->with('success', 'Student registered successfully.' . $warning);
    }

    public function show(Student $student)
    {
        $promotionHistory = collect();
        $fees = ['total' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
        $programs = Program::orderBy('sequence')->get();

        try {
            $student->load([
                'user', 'program.courses', 'churchBranch',
                'enrollments.course', 'attendances.course',
                'payments',
                'examScores.course',
            ]);

            try {
                if (DB::connection()->getSchemaBuilder()->hasTable('promotion_histories')) {
                    $student->loadMissing('promotionHistory.fromProgram', 'promotionHistory.toProgram');
                    $promotionHistory = $student->relationLoaded('promotionHistory')
                        ? $student->promotionHistory : collect();
                }
            } catch (\Throwable $e) {
                $promotionHistory = collect();
            }

            $fees = $this->feeService->calculate($student);
        } catch (\Throwable $e) {
            // fallback to minimal safe loading when some relations fail
            $student->load(['user', 'program', 'churchBranch', 'attendances']);
            $promotionHistory = collect();
        }

        $attendanceStats = [
            'total'   => $student->attendances->count(),
            'present' => $student->attendances->where('status', 'present')->count(),
        ];
        $attendanceStats['rate'] = $attendanceStats['total'] > 0
            ? round(($attendanceStats['present'] / $attendanceStats['total']) * 100) : 0;

        return view('admin.students.show', compact('student', 'fees', 'programs', 'attendanceStats', 'promotionHistory'));
    }

    public function edit(Student $student)
    {
        $student->load('user');
        $programs = Program::with('courses')->orderBy('sequence')->get();
        $branches = ChurchBranch::all();
        $enrolledCourseIds = $student->enrollments->pluck('course_id')->toArray();
        return view('admin.students.edit', compact('student', 'programs', 'branches', 'enrolledCourseIds'));
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|unique:users,email,' . $student->user_id,
            'date_of_birth'    => 'nullable|date',
            'phone'            => 'nullable|string|max:20',
            'address'          => 'nullable|string',
            'gender'           => 'nullable|in:male,female,other',
            'marital_status'   => 'nullable|in:single,married,divorced,widowed',
            'nationality'      => 'nullable|string|max:255',
            'program_id'       => 'required|exists:programs,id',
            'church_branch_id' => 'nullable|exists:church_branches,id',
            'branch_new'       => 'nullable|string|max:255',
            'status'           => 'required|in:active,graduated,suspended,manifestation,withdrawn',
            'results_blocked'  => 'nullable|boolean',
            'portal_password'  => 'nullable|string|min:6',
            'photo'            => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'background_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'signal'           => 'nullable|string|max:100',
            'position'         => 'nullable|string|max:100',
            'date_issued'      => 'nullable|date',
            'qualifications'   => 'nullable|string|max:255',
            'native_town'      => 'nullable|string|max:255',
            'enrollment_year'  => 'nullable|string|max:10',
            'study_mode'       => 'nullable|in:full_time,part_time',
            'profession'       => 'nullable|string|max:255',
        ], [
            'full_name.required' => 'Full name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be valid',
            'email.unique' => 'Email already exists for another user',
            'program_id.required' => 'Program is required',
            'program_id.exists' => 'Selected program does not exist',
            'status.required' => 'Status is required',
            'portal_password.min' => 'Portal password must be at least 6 characters.',
            'photo.image' => 'Photo must be an image file',
            'photo.max' => 'Photo must not exceed 2MB',
            'background_photo.image' => 'Background photo must be an image file',
            'background_photo.max' => 'Background photo must not exceed 5MB',
        ]);

        // Resolve branch
        $branchId = $request->church_branch_id;
        if (!$branchId && $request->filled('branch_new')) {
            $branch   = ChurchBranch::firstOrCreate(
                ['name' => trim($request->branch_new)],
                ['location' => trim($request->branch_new)]
            );
            $branchId = $branch->id;
        }
        if (!$branchId) {
            return back()->withErrors(['church_branch_id' => 'Please select or enter a School Branch.'])->withInput();
        }

        $photoUploadFailed = false;
        $backgroundUploadFailed = false;

        try {
            $student->loadMissing('user');

            DB::transaction(function () use ($request, $student, $branchId, &$photoUploadFailed, &$backgroundUploadFailed) {
                // Update user data
                $userData = [
                    'full_name'      => $request->full_name,
                    'email'          => $request->email,
                    'date_of_birth'  => $request->date_of_birth,
                    'phone'          => $request->phone,
                    'address'        => $request->address,
                    'gender'         => $request->gender,
                    'marital_status' => $request->marital_status,
                    'nationality'    => $request->nationality,
                ];
                
                if (!$student->user->update($userData)) {
                    throw new \Exception('Failed to update user information');
                }

                // Prepare student updates
                $updates = [
                    'program_id'       => $request->program_id,
                    'church_branch_id' => $branchId,
                    'status'           => $request->status,
                    'results_blocked'  => $request->has('results_blocked'),
                    'signal'           => $request->signal,
                    'position'         => $request->position,
                    'date_issued'      => $request->date_issued,
                    'qualifications'   => $request->qualifications,
                    'native_town'      => $request->native_town,
                    'enrollment_year'  => $request->enrollment_year,
                    'study_mode'       => $request->study_mode,
                    'profession'       => $request->profession,
                ];

                // Handle photo upload
                if ($request->hasFile('photo')) {
                    try {
                        $newPhoto = $this->uploadStudentPhoto($request, 'photo', 'students/photos');
                        if ($newPhoto) {
                            if ($student->photo) {
                                try {
                                    if (filter_var($student->photo, FILTER_VALIDATE_URL)) {
                                        \App\Services\CloudinaryService::delete($student->photo);
                                    } elseif (Storage::disk('public')->exists($student->photo)) {
                                        Storage::disk('public')->delete($student->photo);
                                    }
                                } catch (\Throwable $delEx) {
                                    \Log::warning('Failed to delete old photo: ' . $delEx->getMessage());
                                }
                            }
                            $updates['photo'] = $newPhoto;
                        } else {
                            $photoUploadFailed = true;
                        }
                    } catch (\Throwable $e) {
                        $photoUploadFailed = true;
                        \Log::warning('Failed to process photo upload: ' . $e->getMessage());
                    }
                }

                // Handle background photo upload
                if ($request->hasFile('background_photo')) {
                    try {
                        $newBg = $this->uploadStudentPhoto($request, 'background_photo', 'students/backgrounds');
                        if ($newBg) {
                            if ($student->background_photo) {
                                try {
                                    if (filter_var($student->background_photo, FILTER_VALIDATE_URL)) {
                                        \App\Services\CloudinaryService::delete($student->background_photo);
                                    } elseif (Storage::disk('public')->exists($student->background_photo)) {
                                        Storage::disk('public')->delete($student->background_photo);
                                    }
                                } catch (\Throwable $delEx) {
                                    \Log::warning('Failed to delete old background photo: ' . $delEx->getMessage());
                                }
                            }
                            $updates['background_photo'] = $newBg;
                        } else {
                            $backgroundUploadFailed = true;
                        }
                    } catch (\Throwable $e) {
                        $backgroundUploadFailed = true;
                        \Log::warning('Failed to process background photo upload: ' . $e->getMessage());
                    }
                }

                // Update portal password if provided
                if ($request->filled('portal_password')) {
                    $updates['password'] = Hash::make($request->portal_password);
                }

                // Capture old program before update for enrollment logic
                $oldProgramId = $student->program_id;

                // Update student
                if (!$student->update($updates)) {
                    throw new \Exception('Failed to update student information');
                }

                // Sync course enrollments from checkbox selection
                $selectedCourseIds = $request->input('course_ids', []);
                $programId = $student->program_id;
                $year = date('Y') . '/' . (date('Y') + 1);

                // Get all courses for this program
                $programCourseIds = \App\Models\Course::where('program_id', $programId)->pluck('id')->toArray();

                // If program changed, auto-enroll in all new program courses
                if ($oldProgramId && $oldProgramId != $request->program_id) {
                    // Auto-enroll in all courses of the new program
                    foreach ($programCourseIds as $courseId) {
                        if (!in_array($courseId, $selectedCourseIds)) {
                            $selectedCourseIds[] = $courseId;
                        }
                    }
                }

                // Remove enrollments for unchecked courses (only within this program)
                DB::table('enrollments')
                    ->where('student_id', $student->id)
                    ->whereIn('course_id', $programCourseIds)
                    ->whereNotIn('course_id', $selectedCourseIds)
                    ->delete();

                // Add enrollments for newly checked courses
                foreach ($selectedCourseIds as $courseId) {
                    DB::table('enrollments')->updateOrInsert(
                        ['student_id' => $student->id, 'course_id' => $courseId, 'program_id' => $programId],
                        ['year' => $year, 'created_at' => now(), 'updated_at' => now()]
                    );
                }
            });

            $warning = '';
            if ($photoUploadFailed) {
                $warning .= ' (Note: Photo upload failed, kept previous photo)';
            }
            if ($backgroundUploadFailed) {
                $warning .= ' (Note: Background photo upload failed, kept previous background photo)';
            }

            return redirect()->route('admin.students.show', $student)->with('success', 'Student updated successfully. All changes have been saved.' . $warning);
        } catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Database error updating student', ['exception' => $e, 'student_id' => $student->id]);
            return back()->withInput()->with('error', 'A database error occurred. Please try again or contact support.');
        } catch (\Throwable $e) {
            \Log::error('Admin student update failed', ['exception' => $e, 'student_id' => $student->id]);
            return back()->withInput()->with('error', 'Unable to update student: ' . $e->getMessage() . ' Please check the form and try again.');
        }
    }

    public static function storePhotoFile($file, string $directory): ?string
    {
        try {
            if (\App\Services\CloudinaryService::isConfigured()) {
                $url = \App\Services\CloudinaryService::upload($file, $directory);
                if ($url) {
                    return $url;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("Cloudinary upload failed: " . $e->getMessage());
        }

        try {
            $fullDir = Storage::disk('public')->path($directory);
            if (! File::isDirectory($fullDir)) {
                File::makeDirectory($fullDir, 0775, true, true);
            }

            $stored = Storage::disk('public')->putFile($directory, $file);
            if ($stored) {
                return $stored;
            }
        } catch (\Throwable $e) {
            \Log::warning("Storage::disk('public') upload failed: " . $e->getMessage());
        }

        try {
            $ext = method_exists($file, 'getClientOriginalExtension') 
                ? $file->getClientOriginalExtension() 
                : $file->getExtension();
            if (!$ext) $ext = 'jpg';

            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetDir = public_path('uploads/' . $directory);
            if (! File::isDirectory($targetDir)) {
                File::makeDirectory($targetDir, 0775, true, true);
            }
            
            if (method_exists($file, 'move')) {
                $file->move($targetDir, $fileName);
            } else {
                File::copy($file->getRealPath(), $targetDir . '/' . $fileName);
            }
            return 'uploads/' . $directory . '/' . $fileName;
        } catch (\Throwable $e) {
            \Log::error("storePhotoFile fallback failed completely: " . $e->getMessage());
        }

        return null;
    }

    private function uploadStudentPhoto(Request $request, string $field, string $directory): ?string
    {
        if (! $request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);
        return self::storePhotoFile($file, $directory);
    }

    public function bulkApprove(Request $request)
    {
        $request->validate(['student_ids' => 'required|array', 'student_ids.*' => 'exists:students,id']);
        $count = Student::whereIn('id', $request->student_ids)->update([
            'is_approved' => true,
        ]);
        return redirect()->route('admin.students.index')->with('success', "{$count} student(s) approved successfully.");
    }

    /**
     * Approve a single student — removes their NEW badge.
     */
    public function approve(Student $student)
    {
        $student->update(['is_approved' => true]);
        return redirect()->route('admin.students.show', $student)
            ->with('success', $student->user->full_name . ' has been approved.');
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['student_ids' => 'required|array', 'student_ids.*' => 'exists:students,id']);
        $students = Student::whereIn('id', $request->student_ids)->get();
        $count = 0;
        DB::transaction(function () use ($students, &$count) {
            foreach ($students as $student) {
                $userId = $student->user_id;
                $student->delete();
                User::destroy($userId);
                $count++;
            }
        });
        return redirect()->route('admin.students.index')->with('success', "{$count} student(s) deleted successfully.");
    }

    public function destroy(Student $student)
    {
        DB::transaction(function () use ($student) {
            $userId = $student->user_id;
            $student->delete();
            User::destroy($userId);
        });

        return redirect()->route('admin.students.index')->with('success', 'Student removed successfully.');
    }

    /**
     * Serve a student photo directly from disk — no public/storage symlink needed.
     * Supports ?type=background for background photos.
     * Cloudinary URLs are redirected directly.
     */
    public function photo(Student $student, Request $request)
    {
        $type = $request->query('type', 'photo');
        $raw  = $type === 'background' ? $student->background_photo : $student->photo;

        if (! $raw) {
            abort(404);
        }

        // Any external URL — redirect directly to it
        if (filter_var($raw, FILTER_VALIDATE_URL)) {
            return redirect($raw);
        }

        $path = $this->resolvePhotoPath($raw);
        if ($path) {
            return response()->file($path);
        }

        abort(404);
    }

    /**
     * Resolve a stored photo path to an absolute filesystem path.
     */
    private function resolvePhotoPath(string $raw): ?string
    {
        $path = str_replace('\\', '/', trim($raw));

        // Strip known prefixes to get the disk-relative path
        foreach (['public/', '/storage/', 'storage/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = ltrim(substr($path, strlen($prefix)), '/');
                break;
            }
        }

        // Try storage/app/public disk (primary — persistent mount)
        $diskPath = Storage::disk('public')->path($path);
        if (File::exists($diskPath)) {
            return $diskPath;
        }

        // Try public/uploads fallback
        $uploadsPath = public_path('uploads/' . $path);
        if (File::exists($uploadsPath)) {
            return $uploadsPath;
        }

        // Try direct public path
        $publicPath = public_path($path);
        if (File::exists($publicPath)) {
            return $publicPath;
        }

        return null;
    }

    /**
     * Reset a student's portal password to a temporary generated password
     */
    public function resetPassword(Request $request, Student $student)
    {
        try {
            // Generate a random temporary password (8 characters)
            $temporaryPassword = \Illuminate\Support\Str::random(8);
            
            // Update student password (will be hashed by the 'hashed' cast)
            $student->update(['password' => $temporaryPassword]);
            
            return redirect()->route('admin.students.show', $student)
                ->with('success', "Password reset successfully. Temporary password: <strong>$temporaryPassword</strong> — Share this with the student securely.");
        } catch (\Exception $e) {
            return redirect()->route('admin.students.show', $student)
                ->with('error', 'Failed to reset password: ' . $e->getMessage());
        }
    }

    /**
     * Generate the next available student ID.
     * Format: {prefix}/XXXXXXXX — 8 unique alphanumeric chars, never repeated.
     * Same format used by bulk import.
     */
    public function nextStudentId(Request $request)
    {
        $branchId  = $request->get('branch_id');
        $programId = $request->get('program_id');

        if (! $branchId || ! $programId) {
            return response()->json(['id' => '']);
        }

        $branch  = \App\Models\ChurchBranch::find($branchId);
        $program = \App\Models\Program::find($programId);

        if (! $branch || ! $program) {
            return response()->json(['id' => '']);
        }

        $id = self::generateNextSystemId();
        return response()->json(['id' => $id]);
    }

    /**
     * Generate a unique student ID: {school_id_prefix}/XXXXXXXX
     * The 8-character suffix is alphanumeric (uppercase), guaranteed unique in the DB.
     * Used by both manual registration and bulk import.
     */
    public static function generateNextSystemId(): string
    {
        $prefix = rtrim(\App\Models\Setting::get('school_id_prefix', 'STU'), '/');

        $candidate = null;
        $attempts  = 0;

        do {
            // Generate 8 random uppercase alphanumeric characters
            $suffix    = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 8));
            $candidate = $prefix . '/' . $suffix;
            $attempts++;

            // Guard against infinite loop (essentially impossible but safe)
            if ($attempts > 10000) {
                // Fall back to a timestamp-based suffix
                $candidate = $prefix . '/' . strtoupper(base_convert(time() . rand(100, 999), 10, 36));
                break;
            }
        } while (\App\Models\Student::where('student_id', $candidate)->exists());

        return $candidate;
    }

    /**
     * Search students by name, student ID, or registration number.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $q = trim($request->get('q', ''));
        $programId = $request->get('program_id');

        if (!$programId && strlen($q) < 2) {
            return response()->json([]);
        }

        $students = Student::with(['user', 'program'])
            ->when($programId, fn($query) => $query->where('program_id', $programId))
            ->when($q, fn($query) => $query->where(function ($query) use ($q) {
                $query->whereHas('user', fn($user) => $user->where('full_name', 'like', "%{$q}%"))
                      ->orWhere('student_id', 'like', "%{$q}%");
            }))
            ->limit(15)
            ->get()
            ->map(fn($s) => [
                'id'         => $s->id,
                'full_name'  => $s->user->full_name ?? '—',
                'student_id' => $s->student_id,
                'program'    => $s->program->name ?? '—',
                'photo'      => $s->photo,
                'photo_url'  => $s->photo_url,
            ]);

        return response()->json($students);
    }
}

