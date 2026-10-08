@extends('layouts.app')
@section('title', 'Attendance')
@section('subtitle', 'View and manage attendance records')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
    <div class="card p-4 flex items-center justify-between border-l-4 border-primary-500">
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Students</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ $totalStudents }}</h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-primary-50 flex items-center justify-center text-primary-600">
            <i class="fas fa-users text-xl"></i>
        </div>
    </div>
    <div class="card p-4 flex items-center justify-between border-l-4 border-emerald-500">
        <div>
            <p class="text-sm text-gray-500 font-medium">Total Present Selected Date</p>
            <h3 class="text-2xl font-bold text-gray-800">{{ $totalPresentToday }}</h3>
        </div>
        <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600">
            <i class="fas fa-check-circle text-xl"></i>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-5">
    @foreach($classStats as $stat)
    <div class="card p-4 border border-gray-100 shadow-sm hover:shadow-md transition-shadow">
        <h4 class="font-bold text-gray-800 mb-2 truncate" title="{{ $stat['program']->name }}">{{ $stat['program']->name }}</h4>
        <div class="flex justify-between items-center text-sm mb-1">
            <span class="text-gray-500">Total:</span>
            <span class="font-semibold">{{ $stat['total'] }}</span>
        </div>
        <div class="flex justify-between items-center text-sm mb-1">
            <span class="text-emerald-600">Present:</span>
            <span class="font-semibold text-emerald-700">{{ $stat['present'] }}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-red-500">Absent:</span>
            <span class="font-semibold text-red-600">{{ $stat['absent'] }}</span>
        </div>
    </div>
    @endforeach
</div>

<div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-5">
    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex-1 flex flex-wrap gap-2">
        <div class="relative flex-1 min-w-40">
            <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search student..."
                class="w-full pl-9 pr-4 py-2.5 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
        </div>
        <select name="program_id"
            class="px-3 py-2.5 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
            <option value="">All Programs</option>
            @foreach($programs ?? [] as $program)
                <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                    {{ $program->name }}
                </option>
            @endforeach
        </select>
        <div class="relative w-40">
            <input type="date" name="date" value="{{ $date }}"
                class="w-full px-3 py-2.5 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
        </div>
        <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white shadow-sm" style="background:#D4A017">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
        @if(request()->hasAny(['search','program_id','date']) && request('date') !== date('Y-m-d'))
        <a href="{{ route('admin.attendance.index') }}" class="px-3 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold" title="Clear">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </form>
    <a href="{{ route('admin.attendance.create') }}"
       class="flex items-center gap-2 px-5 py-2.5 text-white rounded-xl text-sm font-semibold shadow-sm shrink-0"
       style="background:#D4A017">
        <i class="fas fa-plus"></i> Mark Attendance
    </a>
</div>

<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
        <h3 class="text-sm font-bold" style="color:#78520a">
            <i class="fas fa-clipboard-check mr-2"></i>Attendance Records
        </h3>
    </div>
    <div class="table-wrap">
        <table class="w-full">
            <thead>
                <tr class="border-b border-yellow-50" style="background:#fef9c3">
                    <th class="text-left px-6 py-3.5 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Student</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Course</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Program</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Period</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Date</th>
                    <th class="text-left px-6 py-3.5 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-yellow-50">
                @forelse($attendances as $att)
                <tr class="hover:bg-yellow-50/30 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs flex-shrink-0
                                {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                                {{ strtoupper(substr($att->student->user->full_name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">{{ $att->student->user->full_name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $att->student->student_id ?? '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <span class="font-mono text-xs bg-amber-50 text-amber-700 px-2 py-1 rounded-lg">{{ $att->course->code ?? '—' }}</span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $att->student->program->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $att->academicPeriod->month ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                            {{ $att->status === 'present' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }}">
                            {{ ucfirst($att->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <i class="fas fa-clipboard-check text-gray-200 text-5xl mb-4 block"></i>
                        <p class="text-gray-400 font-medium text-sm">No attendance records yet.</p>
                        <a href="{{ route('admin.attendance.create') }}"
                           class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 text-white rounded-xl text-sm font-semibold"
                           style="background:#D4A017">
                            <i class="fas fa-plus"></i> Mark First Attendance
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($attendances->hasPages())
    <div class="px-6 py-4 border-t border-yellow-50">{{ $attendances->links() }}</div>
    @endif
</div>
@endsection
