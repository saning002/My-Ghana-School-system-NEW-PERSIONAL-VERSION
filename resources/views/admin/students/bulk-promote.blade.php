@extends('layouts.app')
@section('title', 'Bulk Student Promotion')
@section('subtitle', 'Promote students to their next program in bulk')

@section('content')
<div class="space-y-6">
    <div class="card border border-amber-100 bg-[#fffaf0] p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">Academic Management</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">Bulk Student Promotion</h2>
                <p class="text-sm text-slate-600 mt-1">Select students grouped by program to advance them to the next academic level.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.students.bulk-promotion') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    <i class="fas fa-filter"></i> Filtered Mode
                </a>
            </div>
        </div>
    </div>

    @if($programs->isEmpty())
        <div class="card p-12 text-center text-slate-500">
            <i class="fas fa-graduation-cap text-4xl mb-4 text-slate-300"></i>
            <p class="font-medium text-base">No programs found.</p>
        </div>
    @else
        <form method="POST" action="{{ route('admin.students.bulk-promote.submit') }}" x-data="{ selectedStudents: [] }">
            @csrf

            <div class="flex items-center justify-between bg-white p-4 rounded-2xl border border-slate-200 shadow-sm sticky top-16 z-20 mb-6">
                <div class="flex items-center gap-3">
                    <span class="text-sm font-semibold text-slate-700">Selected Students:</span>
                    <span class="px-3 py-1 bg-amber-100 text-amber-800 rounded-full text-xs font-bold" x-text="selectedStudents.length">0</span>
                </div>
                <div class="flex items-center gap-3">
                    <button type="button" x-on:click="selectedStudents = Array.from(document.querySelectorAll('.student-checkbox:not(:disabled)')).map(cb => cb.value)" class="text-xs text-amber-700 font-semibold hover:underline">
                        Select All
                    </button>
                    <span class="text-slate-300">|</span>
                    <button type="button" x-on:click="selectedStudents = []" class="text-xs text-slate-500 font-semibold hover:underline">
                        Deselect All
                    </button>
                    <button type="submit" :disabled="selectedStudents.length === 0" class="btn-gold inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fas fa-[#D4A017] fa-level-up-alt"></i> Promote Selected
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                @foreach($programs as $index => $program)
                    @php
                        $isFinal = ($program->id === $finalProgramId);
                        $nextProg = ($program->sequence !== null)
                            ? $programs->where('sequence', '>', $program->sequence)->sortBy('sequence')->first()
                            : null;
                    @endphp
                    <div class="card p-6 border border-slate-200 bg-white" x-data="{ programChecked: false }">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-slate-100">
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">
                                        {{ $program->sequence ?? ($index + 1) }}
                                    </span>
                                    <h3 class="text-lg font-bold text-slate-900">{{ $program->name }}</h3>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">
                                    @if($isFinal)
                                        <span class="text-amber-600 font-semibold"><i class="fas fa-exclamation-triangle mr-1"></i> Final Program — Students will be marked as Graduated</span>
                                    @elseif($nextProg)
                                        Advances to: <strong class="text-slate-700">{{ $nextProg->name }}</strong>
                                    @endif
                                </p>
                            </div>
                            @if($program->students->isNotEmpty())
                                <label class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 cursor-pointer">
                                    <input type="checkbox" x-on:change="
                                        const ids = [{{ $program->students->pluck('id')->implode(',') }}];
                                        if($el.checked) {
                                            selectedStudents = [...new Set([...selectedStudents, ...ids])];
                                        } else {
                                            selectedStudents = selectedStudents.filter(id => !ids.includes(Number(id)));
                                        }
                                    " class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                                    Select Program Group ({{ $program->students->count() }})
                                </label>
                            @endif
                        </div>

                        @if($program->students->isEmpty())
                            <p class="text-xs text-slate-400 italic py-2">No active students in this program.</p>
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($program->students as $student)
                                    <label class="flex items-center justify-between p-3 rounded-xl border border-slate-100 bg-slate-50/70 hover:bg-slate-100/80 transition-colors cursor-pointer">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" x-model.number="selectedStudents" class="student-checkbox rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                                            <div class="min-w-0">
                                                <p class="text-sm font-semibold text-slate-800 truncate">{{ $student->user?->full_name ?? 'Unnamed Student' }}</p>
                                                <p class="text-xs text-slate-500">{{ $student->student_id }}</p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 uppercase">
                                            {{ $student->status }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </form>
    @endif
</div>
@endsection
