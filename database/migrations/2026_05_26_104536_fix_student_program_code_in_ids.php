<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Program;
use App\Services\StudentIdService;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $studentIdService = app(StudentIdService::class);
        
        // Find all programs to get their codes
        $programs = Program::all()->keyBy('id');
        
        // Find all students with their users eager loaded
        $students = Student::with('user')->get();
        
        $fixed = 0;
        foreach ($students as $student) {
            if ($student->program_id && isset($programs[$student->program_id])) {
                $currentProgram = $programs[$student->program_id];
                $expectedCode = $studentIdService->getProgramCode($currentProgram);
                
                // Parse student ID
                $parts = explode('/', $student->student_id);
                if (count($parts) >= 3) {
                    $actualCode = $parts[2] ?? '';
                    
                    // If codes don't match, update the ID
                    if ($actualCode !== $expectedCode) {
                        $newId = implode('/', [
                            $parts[0], // KMC
                            $parts[1], // branch code
                            $expectedCode, // corrected program code
                            $parts[3] ?? '001', // sequence
                        ]);
                        
                        DB::table('students')
                            ->where('id', $student->id)
                            ->update(['student_id' => $newId]);
                        
                        $name = $student->user ? $student->user->full_name : 'Unknown';
                        echo "Fixed: {$student->student_id} → {$newId} for {$name}" . PHP_EOL;
                        $fixed++;
                    }
                }
            }
        }
        
        echo "\n✅ Fixed {$fixed} student ID(s)." . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be safely reversed as we don't have the old IDs
        // The system will continue to regenerate correct IDs
    }
};
