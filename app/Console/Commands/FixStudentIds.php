<?php

namespace App\Console\Commands;

use App\Models\Student;
use App\Models\PromotionHistory;
use App\Services\StudentIdService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixStudentIds extends Command
{
    protected $signature = 'students:fix-ids {--dry-run : Show what would be changed without making changes} {--student-id= : Fix only a specific student ID}';
    protected $description = 'Fix student IDs that do not match their current program code after promotion';

    public function __construct(private StudentIdService $studentIdService) {
        parent::__construct();
    }

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $specificStudentId = $this->option('student-id');

        $this->info($dryRun ? '🔍 DRY RUN MODE - No changes will be made' : '⚙️  FIXING STUDENT IDs');
        $this->line('');

        // Find all students with promotions
        $query = Student::with('program', 'churchBranch');
        
        if ($specificStudentId) {
            $query->where('student_id', $specificStudentId);
        }

        $students = $query->get();

        if ($students->isEmpty()) {
            $this->warn('No students found to process.');
            return 0;
        }

        $needsFixing = [];
        $fixed = 0;

        foreach ($students as $student) {
            // Check if student ID matches their current program
            if (!$this->studentIdService->studentIdMatchesProgram($student)) {
                $needsFixing[] = $student;
            }
        }

        if (empty($needsFixing)) {
            $this->info('✅ All student IDs are correct!');
            return 0;
        }

        $this->warn("Found " . count($needsFixing) . " student(s) with incorrect IDs:\n");

        foreach ($needsFixing as $student) {
            $oldId = $student->student_id;
            $expectedProgCode = $this->studentIdService->getProgramCode($student->program);
            
            // Extract parts and rebuild with correct program code
            $parts = explode('/', $oldId);
            if (count($parts) >= 3) {
                $newId = implode('/', [
                    $parts[0], // KMC
                    $parts[1], // branch code
                    $expectedProgCode, // corrected program code
                    $parts[3] ?? '001', // sequence
                ]);

                $this->line("  Student: {$student->user->full_name}");
                $this->line("    Old ID: <fg=red>{$oldId}</>");
                $this->line("    New ID: <fg=green>{$newId}</>");
                $this->line("    Program: {$student->program->name}");

                if (!$dryRun) {
                    try {
                        DB::transaction(function () use ($student, $newId) {
                            $student->update(['student_id' => $newId]);
                        });
                        $this->line("    ✅ <fg=green>FIXED</>");
                        $fixed++;
                    } catch (\Exception $e) {
                        $this->line("    ❌ <fg=red>ERROR: {$e->getMessage()}</>");
                    }
                }
                $this->line('');
            }
        }

        if ($dryRun) {
            $this->info("\n📋 Dry run complete. {$fixed} student(s) would be fixed.");
            $this->warn('Run without --dry-run to apply changes.');
        } else {
            $this->info("\n✅ Fixed {$fixed} student ID(s).");
        }

        return 0;
    }
}
