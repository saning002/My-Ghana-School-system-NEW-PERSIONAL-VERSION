@extends('layouts.app')
@section('title', $program->name)
@section('subtitle','Program Details')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    {{-- Header --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-5" style="background:linear-gradient(135deg,#1a1a2e,#0f3460)">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-lg font-bold text-white">{{ $program->name }}</h2>
                    <p class="text-blue-300 text-xs">{{ $program->duration }} months · {{ $enrollmentCount }} students enrolled</p>
                </div>
            </div>
        </div>
        @if($program->requirements)
        <div class="px-5 py-4 bg-blue-50 border-b border-blue-100">
            <p class="text-xs font-semibold text-blue-600 uppercase tracking-wider mb-1">Requirements</p>
            <p class="text-sm text-gray-700">{{ $program->requirements }}</p>
        </div>
        @endif
        <div class="px-5 py-4 flex gap-2">
            <a href="{{ route('admin.programs.edit',$program) }}" class="flex items-center gap-2 px-4 py-2.5 bg-primary-600 text-white rounded-xl text-xs font-semibold active:bg-primary-700">
                <i class="fas fa-pen"></i> Edit
            </a>
            <a href="{{ route('admin.programs.index') }}" class="flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl text-xs font-semibold active:bg-gray-200">
                ← Back
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-indigo-600">{{ $enrollmentCount }}</p>
            <p class="text-xs text-gray-500 mt-1">Enrolled Students</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-3xl font-bold text-blue-600">{{ $program->courses->count() }}</p>
            <p class="text-xs text-gray-500 mt-1">Courses / Subjects</p>
        </div>
    </div>

    {{-- Courses under this program --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Courses in this Program</h3>
            <a href="{{ route('admin.courses.create') }}" class="text-xs text-primary-600 font-semibold">+ Add Course</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($program->courses as $course)
            <div class="flex items-center gap-3 px-5 py-3.5">
                <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-book-open text-blue-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $course->name }}</p>
                    <p class="text-xs text-gray-400 font-mono">{{ $course->code }}</p>
                </div>
                <a href="{{ route('admin.courses.show',$course) }}" class="text-xs text-primary-600 font-semibold flex-shrink-0">View →</a>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No courses assigned to this program yet.</div>
            @endforelse
        </div>
    </div>

    {{-- Students in this program --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <h3 class="text-sm font-bold text-gray-800">Enrolled Students</h3>
            <span class="text-xs bg-indigo-100 text-indigo-700 px-2.5 py-1 rounded-full font-semibold">{{ $enrollmentCount }}</span>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($program->students as $student)
            <a href="{{ route('admin.students.show',$student) }}" class="flex items-center gap-3 px-5 py-3.5 active:bg-gray-50 transition-colors">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $student->user->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $student->student_id }} · Level {{ $student->level }}</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-semibold flex-shrink-0
                    {{ $student->status==='active'?'bg-emerald-100 text-emerald-700':($student->status==='graduated'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700') }}">
                    {{ ucfirst($student->status) }}
                </span>
            </a>
            @empty
            <div class="px-5 py-8 text-center text-gray-400 text-sm">No students enrolled yet.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection
