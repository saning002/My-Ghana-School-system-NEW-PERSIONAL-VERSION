@extends('layouts.app')
@section('title', $course->name)

@section('content')
<div class="max-w-3xl space-y-6">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <div class="flex items-center justify-between mb-3">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm font-bold">{{ $course->code }}</span>
            <a href="{{ route('admin.courses.edit', $course) }}" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-colors">
                <i class="fas fa-pen mr-2"></i>Edit
            </a>
        </div>
        <h2 class="text-xl font-bold text-gray-800 mb-2">{{ $course->name }}</h2>
        <p class="text-sm text-gray-500">{{ $course->description ?? 'No description provided.' }}</p>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-50">
            <h3 class="font-semibold text-gray-800">Enrolled Students ({{ $course->enrollments->count() }})</h3>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse($course->enrollments as $enrollment)
            <div class="flex items-center gap-4 px-6 py-3.5">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">
                    {{ strtoupper(substr($enrollment->student->user->full_name ?? '?', 0, 1)) }}
                </div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-gray-800">{{ $enrollment->student->user->full_name }}</p>
                    <p class="text-xs text-gray-400">{{ $enrollment->student->student_id }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-gray-400 text-sm">No students enrolled in this course.</div>
            @endforelse
        </div>
    </div>
    <a href="{{ route('admin.courses.index') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 font-medium">← Back to Courses</a>
</div>
@endsection
