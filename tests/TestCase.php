<?php

namespace Tests;

use App\Models\ChurchBranch;
use App\Models\Program;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // ── Shared factory helpers ────────────────────────────────────────────────

    protected function makeChurchBranch(): ChurchBranch
    {
        return ChurchBranch::create([
            'name'     => 'Test Branch',
            'location' => 'Test Location',
        ]);
    }

    protected function makeProgram(string $name = 'Test Program', int $sequence = 1): Program
    {
        return Program::create([
            'name'     => $name,
            'sequence' => $sequence,
            'duration' => 6,
        ]);
    }

    protected function makeStudent(Program $program, string $status = 'active'): Student
    {
        $branch = $this->makeChurchBranch();

        $user = User::create([
            'full_name' => 'Test Student ' . uniqid(),
            'email'     => uniqid('stu') . '@test.com',
            'password'  => bcrypt('password'),
            'role'      => 'student',
        ]);

        return Student::create([
            'user_id'          => $user->id,
            'student_id'       => 'STU' . uniqid(),
            'program_id'       => $program->id,
            'church_branch_id' => $branch->id,
            'status'           => $status,
            'admission_date'   => now()->toDateString(),
        ]);
    }
}
