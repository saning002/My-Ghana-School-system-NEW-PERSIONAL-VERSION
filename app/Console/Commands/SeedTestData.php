<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedTestData extends Command
{
    protected $signature   = 'seed:test-data {slug? : Tenant slug (default: saning-academy)}';
    protected $description  = 'Seed 5 students + 2 teachers with full records (exams, SBA, fees, attendance) into a tenant Neon DB';

    private string $conn = 'tenant_seed';

    public function handle(): int
    {
        $slug = $this->argument('slug') ?? 'saning-academy';

        // 1. Resolve tenant from central DB
        $tenant = Tenant::on('pgsql')->where('slug', $slug)->first();
        if (!$tenant) {
            $this->error("Tenant not found: {$slug}");
            return 1;
        }

        $this->info("Tenant: {$tenant->name} ({$tenant->slug})");
        $this->info("DB Host: {$tenant->db_host}");

        // 2. Configure tenant connection
        $dbConfig = $tenant->dbConfig();
        Config::set("database.connections.{$this->conn}", $dbConfig);
        DB::purge($this->conn);
        DB::setDefaultConnection($this->conn);

        try {
            DB::connection($this->conn)->getPdo();
            $this->info('Connected to tenant DB successfully.');
        } catch (\Throwable $e) {
            $this->error('Cannot connect to tenant DB: ' . $e->getMessage());
            return 1;
        }

        $now = now();

        // ── 0a. Ensure missing TABLES exist ──────────────────────────────────
        $ensureTables = [
            "CREATE TABLE IF NOT EXISTS \"term_fee_bills\" (\"id\" BIGSERIAL PRIMARY KEY, \"student_id\" BIGINT NOT NULL, \"academic_session_id\" BIGINT NOT NULL, \"program_fee\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"exam_fee\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"other_fees\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"other_fees_label\" VARCHAR(255) NULL, \"total_billed\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"notes\" TEXT NULL, \"created_at\" TIMESTAMP NULL, \"updated_at\" TIMESTAMP NULL)",
            "CREATE TABLE IF NOT EXISTS \"student_fees\" (\"id\" BIGSERIAL PRIMARY KEY, \"student_id\" BIGINT NOT NULL, \"amount\" DECIMAL(10,2) NULL, \"exam_fee\" DECIMAL(10,2) NULL, \"notes\" TEXT NULL, \"created_at\" TIMESTAMP NULL, \"updated_at\" TIMESTAMP NULL)",
            "CREATE TABLE IF NOT EXISTS \"program_fees\" (\"id\" BIGSERIAL PRIMARY KEY, \"program_id\" BIGINT NOT NULL, \"church_branch_id\" BIGINT NULL, \"amount\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"exam_fee\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"created_at\" TIMESTAMP NULL, \"updated_at\" TIMESTAMP NULL)",
            "CREATE TABLE IF NOT EXISTS \"fee_reminders\" (\"id\" BIGSERIAL PRIMARY KEY, \"student_id\" BIGINT NOT NULL, \"academic_session_id\" BIGINT NULL, \"channel\" VARCHAR(50) NOT NULL, \"recipient\" VARCHAR(255) NOT NULL, \"recipient_name\" VARCHAR(255) NULL, \"message\" TEXT NOT NULL, \"amount_owed\" DECIMAL(10,2) NOT NULL DEFAULT 0, \"status\" VARCHAR(50) NOT NULL DEFAULT 'pending', \"error_message\" TEXT NULL, \"sent_by\" VARCHAR(255) NULL, \"sent_at\" TIMESTAMP NULL, \"created_at\" TIMESTAMP NULL, \"updated_at\" TIMESTAMP NULL)",
        ];

        foreach ($ensureTables as $stmt) {
            try {
                DB::connection($this->conn)->statement($stmt);
            } catch (\Throwable $e) {
                // Ignore — table might already exist
            }
        }
        $this->info('Ensured all required tables exist.');

        // ── 0b. Ensure missing COLUMNS exist ────────────────────────────────
        // The tenant DB is created by TenantSetupController which may not have
        // all columns from later migrations. Add them safely here.
        $ensureColumns = [
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='password') THEN ALTER TABLE students ADD COLUMN \"password\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='is_approved') THEN ALTER TABLE students ADD COLUMN \"is_approved\" BOOLEAN NOT NULL DEFAULT FALSE; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='results_blocked') THEN ALTER TABLE students ADD COLUMN \"results_blocked\" BOOLEAN NOT NULL DEFAULT FALSE; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='students' AND column_name='admission_date') THEN ALTER TABLE students ADD COLUMN \"admission_date\" DATE NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='enrollments' AND column_name='program_id') THEN ALTER TABLE enrollments ADD COLUMN \"program_id\" BIGINT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='enrollments' AND column_name='year') THEN ALTER TABLE enrollments ADD COLUMN \"year\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='program_id') THEN ALTER TABLE exam_scores ADD COLUMN \"program_id\" BIGINT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='quiz_score') THEN ALTER TABLE exam_scores ADD COLUMN \"quiz_score\" DECIMAL(5,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='attempt') THEN ALTER TABLE exam_scores ADD COLUMN \"attempt\" SMALLINT NOT NULL DEFAULT 1; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='test1_score') THEN ALTER TABLE exam_scores ADD COLUMN \"test1_score\" DECIMAL(5,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='groupwork_score') THEN ALTER TABLE exam_scores ADD COLUMN \"groupwork_score\" DECIMAL(5,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='test2_score') THEN ALTER TABLE exam_scores ADD COLUMN \"test2_score\" DECIMAL(5,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='project_score') THEN ALTER TABLE exam_scores ADD COLUMN \"project_score\" DECIMAL(5,2) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='exam_scores' AND column_name='academic_period_id') THEN ALTER TABLE exam_scores ADD COLUMN \"academic_period_id\" BIGINT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='attendances' AND column_name='program_id') THEN ALTER TABLE attendances ADD COLUMN \"program_id\" BIGINT NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='course_assignments' AND column_name='year') THEN ALTER TABLE course_assignments ADD COLUMN \"year\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='academic_sessions' AND column_name='mode') THEN ALTER TABLE academic_sessions ADD COLUMN \"mode\" VARCHAR(255) NULL DEFAULT 'term'; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='academic_periods' AND column_name='name') THEN ALTER TABLE academic_periods ADD COLUMN \"name\" VARCHAR(255) NULL; END IF; END \$\$",
            "DO \$\$ BEGIN IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='academic_periods' AND column_name='sequence') THEN ALTER TABLE academic_periods ADD COLUMN \"sequence\" SMALLINT NOT NULL DEFAULT 1; END IF; END \$\$",
        ];

        foreach ($ensureColumns as $stmt) {
            try {
                DB::connection($this->conn)->statement($stmt);
            } catch (\Throwable $e) {
                // Ignore — column might already exist or table structure differs
            }
        }
        $this->info('Ensured all required columns exist.');

        // ── 1. Church Branch ────────────────────────────────────────────────
        $branch = DB::table('church_branches')->first();
        if (!$branch) {
            DB::table('church_branches')->insert([
                'name'       => 'Main Campus',
                'code'       => 'MC',
                'location'   => 'Kasoa, Ghana',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $branchId = DB::getPdo()->lastInsertId();
            $this->info("Created church branch: Main Campus");
        } else {
            $branchId = $branch->id;
            $this->info("Using existing church branch: {$branch->name}");
        }

        // ── 2. Programs ──────────────────────────────────────────────────────
        // TenantSetupController schema: name, code, description, sequence, church_branch_id, duration_years
        $programData = [
            ['name' => 'Basic School (JHS)',   'code' => 'JHS', 'sequence' => 1, 'duration_years' => 3],
            ['name' => 'Senior High School',    'code' => 'SHS', 'sequence' => 2, 'duration_years' => 3],
        ];

        foreach ($programData as $pd) {
            $exists = DB::table('programs')->where('code', $pd['code'])->first();
            if (!$exists) {
                DB::table('programs')->insert([
                    'name'             => $pd['name'],
                    'code'             => $pd['code'],
                    'sequence'         => $pd['sequence'],
                    'church_branch_id' => $branchId,
                    'duration_years'   => $pd['duration_years'],
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            }
        }

        $progJhs = DB::table('programs')->where('code', 'JHS')->first();
        $this->info("Programs: JHS ({$progJhs->id})");

        // ── 3. Courses ───────────────────────────────────────────────────────
        // TenantSetupController schema: name, code, description, program_id
        $courseDefs = [
            ['name' => 'English Language',     'code' => 'ENG101'],
            ['name' => 'Mathematics',           'code' => 'MATH101'],
            ['name' => 'Integrated Science',    'code' => 'SCI101'],
            ['name' => 'Social Studies',        'code' => 'SOC101'],
        ];

        foreach ($courseDefs as $cd) {
            $exists = DB::table('courses')->where('code', $cd['code'])->first();
            if (!$exists) {
                DB::table('courses')->insert([
                    'name'       => $cd['name'],
                    'code'       => $cd['code'],
                    'program_id' => $progJhs->id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
        $courses = DB::table('courses')->where('program_id', $progJhs->id)->get();
        $this->info("Courses: {$courses->count()} for JHS");

        // ── 4. Academic Session + Periods ────────────────────────────────────
        // TenantSetupController schema for sessions: year, mode (we add it)
        // TenantSetupController schema for periods: academic_session_id, month, is_active, name, sequence (we add)
        $session = DB::table('academic_sessions')->where('year', '2025/2026')->first();
        if (!$session) {
            DB::table('academic_sessions')->insert([
                'year'       => '2025/2026',
                'mode'       => 'term',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            $session = DB::table('academic_sessions')->where('year', '2025/2026')->first();
            $this->info("Created academic session: 2025/2026 (term mode)");
        }

        $terms = [
            ['month' => 'September', 'name' => 'Term 1', 'sequence' => 1, 'active' => true],
            ['month' => 'January',   'name' => 'Term 2', 'sequence' => 2, 'active' => false],
            ['month' => 'April',     'name' => 'Term 3', 'sequence' => 3, 'active' => false],
        ];
        foreach ($terms as $t) {
            $period = DB::table('academic_periods')
                ->where('academic_session_id', $session->id)
                ->where('month', $t['month'])
                ->first();
            if (!$period) {
                DB::table('academic_periods')->insert([
                    'academic_session_id' => $session->id,
                    'month'               => $t['month'],
                    'name'                => $t['name'],
                    'is_active'           => $t['active'],
                    'sequence'            => $t['sequence'],
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
        $period1 = DB::table('academic_periods')
            ->where('academic_session_id', $session->id)
            ->where('month', 'September')
            ->first();
        $this->info("Academic period: Term 1 / September ({$period1->id})");

        // ── 5. Teachers (Users with role=lecturer) ──────────────────────────
        // TenantSetupController users schema: full_name, email, phone, date_of_birth, address,
        //   gender, nationality, role, is_super_admin, church_branch_id, qualification, bio, password
        $teachers = [
            [
                'full_name'    => 'Kwame Asante',
                'email'        => 'kwame.asante@saningacademy.test',
                'phone'        => '+233241111111',
                'qualification' => 'B.Ed Mathematics, University of Cape Coast',
                'bio'          => 'Experienced mathematics teacher with 10 years in JHS education.',
                'password'     => 'Teacher@2025',
            ],
            [
                'full_name'    => 'Ama Owusu',
                'email'        => 'ama.owusu@saningacademy.test',
                'phone'        => '+233242222222',
                'qualification' => 'B.Ed English, University of Ghana',
                'bio'          => 'English and Social Studies specialist with 8 years teaching experience.',
                'password'     => 'Teacher@2025',
            ],
        ];

        $teacherIds = [];
        foreach ($teachers as $td) {
            $existing = DB::table('users')->where('email', $td['email'])->first();
            if ($existing) {
                $teacherIds[] = $existing->id;
                $this->info("Teacher already exists: {$td['full_name']}");
                continue;
            }
            DB::table('users')->insert([
                'full_name'        => $td['full_name'],
                'email'             => $td['email'],
                'phone'             => $td['phone'],
                'role'              => 'lecturer',
                'is_super_admin'    => false,
                'church_branch_id'  => $branchId,
                'qualification'     => $td['qualification'],
                'bio'               => $td['bio'],
                'password'          => Hash::make($td['password']),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $teacherIds[] = DB::getPdo()->lastInsertId();
            $this->info("Created teacher: {$td['full_name']} ({$td['email']})");
        }

        // Assign teachers to courses
        // TenantSetupController course_assignments: lecturer_id, course_id, program_id, sba_percentage, exam_percentage, year (we add)
        $assignments = [
            ['teacher_idx' => 0, 'course_code' => 'MATH101'],
            ['teacher_idx' => 0, 'course_code' => 'SCI101'],
            ['teacher_idx' => 1, 'course_code' => 'ENG101'],
            ['teacher_idx' => 1, 'course_code' => 'SOC101'],
        ];

        foreach ($assignments as $a) {
            $course = DB::table('courses')->where('code', $a['course_code'])->first();
            $exists = DB::table('course_assignments')
                ->where('course_id', $course->id)
                ->where('lecturer_id', $teacherIds[$a['teacher_idx']])
                ->first();
            if (!$exists) {
                DB::table('course_assignments')->insert([
                    'course_id'         => $course->id,
                    'lecturer_id'       => $teacherIds[$a['teacher_idx']],
                    'program_id'        => $progJhs->id,
                    'sba_percentage'    => 60,
                    'exam_percentage'   => 40,
                    'year'              => '2025/2026',
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]);
            }
        }
        $this->info('Course assignments created for 2 teachers.');

        // ── 6. Students ─────────────────────────────────────────────────────
        // TenantSetupController students schema: user_id, student_id, program_id, church_branch_id,
        //   status, year_of_admission, portal_password, gender, date_of_birth, phone, address,
        //   photo, nationality, religion, guardian_name, guardian_phone, guardian_email
        //   + password, is_approved, results_blocked, admission_date (we ensure exist)
        $studentDefs = [
            [
                'full_name'    => 'Kofi Mensah',
                'student_id'    => 'SAN/JHS/001',
                'email'         => 'kofi.mensah@student.test',
                'phone'         => '+233243333301',
                'gender'        => 'male',
                'date_of_birth' => '2010-05-15',
                'address'       => 'House 12, Kasoa, Central Region',
                'guardian_name' => 'Samuel Mensah',
                'guardian_phone'=> '+233243333311',
                'password'      => 'Student@2025',
            ],
            [
                'full_name'    => 'Abena Boateng',
                'student_id'    => 'SAN/JHS/002',
                'email'         => 'abena.boateng@student.test',
                'phone'         => '+233243333302',
                'gender'        => 'female',
                'date_of_birth' => '2011-03-22',
                'address'       => 'House 45, Winneba Road, Kasoa',
                'guardian_name' => 'Grace Boateng',
                'guardian_phone'=> '+233243333322',
                'password'      => 'Student@2025',
            ],
            [
                'full_name'    => 'Yaw Sarpong',
                'student_id'    => 'SAN/JHS/003',
                'email'         => 'yaw.sarpong@student.test',
                'phone'         => '+233243333303',
                'gender'        => 'male',
                'date_of_birth' => '2010-09-10',
                'address'       => 'House 7, Nyanyano, Kasoa',
                'guardian_name' => 'John Sarpong',
                'guardian_phone'=> '+233243333333',
                'password'      => 'Student@2025',
            ],
            [
                'full_name'    => 'Akosua Frimpong',
                'student_id'    => 'SAN/JHS/004',
                'email'         => 'akosua.frimpong@student.test',
                'phone'         => '+233243333304',
                'gender'        => 'female',
                'date_of_birth' => '2011-01-30',
                'address'       => 'House 23, Budumburam, Kasoa',
                'guardian_name' => 'Mary Frimpong',
                'guardian_phone'=> '+233243333344',
                'password'      => 'Student@2025',
            ],
            [
                'full_name'    => 'Kwabena Adjei',
                'student_id'    => 'SAN/JHS/005',
                'email'         => 'kwabena.adjei@student.test',
                'phone'         => '+233243333305',
                'gender'        => 'male',
                'date_of_birth' => '2010-07-18',
                'address'       => 'House 33, Millennium City, Kasoa',
                'guardian_name' => 'David Adjei',
                'guardian_phone'=> '+233243333355',
                'password'      => 'Student@2025',
            ],
        ];

        $studentRecords = [];

        foreach ($studentDefs as $sd) {
            // Check if student already exists by student_id
            $existingStudent = DB::table('students')->where('student_id', $sd['student_id'])->first();
            if ($existingStudent) {
                $user = DB::table('users')->where('id', $existingStudent->user_id)->first();
                $studentRecords[] = [
                    'student_id'  => $sd['student_id'],
                    'full_name'   => $user->full_name,
                    'password'    => $sd['password'],
                    'db_id'       => $existingStudent->id,
                    'user_id'     => $existingStudent->user_id,
                    'program_id'  => $existingStudent->program_id ?? $progJhs->id,
                ];
                $this->info("Student already exists: {$sd['student_id']} — skipping");
                continue;
            }

            // Create user
            DB::table('users')->insert([
                'full_name'        => $sd['full_name'],
                'email'             => $sd['email'],
                'phone'             => $sd['phone'],
                'date_of_birth'     => $sd['date_of_birth'],
                'address'           => $sd['address'],
                'gender'            => $sd['gender'],
                'nationality'       => 'Ghanaian',
                'role'              => 'student',
                'is_super_admin'    => false,
                'church_branch_id'  => $branchId,
                'password'          => Hash::make($sd['password']),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $userId = DB::getPdo()->lastInsertId();

            // Create student record
            DB::table('students')->insert([
                'user_id'          => $userId,
                'student_id'       => $sd['student_id'],
                'program_id'       => $progJhs->id,
                'church_branch_id' => $branchId,
                'status'           => 'active',
                'is_approved'       => true,
                'year_of_admission' => 2025,
                'admission_date'   => '2025-09-01',
                'gender'           => $sd['gender'],
                'date_of_birth'    => $sd['date_of_birth'],
                'phone'            => $sd['phone'],
                'address'          => $sd['address'],
                'nationality'      => 'Ghanaian',
                'guardian_name'    => $sd['guardian_name'],
                'guardian_phone'   => $sd['guardian_phone'],
                'password'         => Hash::make($sd['password']),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);
            $studentDbId = DB::getPdo()->lastInsertId();

            $studentRecords[] = [
                'student_id'  => $sd['student_id'],
                'full_name'   => $sd['full_name'],
                'password'    => $sd['password'],
                'db_id'       => $studentDbId,
                'user_id'     => $userId,
                'program_id'  => $progJhs->id,
            ];
            $this->info("Created student: {$sd['student_id']} — {$sd['full_name']}");
        }

        // ── 7. Enrollments ─────────────────────────────────────────────────
        foreach ($studentRecords as $sr) {
            foreach ($courses as $course) {
                $exists = DB::table('enrollments')
                    ->where('student_id', $sr['db_id'])
                    ->where('course_id', $course->id)
                    ->first();
                if (!$exists) {
                    DB::table('enrollments')->insert([
                        'student_id'  => $sr['db_id'],
                        'program_id'  => $sr['program_id'],
                        'course_id'   => $course->id,
                        'year'        => '2025/2026',
                        'created_at'  => $now,
                        'updated_at'  => $now,
                    ]);
                }
            }
        }
        $this->info("Enrolled 5 students in {$courses->count()} courses each.");

        // ── 8. Exam Scores + SBA ────────────────────────────────────────────
        // Available columns: student_id, course_id, class_score, exam_score, academic_session_id,
        //   sba_score, total_score, grade, remarks
        //   + program_id, quiz_score, attempt, test1_score, groupwork_score, test2_score, project_score,
        //     academic_period_id (we ensure exist)
        //
        // SBA = 60% of total, Exam = 40% (Ghana GES standard)
        // SBA subscores: test1 (15), groupwork (15), test2 (15), project (15) = 60
        // Exam score: out of 40
        // Final = SBA + Exam out of 100

        $examData = [
            0 => [ // Kofi - good student
                'ENG101' => [14, 13, 14, 13, 35],
                'MATH101'=> [15, 14, 14, 14, 36],
                'SCI101' => [13, 12, 13, 12, 33],
                'SOC101' => [14, 14, 13, 14, 34],
            ],
            1 => [ // Abena - excellent
                'ENG101' => [15, 15, 14, 14, 38],
                'MATH101'=> [14, 13, 15, 14, 37],
                'SCI101' => [15, 14, 14, 13, 35],
                'SOC101' => [15, 15, 15, 14, 39],
            ],
            2 => [ // Yaw - average
                'ENG101' => [10, 11, 9, 10, 25],
                'MATH101'=> [11, 10, 9, 11, 28],
                'SCI101' => [9, 10, 10, 9, 22],
                'SOC101' => [12, 11, 10, 10, 27],
            ],
            3 => [ // Akosua - above average
                'ENG101' => [13, 12, 12, 13, 30],
                'MATH101'=> [12, 13, 11, 12, 32],
                'SCI101' => [13, 12, 13, 12, 31],
                'SOC101' => [14, 13, 12, 13, 33],
            ],
            4 => [ // Kwabena - below average (owing fees, results blocked)
                'ENG101' => [8, 7, 9, 8, 18],
                'MATH101'=> [7, 8, 7, 8, 15],
                'SCI101' => [8, 7, 8, 7, 16],
                'SOC101' => [9, 8, 8, 7, 19],
            ],
        ];

        foreach ($examData as $sIdx => $courseScores) {
            if (!isset($studentRecords[$sIdx])) continue;
            $sr = $studentRecords[$sIdx];

            foreach ($courseScores as $courseCode => $scores) {
                $course = DB::table('courses')->where('code', $courseCode)->first();
                if (!$course) continue;

                $test1     = $scores[0];
                $groupwork = $scores[1];
                $test2     = $scores[2];
                $project   = $scores[3];
                $exam      = $scores[4];
                $sba       = $test1 + $groupwork + $test2 + $project; // out of 60
                $total     = $sba + $exam; // out of 100
                $quiz      = round($sba / 60 * 100, 2); // quiz_score out of 100
                $grade     = $this->computeGrade($total);

                $exists = DB::table('exam_scores')
                    ->where('student_id', $sr['db_id'])
                    ->where('course_id', $course->id)
                    ->first();
                if (!$exists) {
                    DB::table('exam_scores')->insert([
                        'student_id'          => $sr['db_id'],
                        'course_id'           => $course->id,
                        'program_id'          => $sr['program_id'],
                        'academic_session_id' => $session->id,
                        'academic_period_id'  => $period1->id,
                        'attempt'             => 1,
                        'quiz_score'          => $quiz,
                        'sba_score'           => $sba,
                        'class_score'         => $quiz, // same as quiz_score (legacy column)
                        'test1_score'         => $test1,
                        'groupwork_score'     => $groupwork,
                        'test2_score'         => $test2,
                        'project_score'       => $project,
                        'exam_score'          => $exam,
                        'total_score'         => $total,
                        'grade'               => $grade,
                        'remarks'             => $this->computeRemark($total),
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            }
        }
        $this->info('Exam scores + SBA subscores created for all 5 students (4 courses each).');

        // ── 9. Attendance ──────────────────────────────────────────────────
        // Available: student_id, academic_period_id, course_id, date, status, present_days, absent_days
        //   + program_id (we ensure)
        $attendanceDays = 10;
        $startDate = now()->subDays($attendanceDays);

        foreach ($studentRecords as $sr) {
            foreach ($courses as $course) {
                for ($d = 0; $d < $attendanceDays; $d++) {
                    $date = $startDate->copy()->addDays($d)->toDateString();

                    $exists = DB::table('attendances')
                        ->where('student_id', $sr['db_id'])
                        ->where('course_id', $course->id)
                        ->where('date', $date)
                        ->first();
                    if ($exists) continue;

                    $status = (rand(1, 10) > 2) ? 'present' : 'absent';

                    DB::table('attendances')->insert([
                        'student_id'         => $sr['db_id'],
                        'program_id'          => $sr['program_id'],
                        'course_id'           => $course->id,
                        'academic_period_id'  => $period1->id,
                        'date'                => $date,
                        'status'              => $status,
                        'present_days'        => $status === 'present' ? 1 : 0,
                        'absent_days'         => $status === 'absent' ? 1 : 0,
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }
            }
        }
        $this->info("Attendance created: {$attendanceDays} days × 4 courses × 5 students = " . ($attendanceDays * 4 * 5) . ' records.');

        // ── 10. Program Fees ────────────────────────────────────────────────
        $programFee = DB::table('program_fees')->where('program_id', $progJhs->id)->first();
        if (!$programFee) {
            DB::table('program_fees')->insert([
                'program_id'        => $progJhs->id,
                'church_branch_id'  => $branchId,
                'amount'            => 500.00,
                'exam_fee'          => 50.00,
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);
            $this->info('Program fee: GHS 500.00/term + GHS 50.00 exam fee for JHS');
        }

        // ── 11. Student Fees ────────────────────────────────────────────────
        foreach ($studentRecords as $sr) {
            $exists = DB::table('student_fees')->where('student_id', $sr['db_id'])->first();
            if (!$exists) {
                DB::table('student_fees')->insert([
                    'student_id' => $sr['db_id'],
                    'amount'     => 300.00,
                    'exam_fee'   => 50.00,
                    'notes'      => 'Tuition fee per term',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
        $this->info('Student fees: GHS 300.00 + GHS 50.00 exam fee each.');

        // ── 12. Term Fee Bills ──────────────────────────────────────────────
        foreach ($studentRecords as $sr) {
            $exists = DB::table('term_fee_bills')
                ->where('student_id', $sr['db_id'])
                ->where('academic_session_id', $session->id)
                ->first();
            if (!$exists) {
                DB::table('term_fee_bills')->insert([
                    'student_id'           => $sr['db_id'],
                    'academic_session_id'  => $session->id,
                    'program_fee'          => 500.00,
                    'exam_fee'             => 50.00,
                    'other_fees'            => 0,
                    'other_fees_label'     => null,
                    'total_billed'          => 550.00,
                    'notes'                => 'Term 1 fees 2025/2026',
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
        }
        $this->info('Term fee bills: GHS 550.00 per student for 2025/2026.');

        // ── 13. Payments ────────────────────────────────────────────────────
        // Student 1 (Kofi): paid in full
        // Student 2 (Abena): paid in full
        // Student 3 (Yaw): partial payment (GHS 300 of GHS 550)
        // Student 4 (Akosua): paid in full
        // Student 5 (Kwabena): no payment — owes full amount, results blocked
        $paymentPlans = [
            0 => [550.00],
            1 => [550.00],
            2 => [300.00],
            3 => [550.00],
            4 => [],
        ];

        foreach ($studentRecords as $idx => $sr) {
            if (empty($paymentPlans[$idx])) continue;

            foreach ($paymentPlans[$idx] as $amount) {
                $receipt = 'RCP-' . strtoupper(Str::random(8));
                DB::table('payments')->insert([
                    'receipt_number'       => $receipt,
                    'student_id'            => $sr['db_id'],
                    'amount_paid'          => $amount,
                    'payment_date'         => $now->copy()->subDays(rand(1, 15))->toDateString(),
                    'payment_method'       => 'cash',
                    'academic_session_id'  => $session->id,
                    'payer_name'           => $sr['full_name'] . ' (Guardian)',
                    'payer_phone'          => '+2332433333' . str_pad($idx + 1, 2, '0', STR_PAD_LEFT),
                    'notes'                => $amount >= 550 ? 'Full term fee payment' : 'Partial payment — balance outstanding',
                    'created_at'           => $now,
                    'updated_at'           => $now,
                ]);
            }
        }
        $this->info('Payments: 3 full + 1 partial + 1 unpaid (Kwabena owes GHS 550).');

        // Block results for Kwabena (unpaid)
        if (isset($studentRecords[4])) {
            DB::table('students')->where('id', $studentRecords[4]['db_id'])->update(['results_blocked' => true]);
            $this->info('Kwabena Adjei: results_blocked = true (unpaid fees).');
        }

        // ── Summary ─────────────────────────────────────────────────────────
        $this->newLine();
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('  SEED COMPLETE — TEST CREDENTIALS');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->newLine();

        $this->comment('── TEACHERS (login via /teachers-portal/login) ──');
        $this->table(
            ['Name', 'Email', 'Password'],
            [
                ['Kwame Asante', 'kwame.asante@saningacademy.test', 'Teacher@2025'],
                ['Ama Owusu',    'ama.owusu@saningacademy.test',    'Teacher@2025'],
            ]
        );

        $this->newLine();
        $this->comment('── STUDENTS (login via student portal) ──');
        $this->table(
            ['Student ID', 'Full Name', 'Password', 'Fees Status'],
            [
                ['SAN/JHS/001', 'Kofi Mensah',      'Student@2025', 'PAID (GHS 550)'],
                ['SAN/JHS/002', 'Abena Boateng',    'Student@2025', 'PAID (GHS 550)'],
                ['SAN/JHS/003', 'Yaw Sarpong',      'Student@2025', 'PARTIAL (GHS 300/550, owes GHS 250)'],
                ['SAN/JHS/004', 'Akosua Frimpong',  'Student@2025', 'PAID (GHS 550)'],
                ['SAN/JHS/005', 'Kwabena Adjei',    'Student@2025', 'UNPAID (owes GHS 550, results blocked)'],
            ]
        );

        $this->newLine();
        $this->comment('── DATA SUMMARY ──');
        $this->table(
            ['Record Type', 'Count'],
            [
                ['Teachers',           '2'],
                ['Students',           '5'],
                ['Courses (JHS)',      '4'],
                ['Enrollments',        '20 (5 students × 4 courses)'],
                ['Exam Scores',        '20 (with SBA subscores + exam + grade)'],
                ['Attendance Records', '200 (10 days × 4 courses × 5 students)'],
                ['Program Fees',       'GHS 500/term + GHS 50 exam fee'],
                ['Student Fees',       'GHS 300 + GHS 50 exam fee each'],
                ['Term Fee Bills',     '5 (GHS 550 each)'],
                ['Payments',           '4 (3 full + 1 partial)'],
            ]
        );

        return 0;
    }

    private function computeGrade(float $total): string
    {
        return match(true) {
            $total >= 80 => 'A',
            $total >= 70 => 'B',
            $total >= 60 => 'C',
            $total >= 50 => 'D',
            $total >= 40 => 'E',
            default       => 'F',
        };
    }

    private function computeRemark(float $total): string
    {
        return match(true) {
            $total >= 80 => 'Excellent',
            $total >= 70 => 'Very Good',
            $total >= 60 => 'Good',
            $total >= 50 => 'Credit',
            $total >= 40 => 'Pass',
            default       => 'Fail',
        };
    }
}
