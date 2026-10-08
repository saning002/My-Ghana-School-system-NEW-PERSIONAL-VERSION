@extends('layouts.app')
@section('title','Attendance Report')
@section('subtitle','Attendance records and student summaries')

@push('styles')
<style>
@media print {
    nav, aside, .no-print, form, .pagination { display: none !important; }
    body { font-size: 11px; }
}
</style>
@endpush

@section('content')

{{-- Filters --}}
<div class="card p-4 mb-5 no-print">
    <form method="GET" action="{{ route('admin.reports.attendance') }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Course</label>
            <select name="course_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Courses</option>
                @foreach($courses as $c)
                <option value="{{ $c->id }}" {{ request('course_id')==$c->id?'selected':'' }}>{{ $c->code }} - {{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Status</label>
            <select name="status" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All</option>
                <option value="present" {{ request('status')==='present'?'selected':'' }}>Present</option>
                <option value="absent"  {{ request('status')==='absent'?'selected':'' }}>Absent</option>
            </select>
        </div>
        <div class="col-span-2 sm:col-span-2 flex items-end gap-2">
            <button type="submit" class="flex-1 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.reports.attendance') }}" class="py-2.5 px-3 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

{{-- Student Attendance Summary --}}
<div class="card overflow-hidden mb-5">
    <div class="px-5 py-4 border-b border-gray-50">
        <h3 class="text-sm font-bold text-gray-800">Student Attendance Summary</h3>
    </div>
    <div class="divide-y divide-gray-50">
        @foreach($studentSummary->take(10) as $item)
        <div class="flex items-center gap-3 px-5 py-3.5">
            <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($item['student']->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $item['student']->user->full_name }}</p>
                <div class="flex items-center gap-2 mt-1">
                    <div class="flex-1 bg-gray-100 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full {{ $item['rate']>=75?'bg-emerald-500':($item['rate']>=50?'bg-amber-500':'bg-red-500') }}"
                             style="width:{{ $item['rate'] }}%"></div>
                    </div>
                    <span class="text-xs font-bold {{ $item['rate']>=75?'text-emerald-600':($item['rate']>=50?'text-amber-600':'text-red-600') }} flex-shrink-0">
                        {{ $item['rate'] }}%
                    </span>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-xs font-semibold text-gray-700">{{ $item['present'] }}/{{ $item['total'] }}</p>
                <p class="text-xs text-gray-400">classes</p>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- Records --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
        <h3 class="text-sm font-bold text-gray-800">Attendance Records</h3>
        <div class="flex items-center gap-3">
            <span class="text-xs text-gray-400">{{ $attendances->total() }} records</span>
            {{-- Action Buttons --}}
            <div class="flex gap-2 no-print">
                <a href="{{ route('admin.reports.attendance.preview', request()->query()) }}" target="_blank"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">
                    <i class="fas fa-eye"></i> Preview
                </a>
                <a href="{{ route('admin.reports.attendance.pdf', request()->query()) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 {{ $attendances->total() === 0 ? 'opacity-50 pointer-events-none' : '' }}">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                <a href="{{ route('admin.reports.attendance.excel', request()->query()) }}"
                   class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-50 text-green-700 hover:bg-green-100 {{ $attendances->total() === 0 ? 'opacity-50 pointer-events-none' : '' }}">
                    <i class="fas fa-file-excel"></i> Excel
                </a>
                <button onclick="window.print()" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white" style="background:#D4A017">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile --}}
    <div class="divide-y divide-gray-50 lg:hidden">
        @forelse($attendances as $att)
        <div class="flex items-center gap-3 px-5 py-3.5">
            <div class="w-8 h-8 rounded-full {{ $att->status==='present'?'bg-emerald-100':'bg-red-100' }} flex items-center justify-center flex-shrink-0">
                <i class="fas {{ $att->status==='present'?'fa-check text-emerald-600':'fa-times text-red-500' }} text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-gray-800 truncate">{{ $att->student->user->full_name??'—' }}</p>
                <p class="text-xs text-gray-400">{{ $att->course->code??'—' }} · {{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full font-semibold flex-shrink-0 {{ $att->status==='present'?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-600' }}">
                {{ ucfirst($att->status) }}
            </span>
        </div>
        @empty
        <div class="px-5 py-8 text-center text-gray-400 text-sm">No records found.</div>
        @endforelse
    </div>

    {{-- Desktop --}}
    <div class="table-wrap hidden lg:block">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Course</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($attendances as $att)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4 text-sm font-semibold text-gray-800">{{ $att->student->user->full_name??'—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600"><span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">{{ $att->course->code??'—' }}</span></td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $att->academicPeriod->month??'—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ \Carbon\Carbon::parse($att->date)->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $att->status==='present'?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-600' }}">
                            {{ ucfirst($att->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($attendances->hasPages())
    <div class="px-5 py-4 border-t border-gray-50">{{ $attendances->links() }}</div>
    @endif
</div>
@endsection
