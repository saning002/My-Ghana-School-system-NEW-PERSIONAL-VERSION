<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Student;
use App\Models\Program;
use App\Models\ChurchBranch;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
// NOTE: We deliberately do NOT use WithHeadingRow because it transforms
// headers like "Full Name (REQUIRED)" into "full_name_required_" which
// then never matches our lookup. We read row 0 as headers ourselves.

class StudentImport implements ToCollection
{
    protected $extractedPath;
    protected $defaultProgramId;
    protected ?int $defaultBranchId;
    protected int $successCount = 0;
    protected int $failureCount = 0;
    protected array $errors = [];
    protected array $credentials = [];
    protected ?string $credentialsPublicPath = null;

    public function __construct($extractedPath = null, $defaultProgramId = null, ?int $defaultBranchId = null)
    {
        $this->extractedPath    = $extractedPath;
        $this->defaultProgramId = $defaultProgramId;
        $this->defaultBranchId  = $defaultBranchId;
    }

    // ── Normalize a string to a bare alpha-numeric key ────────────────────
    private function n(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower($s));
    }

    // ── Pick the first matching column index given a list of candidates ────
    private function colIndex(array $headerMap, array $candidates): ?int
    {
        foreach ($candidates as $c) {
            $key = $this->n($c);
            // exact normalised match
            if (isset($headerMap[$key])) return $headerMap[$key];
            // prefix match — catches "Full Name (REQUIRED)" → fullnamerequired
            // which starts with "fullname"
            foreach ($headerMap as $k => $idx) {
                if (str_starts_with($k, $key) || str_starts_with($key, $k)) {
                    return $idx;
                }
            }
        }
        return null;
    }

    private function get(array $row, ?int $idx): string
    {
        if ($idx === null || ! isset($row[$idx])) return '';
        return trim((string) $row[$idx]);
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) return;

        // Row 0 = raw headers, Row 1+ = data
        $rawHeaders = $rows->first()->toArray();

        // Build normalised-key → column-index map
        $headerMap = [];
        foreach ($rawHeaders as $idx => $h) {
            if ($h !== null && $h !== '') {
                $headerMap[$this->n((string)$h)] = $idx;
            }
        }

        // Resolve each column index once
        $colName   = $this->colIndex($headerMap, ['fullname','full_name','name','studentname','student_name','fullname_required']);
        $colEmail  = $this->colIndex($headerMap, ['email','emailaddress','email_address','mail','e-mail']);
        $colPass   = $this->colIndex($headerMap, ['password','pass','passwd']);
        $colId     = $this->colIndex($headerMap, ['studentid','student_id','id','studentno','student_no']);
        $colDate   = $this->colIndex($headerMap, ['admissiondate','admission_date','admitted','admissiondateyyyymmdd','admissiondate_']);
        $colProg   = $this->colIndex($headerMap, ['program','programme','class','programname','program_name','programid','program_id','programcode','program_code']);
        $colBranch = $this->colIndex($headerMap, ['churchbranch','church_branch','branch','branchname','branch_name','ministrybranch']);
        $colPhone  = $this->colIndex($headerMap, ['phone','telephone','mobile','phonenumber','phone_number']);
        $colDob    = $this->colIndex($headerMap, ['dateofbirth','date_of_birth','dob','birthdate','birth_date','birthday']);
        $colAddr   = $this->colIndex($headerMap, ['address','homeaddress','home_address','residence']);
        $colGender = $this->colIndex($headerMap, ['gender','sex']);
        $colYear   = $this->colIndex($headerMap, ['enrollmentyear','enrollment_year','enrollyear','year']);
        $colMode   = $this->colIndex($headerMap, ['studymode','study_mode','mode']);
        $colPhoto  = $this->colIndex($headerMap, ['photofilename','photo_filename','photo','photofile','photo_file']);
        $colBg     = $this->colIndex($headerMap, ['backgroundphotofilename','background_photo_filename','bgphoto','backgroundphoto']);
        $colPortal = $this->colIndex($headerMap, ['portalpassword','portal_password','portalpass']);
        $colNation = $this->colIndex($headerMap, ['nationality','nation']);
        $colQuals  = $this->colIndex($headerMap, ['qualifications','qualification','quals']);
        $colTown   = $this->colIndex($headerMap, ['nativetown','native_town','hometown','town']);
        $colProf   = $this->colIndex($headerMap, ['profession','occupation','job']);

        $rowNumber = 2; // Excel row 1 = headers, row 2 = first data row

        foreach ($rows->slice(1) as $row) {
            $ra = $row->toArray();

            // Skip blank rows
            if (collect($ra)->every(fn($v) => $v === null || $v === '')) {
                $rowNumber++;
                continue;
            }

            try {
                $fullName = $this->get($ra, $colName);
                if (! $fullName) {
                    throw new \Exception("Row {$rowNumber}: Full Name is empty (first column must be the student's name).");
                }

                DB::beginTransaction();
                try {
                    // Email
                    $email = $this->get($ra, $colEmail);
                    if (! $email) {
                        $p     = explode(' ', $fullName);
                        $email = strtolower($p[0] ?? 'student') . '.'
                               . strtolower(end($p)) . '+' . Str::random(5) . '@student.local';
                    }
                    $base = $email; $c = 1;
                    while (User::where('email', $email)->exists()) {
                        $parts = explode('@', $base);
                        $email = $parts[0] . '+' . $c . '@' . ($parts[1] ?? 'student.local');
                        $c++;
                    }

                    // Password
                    $password = $this->get($ra, $colPass) ?: Str::random(8);

                    // Create user
                    $user = User::create([
                        'full_name'     => $fullName,
                        'email'         => $email,
                        'password'      => Hash::make($password),
                        'role'          => 'student',
                        'phone'         => $this->get($ra, $colPhone)  ?: null,
                        'date_of_birth' => $this->get($ra, $colDob)    ?: null,
                        'address'       => $this->get($ra, $colAddr)   ?: null,
                        'gender'        => $this->get($ra, $colGender) ?: null,
                        'nationality'   => $this->get($ra, $colNation) ?: null,
                    ]);

                    // ── Program (resolve FIRST — needed for ID generation) ──
                    $progVal   = $this->get($ra, $colProg);
                    $programId = $progVal ? $this->tolerantResolveProgramId($progVal) : null;
                    if (! $programId) $programId = $this->defaultProgramId;
                    if (! $programId) $programId = Program::orderBy('sequence')->value('id');
                    if (! $programId) throw new \Exception("Row {$rowNumber}: No program could be determined.");

                    // ── Branch: use row value → admin's branch → first branch ──
                    $branchVal = $this->get($ra, $colBranch);
                    $branchId  = null;
                    if ($branchVal) {
                        $branchId = is_numeric($branchVal)
                            ? ChurchBranch::find((int)$branchVal)?->id
                            : ChurchBranch::whereRaw('LOWER(name) = ?', [strtolower($branchVal)])->value('id');
                        if (! $branchId) {
                            $this->errors[] = "Row {$rowNumber}: Branch '{$branchVal}' not found — used admin's branch";
                        }
                    }
                    // Fall back to the importing admin's branch (most important fallback)
                    if (! $branchId) $branchId = $this->defaultBranchId;
                    // Last resort: first branch in system
                    if (! $branchId) $branchId = ChurchBranch::first()?->id;
                    if (! $branchId) throw new \Exception("Row {$rowNumber}: No branch exists. Create one first.");

                    // ── Student ID (now that program + branch are known) ──
                    $studentId = $this->get($ra, $colId);
                    if (! $studentId) {
                        $studentId = $this->generateSystemStudentId($programId, $branchId);
                    } elseif (Student::where('student_id', $studentId)->exists()) {
                        $orig      = $studentId;
                        $studentId = $this->generateNextStudentId($studentId);
                        $this->errors[] = "Row {$rowNumber}: ID '{$orig}' already exists — assigned '{$studentId}'";
                    }

                    // Photos
                    $photoPath = $bgPath = null;
                    if ($this->extractedPath) {
                        foreach ([
                            [$this->get($ra, $colPhoto), 'students/photos',       'photoPath'],
                            [$this->get($ra, $colBg),    'students/backgrounds',  'bgPath'],
                        ] as [$fn, $dir, $var]) {
                            if (! $fn) continue;
                            foreach ([$this->extractedPath.'/'.$fn, $this->extractedPath.'/photos/'.$fn] as $fp) {
                                if (file_exists($fp)) {
                                    try { $$var = \App\Http\Controllers\Admin\StudentController::storePhotoFile(new \Illuminate\Http\File($fp), $dir); }
                                    catch (\Throwable $e) {}
                                    break;
                                }
                            }
                        }
                    }

                    // Create student
                    Student::create([
                        'user_id'          => $user->id,
                        'student_id'       => $studentId,
                        'admission_date'   => $this->get($ra, $colDate) ?: now()->toDateString(),
                        'program_id'       => $programId,
                        'church_branch_id' => $branchId,
                        'status'           => 'active',
                        'is_approved'      => false,
                        'enrollment_year'  => $this->get($ra, $colYear) ?: date('Y'),
                        'study_mode'       => $this->get($ra, $colMode) ?: 'full_time',
                        'qualifications'   => $this->get($ra, $colQuals) ?: null,
                        'native_town'      => $this->get($ra, $colTown)  ?: null,
                        'profession'       => $this->get($ra, $colProf)  ?: null,
                        'photo'            => $photoPath,
                        'background_photo' => $bgPath,
                        'password'         => Hash::make($this->get($ra, $colPortal) ?: $password),
                    ]);

                    $this->credentials[] = [
                        'full_name'  => $fullName,
                        'email'      => $email,
                        'student_id' => $studentId,
                        'password'   => $password,
                    ];

                    DB::commit();
                    $this->successCount++;

                } catch (\Throwable $e) {
                    DB::rollBack();
                    throw $e;
                }
            } catch (\Throwable $e) {
                $this->failureCount++;
                $this->errors[] = $e->getMessage();
                Log::error('Import row ' . $rowNumber . ': ' . $e->getMessage());
            }

            $rowNumber++;
        }

        Log::info("Import done: {$this->successCount} ok, {$this->failureCount} failed");
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    protected function tolerantResolveProgramId(?string $val)
    {
        if (! $val) return null;
        $low = strtolower(trim($val));
        if (is_numeric($val)) {
            $p = Program::find((int)$val);
            if ($p) return $p->id;
        }
        $p = Program::whereRaw('LOWER(TRIM(name)) = ?', [$low])->first();
        if ($p) return $p->id;
        $p = Program::whereRaw('LOWER(name) LIKE ?', ["%{$low}%"])->first();
        if ($p) return $p->id;
        return null;
    }

    // ── Generate a proper system-format student ID ───────────────────────
    // Delegates to StudentController::generateNextSystemId() so the
    // format is always identical whether adding manually or via bulk import.
    protected function generateSystemStudentId(?int $programId = null, ?int $branchId = null): string
    {
        try {
            return \App\Http\Controllers\Admin\StudentController::generateNextSystemId();
        } catch (\Throwable $e) {
            // Absolute fallback
            do { $id = 'STU-' . strtoupper(Str::random(6)); }
            while (Student::where('student_id', $id)->exists());
            return $id;
        }
    }

    protected function generateNextStudentId(string $studentId): string
    {
        if (preg_match('/^(.*?)(\d+)$/', $studentId, $m)) {
            $prefix = $m[1];
            $width  = strlen($m[2]);
            $max    = 0;
            foreach (Student::where('student_id', 'like', $prefix . '%')->pluck('student_id') as $cand) {
                if (preg_match('/^' . preg_quote($prefix, '/') . '(\d+)$/', $cand, $mm)) {
                    $max = max($max, (int)$mm[1]);
                }
            }
            return $prefix . str_pad((string)($max + 1), $width, '0', STR_PAD_LEFT);
        }
        $base = $studentId; $c = 1;
        while (Student::where('student_id', $studentId)->exists()) {
            $studentId = $base . '-' . $c++;
        }
        return $studentId;
    }

    protected function writeCredentialsCsvIfNeeded(): void
    {
        if (empty($this->credentials)) return;
        try {
            $csv  = '"Full Name","Email","Student ID","Password"' . "\n";
            foreach ($this->credentials as $r) {
                $csv .= '"' . str_replace('"','""',$r['full_name'])  . '",'
                      . '"' . str_replace('"','""',$r['email'])      . '",'
                      . '"' . str_replace('"','""',$r['student_id']) . '",'
                      . '"' . str_replace('"','""',$r['password'])   . '"' . "\n";
            }
            $path = 'imports/imported_credentials_' . date('Ymd_His') . '.csv';
            Storage::disk('public')->put($path, $csv);
            $this->credentialsPublicPath = 'storage/' . $path;
        } catch (\Throwable $e) {
            Log::warning('Credentials CSV write failed: ' . $e->getMessage());
        }
    }

    public function getSuccessCount(): int { return $this->successCount; }
    public function getFailureCount(): int { return $this->failureCount; }
    public function getErrors(): array     { return $this->errors; }

    public function getCredentialsPublicPath(): ?string
    {
        if ($this->credentialsPublicPath === null) {
            $this->writeCredentialsCsvIfNeeded();
        }
        return $this->credentialsPublicPath;
    }
}
