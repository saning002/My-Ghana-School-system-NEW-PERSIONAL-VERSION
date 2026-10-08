@extends('layouts.app')
@section('title','Exams & Results')
@section('subtitle','Enter scores and generate report cards')

@section('content')

<style>
    .exam-card {
        background: #fffef8;
        border-radius: 18px;
        border: 1px solid rgba(15, 23, 42, 0.06);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
    }
    
    .exam-card:hover {
        box-shadow: 0 18px 34px rgba(15, 23, 42, 0.12);
        transform: translateY(-4px);
        border-color: rgba(212, 160, 23, 0.25);
    }
    
    .exam-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #D4A017;
    }
</style>

<div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900">Exams & Results</h2>
        <p class="text-sm text-slate-500 mt-1">{{ $programs->count() }} program{{ $programs->count() !== 1 ? 's' : '' }} available for scoring</p>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.exams.bulk') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-2xl text-sm font-semibold shadow-lg hover:shadow-xl transition-all">
            <i class="fas fa-file-pdf"></i> Bulk Download
        </a>
        <a href="{{ route('admin.exams.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-yellow-600 text-white rounded-2xl text-sm font-semibold hover:bg-yellow-700 shadow-sm transition-all">
            <i class="fas fa-plus"></i> Enter Scores
        </a>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($programs as $program)
    <div class="exam-card p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 bg-yellow-100">
                <i class="fas fa-file-chart-line text-base text-yellow-700"></i>
            </div>
            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-yellow-100 text-yellow-700">Seq. {{ $program->sequence }}</span>
        </div>
        <h3 class="text-[15px] font-bold text-slate-900 mb-2 leading-tight">{{ $program->name }}</h3>
        <p class="text-sm text-slate-600 mb-3">
            <i class="fas fa-book-open mr-1.5" style="color: #D4A017;"></i>
            {{ $program->courses_count }} course{{ $program->courses_count !== 1 ? 's' : '' }}
        </p>
        <p class="text-xs text-slate-500 mb-4 pb-4 border-b border-slate-100">
            {{ $program->duration }}-month program · Level {{ $program->sequence }}
        </p>
        <a href="{{ route('admin.exams.create') }}?program_id={{ $program->id }}"
           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl text-sm font-semibold transition-all bg-yellow-50 text-yellow-700 hover:bg-yellow-100">
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
