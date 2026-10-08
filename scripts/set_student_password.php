<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$studentId = $argv[1] ?? null;
$password  = $argv[2] ?? null;

if (! $studentId || ! $password) {
    echo "Usage: php scripts/set_student_password.php STUDENT_ID PASSWORD\n";
    exit(1);
}

$student = \App\Models\Student::where('student_id', $studentId)->first();
if (! $student) {
    echo "Student not found: {$studentId}\n";
    exit(1);
}

try {
    $student->update(['password' => $password]);
    echo "Password updated for {$student->student_id}\n";
} catch (Throwable $e) {
    echo "Failed to update password: " . $e->getMessage() . "\n";
    exit(1);
}
