<?php

namespace Tests\Unit;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentExamEligibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('students');
        Schema::create('students', function ($table) {
            $table->id();
            $table->string('status')->default('active');
            $table->integer('program_id')->nullable();
            $table->integer('church_branch_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->timestamps();
        });
    }

    public function test_manifestation_students_are_excluded_from_exam_lists(): void
    {
        $activeStudent = Student::create(['status' => 'active']);
        $manifestationStudent = Student::create(['status' => 'manifestation']);
        $suspendedStudent = Student::create(['status' => 'suspended']);
        $withdrawnStudent = Student::create(['status' => 'withdrawn']);

        $eligibleIds = Student::query()->forExams()->pluck('id')->all();

        $this->assertContains($activeStudent->id, $eligibleIds);
        $this->assertNotContains($manifestationStudent->id, $eligibleIds);
        $this->assertNotContains($suspendedStudent->id, $eligibleIds);
        $this->assertNotContains($withdrawnStudent->id, $eligibleIds);
    }
}
