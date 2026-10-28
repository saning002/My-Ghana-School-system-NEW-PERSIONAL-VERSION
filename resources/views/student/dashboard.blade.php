@extends('layouts.app')

@section('content')
<div class="mb-6 flex justify-between items-end">
    <div>
        <h2 class="text-2xl font-semibold text-gray-800">Student Portal</h2>
        <p class="text-gray-600 text-sm mt-1">{{ auth()->user()->full_name }} | {{ $student->student_id }}</p>
    </div>
    <div>
        <span class="px-3 py-1 bg-emerald-100 text-emerald-800 rounded-full text-sm font-semibold border border-emerald-200">Status: {{ ucfirst($student->status) }}</span>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Enrolled Courses -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-semibold text-gray-800 text-lg">My Courses</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($student->enrollments as $enrollment)
                    <div class="p-6 flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold text-indigo-600 mb-1">{{ $enrollment->course->code }}</p>
                            <h4 class="text-lg font-bold text-gray-800">{{ $enrollment->course->name }}</h4>
                            <p class="text-sm text-gray-500 mt-1">Year: {{ $enrollment->year }}</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center border border-indigo-100">
                            <i class="fas fa-book text-indigo-400"></i>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-500">
                        <p>No courses enrolled yet.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Attendance Overview -->
    <div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h3 class="font-semibold text-gray-800 text-lg">Attendance Overview</h3>
            </div>
            <div class="p-6">
                @php
                    $total = $student->attendances->count();
                    $present = $student->attendances->where('status', 'present')->count();
                    $percentage = $total > 0 ? round(($present / $total) * 100) : 0;
                @endphp
                
                <div class="flex items-center justify-center mb-6">
                    <div class="relative w-32 h-32">
                        <svg class="w-full h-full" viewBox="0 0 36 36">
                            <path class="text-gray-200" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                            <path class="text-indigo-600" stroke-dasharray="{{ $percentage }}, 100" stroke-width="3" stroke-linecap="round" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center flex-col">
                            <span class="text-2xl font-bold text-gray-800">{{ $percentage }}%</span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between text-sm text-gray-600 px-4">
                    <div class="text-center">
                        <p class="font-semibold text-gray-800">{{ $present }}</p>
                        <p>Classes Attended</p>
                    </div>
                    <div class="text-center">
                        <p class="font-semibold text-gray-800">{{ $total - $present }}</p>
                        <p>Classes Missed</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
