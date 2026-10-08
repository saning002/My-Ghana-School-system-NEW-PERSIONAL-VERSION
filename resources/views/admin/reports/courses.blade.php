@extends('layouts.app')
@section('title','Course Enrollment Report')
@section('subtitle','Students enrolled per course')

@push('styles')
<style>
@media print {
    nav, aside, .no-print, form, .pagination { display: none !important; }
    body { font-size: 11px; }
}
</style>
@endpush

@section('content')
<div class="card p-4 mb-5 no-print">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-32">
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">{{ $programLabelSingular }}</label>
            <select name="program_id" class="w-full px-3 py-2.5 border border-yellow-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2">
                <option value="">All {{ $programLabel }}</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn-gold px-4 py-2.5 rounded-xl text-sm font-semibold"><i class="fas fa-filter mr-1"></i>Filter</button>
            <a href="{{ route('admin.reports.courses') }}" class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold"><i class="fas fa-times"></i></a>
            <a href="{{ route('admin.reports.courses.preview', request()->query()) }}" target="_blank"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">
                <i class="fas fa-eye"></i> Preview
            </a>
            <a href="{{ route('admin.reports.courses.pdf', request()->query()) }}"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-red-50 text-red-700 hover:bg-red-100 {{ $courses->isEmpty() ? 'opacity-50 pointer-events-none' : '' }}">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.reports.courses.excel', request()->query()) }}"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-green-50 text-green-700 hover:bg-green-100 {{ $courses->isEmpty() ? 'opacity-50 pointer-events-none' : '' }}">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <button type="button" onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#D4A017">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </form>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($courses as $course)
    <div class="card p-5">
        <div class="flex items-start justify-between mb-3">
            <span class="text-xs font-bold px-2.5 py-1 rounded-lg" style="background:#fef9c3;color:#78520a">{{ $course->code }}</span>
            <span class="text-xs text-gray-400">{{ $course->program->name??'—' }}</span>
        </div>
        <h3 class="text-sm font-bold text-ink mb-3">{{ $course->name }}</h3>
        <div class="flex items-center justify-between pt-3 border-t border-yellow-50">
            <span class="text-xs text-gray-500">{{ $course->enrollments_count }} enrolled</span>
            <a href="{{ route('admin.courses.show',$course) }}" class="text-xs font-semibold" style="color:#D4A017">View →</a>
        </div>
    </div>
    @empty
    <div class="col-span-full card p-12 text-center text-gray-400 text-sm">No courses found.</div>
    @endforelse
</div>
@endsection
