@extends('layouts.app')
@section('title', $programLabelSingular.' Report')
@section('subtitle', 'Enrollment and course breakdown per '.strtolower($programLabelSingular))

@push('styles')
<style>
@media print {
    nav, aside, .no-print, form, .pagination { display: none !important; }
    body { font-size: 11px; }
}
</style>
@endpush

@section('content')

{{-- Action Buttons --}}
<div class="flex flex-wrap gap-2 mb-5 no-print justify-end">
    <a href="{{ route('admin.reports.programs.preview') }}" target="_blank"
       class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">
        <i class="fas fa-eye"></i> Preview
    </a>
    <a href="{{ route('admin.reports.programs.pdf') }}"
       class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-red-50 text-red-700 hover:bg-red-100 {{ $programs->isEmpty() ? 'opacity-50 pointer-events-none' : '' }}">
        <i class="fas fa-file-pdf"></i> PDF
    </a>
    <a href="{{ route('admin.reports.programs.excel') }}"
       class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-green-50 text-green-700 hover:bg-green-100 {{ $programs->isEmpty() ? 'opacity-50 pointer-events-none' : '' }}">
        <i class="fas fa-file-excel"></i> Excel
    </a>
    <button onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#D4A017">
        <i class="fas fa-print"></i> Print
    </button>
</div>

<div class="space-y-4">
    @foreach($programs as $program)
    @php
        $active    = $program->students->where('status','active')->count();
        $graduated = $program->students->where('status','graduated')->count();
        $suspended = $program->students->where('status','suspended')->count();
        $total     = $program->students_count;
    @endphp
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-bold text-gray-800">{{ $program->name }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $program->duration }} months · {{ $program->courses->count() }} courses</p>
            </div>
            <a href="{{ route('admin.programs.show',$program) }}" class="text-xs text-primary-600 font-semibold">View →</a>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-4 divide-x divide-gray-50 border-b border-gray-50">
            <div class="px-4 py-3 text-center">
                <p class="text-xl font-bold text-gray-800">{{ $total }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Total</p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-xl font-bold text-emerald-600">{{ $active }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Active</p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-xl font-bold text-blue-600">{{ $graduated }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Graduated</p>
            </div>
            <div class="px-4 py-3 text-center">
                <p class="text-xl font-bold text-red-500">{{ $suspended }}</p>
                <p class="text-xs text-gray-400 mt-0.5">Suspended</p>
            </div>
        </div>

        {{-- Courses --}}
        <div class="px-5 py-3">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Courses</p>
            <div class="flex flex-wrap gap-2">
                @forelse($program->courses as $course)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-lg text-xs font-semibold">
                    <span class="font-mono">{{ $course->code }}</span>
                    <span class="text-blue-400">·</span>
                    {{ $course->name }}
                </span>
                @empty
                <span class="text-xs text-gray-400">No courses assigned</span>
                @endforelse
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection
