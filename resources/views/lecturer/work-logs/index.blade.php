@extends('layouts.app')
@section('title','Work Logs')
@section('subtitle','Record your daily teaching activities')

@section('content')
<div class="max-w-5xl mx-auto space-y-5" x-data="{ showForm: {{ $errors->any() ? 'true' : 'false' }} }">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- ── Targets Summary ─────────────────────────────────────────────────── --}}
@if($assignments->isNotEmpty())
<div class="card p-5">
    <h3 class="text-sm font-extrabold text-slate-700 mb-3 flex items-center gap-2">
        <i class="fas fa-bullseye text-amber-500"></i> Your Expected Targets Per Subject
    </h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        @php $seenCourses = []; @endphp
        @foreach($assignments as $a)
        @php
            if (in_array($a->course_id, $seenCourses)) continue;
            $seenCourses[] = $a->course_id;
            $course = $a->course;
            $expCw = $course->expected_classworks ?? $globalCw;
            $expHw = $course->expected_homeworks  ?? $globalHw;
            $expTs = $course->expected_tests       ?? $globalTs;
            $doneCw = $logs->where('course_id', $course->id)->where('type', 'classwork')->count();
            $doneHw = $logs->where('course_id', $course->id)->where('type', 'homework')->count();
            $doneTs = $logs->where('course_id', $course->id)->where('type', 'monthly_test')->count();
        @endphp
        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-2">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-extrabold text-slate-800">{{ $course->name }}</p>
                    <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ $a->program->name ?? '' }}</p>
                </div>
                @if($course->expected_classworks || $course->expected_homeworks || $course->expected_tests)
                <span class="text-[9px] font-bold text-amber-700 bg-amber-100 px-1.5 py-0.5 rounded-full">Custom</span>
                @endif
            </div>
            <div class="grid grid-cols-3 gap-2 text-center">
                @foreach([
                    ['Classwork', $doneCw, $expCw, 'purple'],
                    ['Homework',  $doneHw, $expHw, 'amber'],
                    ['Tests',     $doneTs, $expTs, 'rose'],
                ] as [$lbl, $done, $exp, $color])
                @php $pct = $exp > 0 ? min(100, round(($done/$exp)*100)) : 100; @endphp
                <div>
                    <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-1">{{ $lbl }}</p>
                    <p class="text-sm font-extrabold text-{{ $color }}-600">{{ $done }}<span class="text-[10px] font-medium text-slate-400">/{{ $exp }}</span></p>
                    <div class="mt-1 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                        <div class="h-full bg-{{ $color }}-400 rounded-full transition-all" style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── Log Entry Form ───────────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <button @click="showForm = !showForm"
        class="w-full flex items-center justify-between px-5 py-4 bg-slate-50 hover:bg-slate-100 transition-colors text-left border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-yellow-100 flex items-center justify-center">
                <i class="fas fa-plus text-yellow-600 text-xs"></i>
            </div>
            <p class="text-sm font-extrabold text-slate-800">Add Work Log Entry</p>
        </div>
        <i class="fas text-slate-400 text-xs" :class="showForm ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
    </button>

    <div x-show="showForm" x-cloak class="p-5">
        <form method="POST" action="{{ route('lecturer.work-logs.store') }}" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Course / Subject *</label>
                    <select name="assignment_id" required class="field-input w-full">
                        <option value="">— Select —</option>
                        @foreach($assignments as $a)
                        <option value="{{ $a->id }}" {{ old('assignment_id')==$a->id?'selected':'' }}>
                            {{ $a->course->name }} ({{ $a->program->name ?? '' }})
                        </option>
                        @endforeach
                    </select>
                    @error('assignment_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Activity Type *</label>
                    <select name="type" required class="field-input w-full">
                        <option value="">— Select —</option>
                        <option value="classwork"    {{ old('type')==='classwork'   ?'selected':'' }}>📝 Classwork</option>
                        <option value="homework"     {{ old('type')==='homework'    ?'selected':'' }}>📚 Homework</option>
                        <option value="monthly_test" {{ old('type')==='monthly_test'?'selected':'' }}>📋 Monthly Test</option>
                    </select>
                    @error('type')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Title / Activity Name *</label>
                <input type="text" name="title" value="{{ old('title') }}" required
                    class="field-input w-full" placeholder="e.g. Exercise 4B, Chapter 3 Test">
                @error('title')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Topic Covered</label>
                <input type="text" name="topic_covered" value="{{ old('topic_covered') }}"
                    class="field-input w-full" placeholder="e.g. Fractions and Decimals, Chapter 5">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Notes / Description</label>
                <textarea name="notes" rows="3" class="field-input w-full resize-none"
                    placeholder="Brief description of what was taught, any observations...">{{ old('notes') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Date *</label>
                    <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required class="field-input w-full">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Week Number</label>
                    <input type="number" name="week_number" value="{{ old('week_number') }}" min="1" max="52"
                        class="field-input w-full" placeholder="e.g. 3">
                </div>
            </div>

            <button type="submit"
                class="btn-gold px-6 py-2.5 rounded-xl text-sm font-bold shadow active:scale-95 inline-flex items-center gap-2">
                <i class="fas fa-save text-xs"></i> Save Log Entry
            </button>
        </form>
    </div>
</div>

{{-- ── Log History ─────────────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fas fa-list-ul text-slate-400"></i> My Work Log History
            <span class="rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5">{{ $logs->count() }}</span>
        </h3>
        {{-- Quick filters --}}
        <form method="GET" class="flex gap-2">
            <select name="type" onchange="this.form.submit()" class="text-xs rounded-lg border border-slate-200 px-2 py-1.5 bg-white focus:outline-none">
                <option value="">All Types</option>
                <option value="classwork"    {{ request('type')==='classwork'   ?'selected':'' }}>Classwork</option>
                <option value="homework"     {{ request('type')==='homework'    ?'selected':'' }}>Homework</option>
                <option value="monthly_test" {{ request('type')==='monthly_test'?'selected':'' }}>Tests</option>
            </select>
        </form>
    </div>

    @if($logs->isEmpty())
    <div class="py-12 text-center text-slate-400">
        <i class="fas fa-clipboard-list text-3xl mb-2 block"></i>
        <p class="text-sm font-semibold">No log entries yet. Add your first entry above.</p>
    </div>
    @else
    <div class="divide-y divide-slate-50">
        @foreach($logs as $log)
        @php
            $typeColors = [
                'classwork'    => 'bg-purple-100 text-purple-700',
                'homework'     => 'bg-amber-100 text-amber-700',
                'monthly_test' => 'bg-rose-100 text-rose-700',
            ];
            $typeLabels = ['classwork'=>'Classwork','homework'=>'Homework','monthly_test'=>'Monthly Test'];
            $tc = $typeColors[$log->type] ?? 'bg-slate-100 text-slate-600';
            $tl = $typeLabels[$log->type] ?? $log->type;
        @endphp
        <div class="px-5 py-4 hover:bg-slate-50 transition-colors">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full {{ $tc }}">{{ $tl }}</span>
                        <span class="text-xs font-bold text-slate-700">{{ $log->title }}</span>
                        @if($log->week_number)
                        <span class="text-[10px] text-slate-400 font-semibold">Week {{ $log->week_number }}</span>
                        @endif
                    </div>
                    <div class="flex flex-wrap gap-3 text-[11px] text-slate-500 font-medium">
                        <span><i class="fas fa-book text-[9px] mr-1 opacity-50"></i>{{ $log->course->name ?? '—' }}</span>
                        <span><i class="fas fa-calendar text-[9px] mr-1 opacity-50"></i>{{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}</span>
                        @if($log->topic_covered)
                        <span><i class="fas fa-tag text-[9px] mr-1 opacity-50"></i>{{ $log->topic_covered }}</span>
                        @endif
                    </div>
                    @if($log->notes)
                    <p class="text-xs text-slate-500 mt-1.5 bg-slate-50 rounded-lg px-3 py-2 border border-slate-100">{{ $log->notes }}</p>
                    @endif
                    {{-- Admin comment if any --}}
                    @if($log->admin_comment)
                    <div class="mt-2 rounded-lg border border-indigo-200 bg-indigo-50 px-3 py-2">
                        <p class="text-[10px] font-bold text-indigo-600 uppercase tracking-wider mb-0.5">Admin Feedback</p>
                        <p class="text-xs text-indigo-800">{{ $log->admin_comment }}</p>
                    </div>
                    @endif
                </div>
                <form method="POST" action="{{ route('lecturer.work-logs.destroy', $log) }}"
                      onsubmit="return confirm('Delete this log entry?')">
                    @csrf @method('DELETE')
                    <button type="submit"
                        class="w-7 h-7 flex items-center justify-center rounded-lg bg-slate-100 text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors shrink-0">
                        <i class="fas fa-trash text-[9px]"></i>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

</div>

@push('styles')
<style>
.field-input { padding:10px 14px; border:1px solid #e2e8f0; border-radius:10px; font-size:13px; background:#f8fafc; outline:none; transition:all .15s; width:100%; }
.field-input:focus { border-color:#eab308; box-shadow:0 0 0 3px rgba(234,179,8,.12); background:#fff; }
</style>
@endpush
@endsection
