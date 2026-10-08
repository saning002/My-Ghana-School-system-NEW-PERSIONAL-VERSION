<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassTeacherAssignment;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\Request;

class ClassTeacherController extends Controller
{
    public function index()
    {
        $programs  = Program::with(['classTeacherAssignment.lecturer'])
            ->orderBy('sequence')
            ->get();

        $lecturers = User::whereIn('role', ['lecturer','lecturers','teacher'])
            ->orderBy('full_name')
            ->get();

        return view('admin.class-teachers.index', compact('programs', 'lecturers'));
    }

    public function assign(Request $request)
    {
        $request->validate([
            'program_id'  => 'required|exists:programs,id',
            'lecturer_id' => 'required|exists:users,id',
        ]);

        ClassTeacherAssignment::updateOrCreate(
            ['program_id' => $request->program_id],
            ['lecturer_id' => $request->lecturer_id]
        );

        $program  = Program::find($request->program_id);
        $lecturer = User::find($request->lecturer_id);

        return back()->with('success', "{$lecturer->full_name} assigned as class teacher for {$program->name}.");
    }

    public function remove(Request $request)
    {
        $request->validate(['program_id' => 'required|exists:programs,id']);

        ClassTeacherAssignment::where('program_id', $request->program_id)->delete();

        return back()->with('success', 'Class teacher removed.');
    }
}
