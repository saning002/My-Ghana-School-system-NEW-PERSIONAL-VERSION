<?php
require 'vendor/autoload.php';
require 'bootstrap/app.php';

use App\Models\Program;
use App\Models\Course;
use App\Models\User;

echo "Available Programs:\n";
$programs = Program::all();
foreach ($programs as $program) {
    echo "  ID: {$program->id}, Name: {$program->name}\n";
    $courses = $program->courses;
    foreach ($courses as $course) {
        echo "    - Course ID: {$course->id}, Name: {$course->name}\n";
    }
}

echo "\n\nSample Students in System:\n";
$students = User::whereHas('student')->limit(5)->get();
foreach ($students as $user) {
    echo "  Name: {$user->full_name}\n";
}
