<?php

namespace Database\Seeders;

use App\Models\ChurchBranch;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Enrollment;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * GhanaDemoSeeder
 * ----------------
 * Populates the system with realistic Ghanaian demo data for development/testing.
 * Safe to run multiple times — skips existing records by email / student_id.
 *
 * Creates:
 *   - 3 branches (Accra, Kumasi, Tamale)
 *   - 5 programs with Ghanaian-style course names
 *   - 3 lecturers
 *   - 20 students with Ghanaian names
 *   - Course enrollments for every student
 *
 * DO NOT run in production — use InitialAdminSeeder instead.
 */
class GhanaDemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Seeding Ghana demo data...');

        $branches  = $this->seedBranches();
        $programs  = $this->seedPrograms();
        $this->seedCourses($programs);
        $lecturers = $this->seedLecturers($branches);
        $students  = $this->seedStudents($branches, $programs);
        $this->seedEnrollments($students, $programs);
        $this->seedCourseAssignments($lecturers, $programs);

        $this->command->info('Ghana demo data seeded successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // BRANCHES
    // ──────────────────────────────────────────────────────────────────────────
    private function seedBranches(): \Illuminate\Support\Collection
    {
        $data = [
            ['name' => 'Accra Central',   'code' => 'ACC', 'location' => 'Accra, Greater Accra'],
            ['name' => 'Kumasi Ashanti',   'code' => 'KUM', 'location' => 'Kumasi, Ashanti Region'],
            ['name' => 'Tamale Northern',  'code' => 'TAM', 'location' => 'Tamale, Northern Region'],
        ];

        foreach ($data as $row) {
            ChurchBranch::firstOrCreate(['code' => $row['code']], $row);
        }

        return ChurchBranch::whereIn('code', array_column($data, 'code'))->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PROGRAMS
    // ──────────────────────────────────────────────────────────────────────────
    private function seedPrograms(): \Illuminate\Support\Collection
    {
        $data = [
            ['name' => 'Pre-College Foundation', 'duration' => 6,  'sequence' => 1],
            ['name' => 'First Semester',          'duration' => 12, 'sequence' => 2],
            ['name' => 'Second Semester',         'duration' => 12, 'sequence' => 3],
            ['name' => 'Third Semester',          'duration' => 12, 'sequence' => 4],
            ['name' => 'Third Semester Practical','duration' => 6,  'sequence' => 5],
        ];

        foreach ($data as $row) {
            Program::firstOrCreate(['name' => $row['name']], $row);
        }

        return Program::whereIn('name', array_column($data, 'name'))->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // COURSES (Ghanaian ministerial subject names)
    // ──────────────────────────────────────────────────────────────────────────
    private function seedCourses(\Illuminate\Support\Collection $programs): void
    {
        $coursesByProgram = [
            'Pre-College Foundation' => [
                ['name' => 'Introduction to Biblical Studies',       'code' => 'IBS101'],
                ['name' => 'Foundations of Christian Ministry',      'code' => 'FCM101'],
                ['name' => 'Communication Skills in Ministry',       'code' => 'CSM101'],
                ['name' => 'Basic Theology',                         'code' => 'BT101'],
            ],
            'First Semester' => [
                ['name' => 'Old Testament Survey',                   'code' => 'OTS201'],
                ['name' => 'New Testament Survey',                   'code' => 'NTS201'],
                ['name' => 'Homiletics and Preaching',               'code' => 'HP201'],
                ['name' => 'Christian Ethics and Leadership',        'code' => 'CEL201'],
                ['name' => 'Church History in Africa',               'code' => 'CHA201'],
            ],
            'Second Semester' => [
                ['name' => 'Systematic Theology I',                  'code' => 'ST301'],
                ['name' => 'Pastoral Counselling',                   'code' => 'PC301'],
                ['name' => 'Evangelism and Mission',                 'code' => 'EM301'],
                ['name' => 'Worship and Liturgy',                    'code' => 'WL301'],
                ['name' => 'African Traditional Religions',          'code' => 'ATR301'],
            ],
            'Third Semester' => [
                ['name' => 'Systematic Theology II',                 'code' => 'ST401'],
                ['name' => 'Church Administration and Finance',      'code' => 'CAF401'],
                ['name' => 'Pentecostal and Charismatic Studies',    'code' => 'PCS401'],
                ['name' => 'Biblical Hermeneutics',                  'code' => 'BH401'],
                ['name' => 'Community Development and Ministry',     'code' => 'CDM401'],
            ],
            'Third Semester Practical' => [
                ['name' => 'Field Ministry Practicum',               'code' => 'FMP501'],
                ['name' => 'Church Planting Strategies',             'code' => 'CPS501'],
                ['name' => 'Ministry Internship Project',            'code' => 'MIP501'],
            ],
        ];

        foreach ($programs as $program) {
            $courses = $coursesByProgram[$program->name] ?? [];
            foreach ($courses as $courseData) {
                Course::firstOrCreate(
                    ['code' => $courseData['code']],
                    array_merge($courseData, ['program_id' => $program->id])
                );
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // LECTURERS (Ghanaian names)
    // ──────────────────────────────────────────────────────────────────────────
    private function seedLecturers(\Illuminate\Support\Collection $branches): \Illuminate\Support\Collection
    {
        $data = [
            ['full_name' => 'Rev. Kwabena Asante-Mensah', 'email' => 'k.asante@school.demo',  'phone' => '0244123456'],
            ['full_name' => 'Ps. Abena Owusu-Acheampong', 'email' => 'a.owusu@school.demo',   'phone' => '0244234567'],
            ['full_name' => 'Dr. Kofi Boateng-Antwi',     'email' => 'k.boateng@school.demo', 'phone' => '0244345678'],
        ];

        foreach ($data as $row) {
            if (!User::where('email', $row['email'])->exists()) {
                User::create(array_merge($row, [
                    'password' => Hash::make('password'),
                    'role'     => 'lecturer',
                ]));
            }
        }

        return User::whereIn('email', array_column($data, 'email'))->get();
    }

    // ──────────────────────────────────────────────────────────────────────────
    // STUDENTS (Ghanaian names)
    // ──────────────────────────────────────────────────────────────────────────
    private function seedStudents(\Illuminate\Support\Collection $branches, \Illuminate\Support\Collection $programs): \Illuminate\Support\Collection
    {
        $names = [
            ['full_name' => 'Akosua Amponsah',          'gender' => 'female'],
            ['full_name' => 'Kweku Mensah-Bonsu',        'gender' => 'male'],
            ['full_name' => 'Abena Frimpong',            'gender' => 'female'],
            ['full_name' => 'Kofi Darko-Asante',         'gender' => 'male'],
            ['full_name' => 'Ama Boateng',               'gender' => 'female'],
            ['full_name' => 'Kwame Amoah-Sarkodie',      'gender' => 'male'],
            ['full_name' => 'Adwoa Owusu',               'gender' => 'female'],
            ['full_name' => 'Yaw Opoku-Agyemang',        'gender' => 'male'],
            ['full_name' => 'Efua Asare-Mensah',         'gender' => 'female'],
            ['full_name' => 'Kojo Acheampong',           'gender' => 'male'],
            ['full_name' => 'Afia Osei-Bonsu',           'gender' => 'female'],
            ['full_name' => 'Nana Yaw Tetteh',           'gender' => 'male'],
            ['full_name' => 'Maame Akua Asante',         'gender' => 'female'],
            ['full_name' => 'Kwasi Bimpong-Asante',      'gender' => 'male'],
            ['full_name' => 'Araba Ankrah',              'gender' => 'female'],
            ['full_name' => 'Fiifi Quansah',             'gender' => 'male'],
            ['full_name' => 'Esi Adusei-Poku',           'gender' => 'female'],
            ['full_name' => 'Kobby Nkrumah-Barimah',     'gender' => 'male'],
            ['full_name' => 'Akua Gyamfi',               'gender' => 'female'],
            ['full_name' => 'Kwadwo Donkor-Appiah',      'gender' => 'male'],
        ];

        $createdStudents = collect();
        $prefix = rtrim(\App\Models\Setting::get('school_id_prefix', 'FOC'), '/') . '/';

        foreach ($names as $i => $nameData) {
            $email = strtolower(str_replace([' ', '-', '\''], ['.', '.', ''], $nameData['full_name'])) . '@student.demo';

            if (User::where('email', $email)->exists()) {
                $user = User::where('email', $email)->first();
                if ($user->student) {
                    $createdStudents->push($user->student);
                }
                continue;
            }

            $branch  = $branches->get($i % $branches->count());
            $program = $programs->get($i % $programs->count());

            $user = User::create([
                'full_name' => $nameData['full_name'],
                'email'     => $email,
                'password'  => Hash::make('password'),
                'gender'    => $nameData['gender'],
                'role'      => 'student',
            ]);

            do {
                $digits = str_pad(mt_rand(0, 99999999), 8, '0', STR_PAD_LEFT);
                $studentId = $prefix . $digits;
            } while (Student::where('student_id', $studentId)->exists());

            $student = Student::create([
                'user_id'          => $user->id,
                'student_id'       => $studentId,
                'admission_date'   => now()->subMonths(rand(1, 24))->toDateString(),
                'program_id'       => $program->id,
                'church_branch_id' => $branch->id,
                'status'           => 'active',
                'enrollment_year'  => now()->year,
                'study_mode'       => 'full_time',
                'signal'           => 'H+R=P',
                'position'         => 'Student',
                'date_issued'      => now()->toDateString(),
            ]);

            $createdStudents->push($student);
        }

        return $createdStudents;
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ENROLLMENTS
    // ──────────────────────────────────────────────────────────────────────────
    private function seedEnrollments(\Illuminate\Support\Collection $students, \Illuminate\Support\Collection $programs): void
    {
        $year = now()->year . '/' . (now()->year + 1);

        foreach ($students as $student) {
            $courses = Course::where('program_id', $student->program_id)->get();
            foreach ($courses as $course) {
                DB::table('enrollments')->updateOrInsert(
                    ['student_id' => $student->id, 'course_id' => $course->id],
                    ['program_id' => $student->program_id, 'year' => $year, 'created_at' => now(), 'updated_at' => now()]
                );
            }
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // COURSE ASSIGNMENTS
    // ──────────────────────────────────────────────────────────────────────────
    private function seedCourseAssignments(\Illuminate\Support\Collection $lecturers, \Illuminate\Support\Collection $programs): void
    {
        if ($lecturers->isEmpty()) return;

        foreach ($programs as $pi => $program) {
            $courses   = Course::where('program_id', $program->id)->get();
            $lecturerIdx = 0;
            foreach ($courses as $course) {
                $lecturer = $lecturers->get($lecturerIdx % $lecturers->count());
                CourseAssignment::firstOrCreate([
                    'lecturer_id' => $lecturer->id,
                    'course_id'   => $course->id,
                    'program_id'  => $program->id,
                ], [
                    'year' => now()->year . '/' . (now()->year + 1),
                ]);
                $lecturerIdx++;
            }
        }
    }
}
