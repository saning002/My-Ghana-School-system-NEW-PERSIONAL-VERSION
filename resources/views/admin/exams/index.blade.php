@extends('layouts.app')
@section('title','Exams & Results')
@section('subtitle','Enter scores and generate report cards')

@section('content')

<div class="page-hero-banner">
    <div class="hero-left">
        <h1><i class="fas fa-file-alt mr-2" style="color:var(--primary);"></i>Exams &amp; Results</h1>
        <p>Enter scores and generate report cards for students</p>
    </div>
    <div class="hero-actions">
        <a href="{{ route('admin.exams.create') }}" class="btn-primary-action">
            <i class="fas fa-plus"></i> Enter Scores
        </a>
        <a href="{{ route('admin.exams.bulk') }}" class="btn-secondary-action">
            <i class="fas fa-download"></i> Bulk Download
        </a>
        <a href="{{ route('admin.exams.sheet') }}" class="btn-secondary-action">
            <i class="fas fa-table"></i> Exam Sheet
        </a>
    </div>
</div>

<style>
    .exam-card {
        background: #fff;
        border-radius: 1.1rem;
        border: 1px solid rgba(148,163,184,.15);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 12px -4px rgba(15,23,42,.08);
    }
    .exam-card:hover {
        box-shadow: 0 12px 28px -8px rgba(15,23,42,.15);
        transform: translateY(-3px);
        border-color: var(--primary);
    }
    .exam-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: var(--primary);
    }
</style>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($programs as $program)
    <div class="exam-card p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 bg-[var(--primary-pale)]">
                <i class="fas fa-file-chart-line text-base text-[var(--primary-text)]"></i>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-[var(--primary-pale)] text-[var(--primary-text)]">Seq. {{ $program->sequence }}</span>
        </div>
        <h3 class="text-[15px] font-bold text-slate-900 mb-2 leading-tight">{{ $program->name }}</h3>
        <p class="text-sm text-slate-600 mb-3">
            <i class="fas fa-book-open mr-1.5" style="color: var(--brand);"></i>
            {{ $program->courses_count }} course{{ $program->courses_count !== 1 ? 's' : '' }}
        </p>
        <p class="text-xs text-slate-500 mb-4 pb-4 border-b border-slate-100">
            {{ $program->duration }}-month program Â· Level {{ $program->sequence }}
        </p>
        <a href="{{ route('admin.exams.create') }}?program_id={{ $program->id }}"
           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition-all bg-[var(--primary-pale)] text-[var(--primary-text)] hover:bg-[var(--primary-pale)]">
            <i class="fas fa-pen"></i> Enter Scores
        </a>
    </div>
    @empty
    <div class="col-span-full card p-16 text-center bg-gray-50">
        <div class="w-20 h-20 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-file-alt text-2xl"></i>
        </div>
        <p class="text-gray-600 font-medium text-lg mb-2">No programs found</p>
        <p class="text-gray-500 text-sm">Create programs to start entering exam scores</p>
    </div>
    @endforelse
</div>
@endsection
