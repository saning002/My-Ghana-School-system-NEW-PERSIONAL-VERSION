<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = auth()->user()->student()->with(['enrollments.course', 'attendances'])->first();

        if (!$student) {
            abort(403, 'Student profile not found.');
        }

        return view('student.dashboard', compact('student'));
    }
}
