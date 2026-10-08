@extends('layouts.app')
@section('title','Student Report')
@section('subtitle','Filter and view student data')

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
    <form method="GET" action="{{ route('admin.reports.students') }}" class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">{{ $programLabelSingular }}</label>
            <select name="program_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All {{ $programLabel }}</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">Status</label>
            <select name="status" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Status</option>
                <option value="active"        {{ request('status')==='active'?'selected':'' }}>Active</option>
                <option value="graduated"     {{ request('status')==='graduated'?'selected':'' }}>Graduated</option>
                <option value="suspended"     {{ request('status')==='suspended'?'selected':'' }}>Suspended</option>
                <option value="manifestation" {{ request('status')==='manifestation'?'selected':'' }}>Manifestation</option>
            </select>
        </div>
        <div class="flex items-end gap-2">
            <button type="submit" class="flex-1 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold active:bg-primary-700">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.reports.students') }}" class="py-2.5 px-3 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold active:bg-gray-200">
                <i class="fas fa-times"></i>
            </a>
        </div>
    </form>
</div>

<div class="flex items-center justify-between mb-3">
    <p class="text-xs text-gray-500">{{ $students->total() }} results</p>
    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.reports.students.preview', request()->query()) }}" target="_blank"
           class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
            <i class="fas fa-eye"></i> Preview
        </a>
        <a href="{{ route('admin.reports.students.pdf', request()->query()) }}"
           class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-red-50 text-red-700 hover:bg-red-100 transition-colors {{ $students->total() === 0 ? 'opacity-50 pointer-events-none' : '' }}"
           @if($students->total() === 0) title="No data to export" @endif>
            <i class="fas fa-file-pdf"></i> PDF
        </a>
        <a href="{{ route('admin.reports.students.excel', request()->query()) }}"
           class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold bg-green-50 text-green-700 hover:bg-green-100 transition-colors {{ $students->total() === 0 ? 'opacity-50 pointer-events-none' : '' }}"
           @if($students->total() === 0) title="No data to export" @endif>
            <i class="fas fa-file-excel"></i> Excel
        </a>
        <button onclick="window.print()" class="flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white transition-colors" style="background:#D4A017">
            <i class="fas fa-print"></i> Print
        </button>
    </div>
</div>

{{-- Mobile Cards --}}
<div class="space-y-3 lg:hidden">
    @forelse($students as $student)
    <div class="card p-4">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm flex-shrink-0">
                {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-800 truncate">{{ $student->user->full_name }}</p>
                <p class="text-xs text-gray-400">{{ $student->student_id }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-full font-semibold flex-shrink-0
                {{ $student->status==='active'?'bg-emerald-100 text-emerald-700':($student->status==='graduated'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700') }}">
                {{ ucfirst($student->status) }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-gray-400">{{ $programLabelSingular }}</p>
                <p class="font-semibold text-gray-700 truncate">{{ $student->program->name??'—' }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-2 text-center">
                <p class="text-gray-400">Courses</p>
                <p class="font-semibold text-gray-700">{{ $student->enrollments->count() }}</p>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center text-gray-400 text-sm">No students match the selected filters.</div>
    @endforelse
</div>

{{-- Desktop Table --}}
<div class="card overflow-hidden hidden lg:block">
    <div class="table-wrap">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Student</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ $programLabelSingular }}</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Ministry Branch</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Courses</th>
                    <th class="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($students as $student)
                <tr class="hover:bg-gray-50/50">
                    <td class="px-6 py-4">
                        <p class="text-sm font-semibold text-gray-800">{{ $student->user->full_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $student->student_id }}</p>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $student->program->name??'—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $student->churchBranch->name??'—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">{{ $student->enrollments->count() }}</td>
                    <td class="px-6 py-4">
                        <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                            {{ $student->status==='active'?'bg-emerald-100 text-emerald-700':($student->status==='graduated'?'bg-blue-100 text-blue-700':'bg-red-100 text-red-700') }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400 text-sm">No students match the filters.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-gray-50">{{ $students->links() }}</div>
    @endif
</div>
@if($students->hasPages())
<div class="mt-4 lg:hidden">{{ $students->links() }}</div>
@endif
@endsection
