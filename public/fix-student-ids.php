<?php
/**
 * Fix Student IDs with Mismatched Program Codes
 * Access: https://kmc-college.onrender.com/fix-student-ids.php
 * 
 * This script fixes students whose registration IDs have outdated program codes
 * (e.g., KMC/KA/PC/001 for students now in First Semester should be KMC/KA/FS/001)
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

try {
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

    use App\Models\Student;
    use App\Models\Program;
    use App\Services\StudentIdService;

    $studentIdService = app(StudentIdService::class);
    
    echo "<h1>🔧 Fixing Student IDs with Mismatched Program Codes</h1>\n";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>\n";
    
    // Find all programs to get their codes
    $programs = Program::all()->keyBy('id');
    
    // Find all students with their users eager loaded
    $students = Student::with('user')->get();
    
    $fixed = 0;
    $checked = 0;
    
    foreach ($students as $student) {
        $checked++;
        
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
                    
                    $student->update(['student_id' => $newId]);
                    
                    $name = $student->user ? $student->user->full_name : 'Unknown';
                    echo "✅ FIXED: {$student->student_id} → {$newId}\n";
                    echo "   Student: {$name}\n";
                    echo "   Program: {$currentProgram->name}\n";
                    echo "   Changed program code: {$actualCode} → {$expectedCode}\n\n";
                    $fixed++;
                }
            }
        }
    }
    
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "✅ COMPLETE!\n";
    echo "   Total students checked: {$checked}\n";
    echo "   Total students fixed: {$fixed}\n";
    echo "   Status: ";
    
    if ($fixed === 0) {
        echo "All student IDs are already correct!\n";
    } else {
        echo "{$fixed} student ID(s) have been updated.\n";
    }
    
    echo str_repeat('=', 70) . "\n";
    echo "\n📋 Summary:\n";
    echo "   • First Semester students should have 'FS' in their ID\n";
    echo "   • Pre-College students should have 'PC' in their ID\n";
    echo "   • Students promoted will automatically get the correct code\n";
    
    echo "</pre>\n";

} catch (\Exception $e) {
    echo "<h1 style='color: red;'>❌ Error</h1>\n";
    echo "<pre style='background: #fee; padding: 15px; border-radius: 5px; color: red;'>\n";
    echo $e->getMessage() . "\n\n";
    echo $e->getTraceAsString();
    echo "</pre>\n";
}
