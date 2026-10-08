@extends('layouts.app')
@section('title', 'Teacher Work Monitoring')
@section('subtitle', 'Monitor all teacher work log entries and subject progress')

@section('content')
<div class="max-w-7xl mx-auto space-y-5">

{{-- Flash --}}
@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 text-sm font-semibold text-red-800 flex items-center gap-2">
    <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
</div>
@endif

{{-- ── STATS OVERVIEW ──────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
    @php
        $totalLogs = $allLogs->count();
        $cwLogs    = $allLogs->where('type','classwork')->count();
        $hwLogs    = $allLogs->where('type','homework')->count();
        $testLogs  = $allLogs->where('type','monthly_test')->count();
    @endphp
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-slate-800">{{ $totalLogs }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Total Logs</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-purple-600">{{ $cwLogs }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Classwork</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-amber-600">{{ $hwLogs }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Homework</p>
    </div>
    <div class="card p-4 text-center">
        <p class="text-2xl font-extrabold text-rose-600">{{ $testLogs }}</p>
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Tests</p>
    </div>
</div>

{{-- ── FILTERS ─────────────────────────────────────────────────────────── --}}
<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[150px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Teacher</label>
            <select name="lecturer_id" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">All Teachers</option>
                @foreach($lecturers as $l)
                <option value="{{ $l->id }}" {{ ($filterLecturer ?? '') == $l->id ? 'selected' : '' }}>
                    {{ $l->full_name }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Type</label>
            <select name="type" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                <option value="">All Types</option>
                <option value="classwork"    {{ ($filterType ?? '') === 'classwork'    ? 'selected' : '' }}>Classwork</option>
                <option value="homework"     {{ ($filterType ?? '') === 'homework'     ? 'selected' : '' }}>Homework</option>
                <option value="monthly_test" {{ ($filterType ?? '') === 'monthly_test' ? 'selected' : '' }}>Monthly Test</option>
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">From Date</label>
            <input type="date" name="date_from" value="{{ $filterDate ?? '' }}" onchange="this.form.submit()"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
        </div>
        @if(($filterLecturer ?? '') || ($filterType ?? '') || ($filterDate ?? ''))
        <a href="{{ route('admin.work-monitoring.index') }}"
           class="px-4 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-600 text-sm font-bold transition-colors">
            Clear
        </a>
        @endif
    </form>
</div>

{{-- ── ALL RECENT LOG ENTRIES (always visible) ────────────────────────── --}}
<div class="card overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
        <h3 class="text-sm font-extrabold text-slate-800 flex items-center gap-2">
            <i class="fas fa-list-ul text-amber-500"></i>
            All Work Log Entries
            <span class="rounded-full bg-slate-200 text-slate-600 text-[10px] font-bold px-2 py-0.5">{{ $allLogs->count() }}</span>
        </h3>
    </div>

    @if($allLogs->isEmpty())
    <div class="py-14 text-center text-slate-400">
        <i class="fas fa-clipboard-list text-4xl mb-3 block"></i>
        <p class="text-sm font-semibold">No work log entries yet.</p>
        <p class="text-xs mt-1">Teachers submit logs from their portal under <strong>Work Logs</strong>.</p>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 border-b border-slate-100 bg-slate-50">
                    <th class="px-4 py-3 text-left">Date</th>
                    <th class="px-4 py-3 text-left">Teacher</th>
                    <th class="px-4 py-3 text-left">Type</th>
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-4 py-3 text-left">Class</th>
                    <th class="px-4 py-3 text-left">Title / Topic</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($allLogs as $log)
                <tr class="hover:bg-amber-50/20 transition-colors" x-data="{ showComment: false }">
                    <td class="px-4 py-3 text-xs font-bold text-slate-600 whitespace-nowrap">
                        {{ $log->date instanceof \Carbon\Carbon ? $log->date->format('M d, Y') : \Carbon\Carbon::parse($log->date)->format('M d, Y') }}
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white text-[10px] font-extrabold shrink-0">
                                {{ strtoupper(substr($log->lecturer?->full_name ?? '?', 0, 1)) }}
                            </div>
                            <span class="text-xs font-semibold text-slate-700">{{ $log->lecturer?->full_name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->type === 'classwork')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-purple-100 text-purple-700 text-[11px] font-bold">
                                <i class="fas fa-pencil-alt text-[8px]"></i> Classwork
                            </span>
                        @elseif($log->type === 'homework')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 text-[11px] font-bold">
                                <i class="fas fa-book text-[8px]"></i> Homework
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[11px] font-bold">
                                <i class="fas fa-file-alt text-[8px]"></i> Test
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-xs font-bold text-slate-800">{{ $log->course?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-xs text-slate-600">{{ $log->program?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <p class="text-xs font-semibold text-slate-800">{{ $log->title }}</p>
                        @if(!empty($log->topic_covered))
                            <p class="text-[10px] text-slate-500 mt-0.5">
                                <i class="fas fa-tag text-[8px] mr-0.5"></i>{{ $log->topic_covered }}
                            </p>
                        @endif
                        @if(!empty($log->week_number))
                            <p class="text-[10px] text-slate-400">Week {{ $log->week_number }}</p>
                        @endif
                        @if(!empty($log->notes))
                            <p class="text-[10px] text-slate-400 italic mt-0.5">{{ Str::limit($log->notes, 70) }}</p>
                        @endif
                        @if(!empty($log->admin_comment))
                            <div class="mt-1.5 rounded-lg bg-indigo-50 border border-indigo-200 px-2.5 py-1.5">
                                <p class="text-[10px] font-bold text-indigo-600 mb-0.5">
                                    <i class="fas fa-comment text-[8px] mr-0.5"></i>Admin Feedback:
                                </p>
                                <p class="text-[11px] text-indigo-700">{{ $log->admin_comment }}</p>
                            </div>
                        @endif
                        {{-- Inline comment form --}}
                        <div x-show="showComment" x-cloak class="mt-2">
                            <form method="POST" action="{{ route('admin.work-monitoring.comment', $log) }}" class="flex gap-2">
                                @csrf
                                <input type="text" name="admin_comment"
                                    value="{{ $log->admin_comment ?? '' }}"
                                    placeholder="Add feedback for this teacher..."
                                    class="flex-1 text-xs px-2.5 py-1.5 border border-indigo-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-300 bg-white">
                                <button type="submit"
                                    class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg whitespace-nowrap transition-colors">
                                    Save
                                </button>
                            </form>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button type="button" title="View Details"
                                onclick="showLogDetail('{{ route('admin.work-monitoring.log.show', $log) }}')"
                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-[11px] font-bold transition-colors">
                                <i class="fas fa-eye text-[9px]"></i> Detail
                            </button>
                            <button @click="showComment = !showComment" type="button" title="Add feedback"
                                :class="showComment ? 'bg-indigo-200 text-indigo-700' : 'bg-indigo-50 text-indigo-500 hover:bg-indigo-100'"
                                class="w-7 h-7 flex items-center justify-center rounded-lg transition-colors">
                                <i class="fas fa-comment text-[9px]"></i>
                            </button>
                            <form method="POST" action="{{ route('admin.work-monitoring.log.destroy', $log) }}"
                                  onsubmit="return confirm('Delete this log entry?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 flex items-center justify-center rounded-lg bg-red-50 text-red-400 hover:bg-red-100 transition-colors">
                                    <i class="fas fa-trash text-[9px]"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- ── PER-TEACHER PROGRESS (collapsible) ─────────────────────────────── --}}
<div class="card overflow-hidden">
    <button type="button" onclick="togglePanel('teacher-progress')"
        class="w-full flex items-center justify-between px-5 py-4 bg-slate-50 hover:bg-slate-100 transition-colors text-left border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center">
                <i class="fas fa-chart-bar text-amber-600 text-xs"></i>
            </div>
            <div>
                <p class="text-sm font-extrabold text-slate-800">Per-Teacher Subject Progress</p>
                <p class="text-xs text-slate-400">Classwork / Homework / Test progress vs. targets per teacher</p>
            </div>
        </div>
        <i class="fas fa-chevron-down text-slate-400 text-xs" id="teacher-progress-icon"></i>
    </button>

    <div id="teacher-progress" class="hidden">
        <div class="divide-y divide-slate-50">
            @forelse($lecturers as $lecturer)
            @php
                $lecturerAssignments = $assignmentsByLecturer->get($lecturer->id, collect());
                $lecLogs = $logsByLecturer->get($lecturer->id, collect());
            @endphp
            <div class="px-5 py-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-amber-400 to-yellow-600 flex items-center justify-center text-white text-xs font-extrabold shrink-0">
                        {{ strtoupper(substr($lecturer->full_name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-extrabold text-slate-800">{{ $lecturer->full_name }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $lecturerAssignments->count() }} subject(s) &bull;
                            {{ $lecLogs->count() }} log {{ Str::plural('entry', $lecLogs->count()) }}
                        </p>
                    </div>
                </div>

                @if($lecturerAssignments->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($lecturerAssignments as $a)
                    @php
                        $key   = "{$a->lecturer_id}_{$a->program_id}_{$a->course_id}";
                        $stat  = $stats[$key] ?? ['classwork'=>0,'homework'=>0,'monthly_test'=>0];
                        $expCw = $a->course->expected_classworks ?? $globalExpected['classwork'];
                        $expHw = $a->course->expected_homeworks  ?? $globalExpected['homework'];
                        $expTs = $a->course->expected_tests       ?? $globalExpected['monthly_test'];
                        $cwPct = $expCw > 0 ? min(100, round($stat['classwork']/$expCw*100)) : 100;
                        $hwPct = $expHw > 0 ? min(100, round($stat['homework']/$expHw*100)) : 100;
                        $tsPct = $expTs > 0 ? min(100, round($stat['monthly_test']/$expTs*100)) : 100;
                        $allMet = $stat['classwork']>=$expCw && $stat['homework']>=$expHw && $stat['monthly_test']>=$expTs;
                    @endphp
                    <div class="rounded-xl border {{ $allMet ? 'border-emerald-200 bg-emerald-50/30' : 'border-slate-200 bg-white' }} p-3">
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-slate-700 truncate">{{ $a->course->name }}</p>
                            @if($allMet)
                            <span class="text-[9px] font-bold text-emerald-600 bg-emerald-100 px-1.5 py-0.5 rounded-full">✓ Done</span>
                            @else
                            <span class="text-[9px] font-bold text-amber-600 bg-amber-100 px-1.5 py-0.5 rounded-full">In Progress</span>
                            @endif
                        </div>
                        @foreach([
                            ['CW', $stat['classwork'], $expCw, $cwPct, '#a855f6'],
                            ['HW', $stat['homework'],  $expHw, $hwPct, '#f59e0b'],
                            ['T',  $stat['monthly_test'],$expTs,$tsPct,'#f43f5e'],
                        ] as [$lbl, $done, $exp, $pct, $col])
                        <div class="flex items-center gap-2 mb-1">
                            <span class="text-[10px] font-bold text-slate-500 w-5">{{ $lbl }}</span>
                            <div class="flex-1 h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                <div class="h-full rounded-full" style="width:{{ $pct }}%;background:{{ $col }}"></div>
                            </div>
                            <span class="text-[10px] font-bold {{ $done>=$exp?'text-emerald-600':'text-slate-500' }} w-8 text-right">
                                {{ $done }}/{{ $exp }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-slate-400 italic">No course assignments yet.</p>
                @endif
            </div>
            @empty
            <div class="py-10 text-center text-slate-400">
                <i class="fas fa-users-slash text-2xl mb-2 block"></i>
                <p class="text-sm font-semibold">No teachers found.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── SUBJECT EXPECTATIONS ────────────────────────────────────────────── --}}
<div class="card overflow-hidden">
    <button type="button" onclick="togglePanel('exp-panel')"
        class="w-full flex items-center justify-between px-5 py-4 bg-slate-50 hover:bg-slate-100 transition-colors text-left border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl bg-indigo-100 flex items-center justify-center">
                <i class="fas fa-sliders-h text-indigo-600 text-xs"></i>
            </div>
            <p class="text-sm font-extrabold text-slate-800">Set Subject-Specific Expectations</p>
        </div>
        <i class="fas fa-chevron-down text-slate-400 text-xs" id="exp-panel-icon"></i>
    </button>
    <div id="exp-panel" class="hidden border-t border-slate-100">
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($courses as $course)
            <form action="{{ route('admin.work-monitoring.expectations', $course) }}" method="POST"
                class="rounded-xl border border-slate-200 p-4 bg-white space-y-3">
                @csrf
                <div>
                    <p class="text-sm font-extrabold text-slate-800 truncate">{{ $course->name }}</p>
                    <p class="text-[10px] font-bold text-slate-400">{{ $course->program->name ?? '' }}</p>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <div>
                        <label class="text-[10px] font-bold text-purple-600 uppercase tracking-widest block mb-1">Classworks</label>
                        <input type="number" name="expected_classworks" min="0"
                            value="{{ $course->expected_classworks ?? '' }}"
                            placeholder="{{ $globalExpected['classwork'] }}"
                            class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/40">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-amber-600 uppercase tracking-widest block mb-1">Homeworks</label>
                        <input type="number" name="expected_homeworks" min="0"
                            value="{{ $course->expected_homeworks ?? '' }}"
                            placeholder="{{ $globalExpected['homework'] }}"
                            class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/40">
                    </div>
                    <div>
                        <label class="text-[10px] font-bold text-rose-600 uppercase tracking-widest block mb-1">Tests</label>
                        <input type="number" name="expected_tests" min="0"
                            value="{{ $course->expected_tests ?? '' }}"
                            placeholder="{{ $globalExpected['monthly_test'] }}"
                            class="w-full px-2 py-1.5 text-sm border border-slate-200 rounded-lg bg-white focus:outline-none focus:ring-2 focus:ring-indigo-400/40">
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    @if($course->expected_classworks || $course->expected_homeworks || $course->expected_tests)
                    <span class="text-[10px] font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">Custom</span>
                    @else
                    <span class="text-[10px] text-slate-400">Using global defaults</span>
                    @endif
                    <button type="submit" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold rounded-lg transition-colors">Save</button>
                </div>
            </form>
            @endforeach
        </div>
    </div>
</div>

</div>

@push('scripts')
<script>
function togglePanel(id) {
    const el   = document.getElementById(id);
    const icon = document.getElementById(id + '-icon');
    el.classList.toggle('hidden');
    if (icon) icon.style.transform = el.classList.contains('hidden') ? '' : 'rotate(180deg)';
}

// ── Work Log Detail Modal ─────────────────────────────────────────────────
function showLogDetail(url) {
    const modal = document.getElementById('logDetailModal');
    const body  = document.getElementById('logDetailBody');
    const spin  = document.getElementById('logDetailSpinner');

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    spin.classList.remove('hidden');
    body.classList.add('hidden');

    fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(d => {
            spin.classList.add('hidden');
            body.classList.remove('hidden');

            const typeColors = {
                classwork:    'bg-purple-100 text-purple-700',
                homework:     'bg-amber-100 text-amber-700',
                monthly_test: 'bg-rose-100 text-rose-700',
            };
            const tc = typeColors[d.type] || 'bg-slate-100 text-slate-600';

            body.innerHTML = `
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Teacher</p>
                        <p class="text-sm font-extrabold text-slate-800">${d.teacher}</p>
                    </div>
                    <div class="col-span-2 sm:col-span-1">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Date</p>
                        <p class="text-sm font-semibold text-slate-700">${d.date}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Subject / Course</p>
                        <p class="text-sm font-semibold text-slate-800">${d.subject}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Class / Program</p>
                        <p class="text-sm font-semibold text-slate-700">${d.program}</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Activity Type</p>
                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-bold ${tc}">${d.type_label}</span>
                    </div>
                    ${d.week_number ? `
                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">Week Number</p>
                        <p class="text-sm font-semibold text-slate-700">Week ${d.week_number}</p>
                    </div>` : '<div></div>'}
                </div>

                <div class="space-y-3">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                            <i class="fas fa-heading text-[8px] mr-1"></i>Title / Activity Name
                        </p>
                        <p class="text-sm font-semibold text-slate-800">${d.title}</p>
                    </div>

                    ${d.topic_covered ? `
                    <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-blue-400 mb-1">
                            <i class="fas fa-tag text-[8px] mr-1"></i>Topic Covered
                        </p>
                        <p class="text-sm text-blue-800">${d.topic_covered}</p>
                    </div>` : ''}

                    ${d.notes ? `
                    <div class="rounded-xl border border-slate-200 bg-white p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-1">
                            <i class="fas fa-file-alt text-[8px] mr-1"></i>Notes / Description
                        </p>
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap">${d.notes}</p>
                    </div>` : ''}

                    ${d.admin_comment ? `
                    <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-3.5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-indigo-400 mb-1">
                            <i class="fas fa-comment text-[8px] mr-1"></i>Admin Feedback
                        </p>
                        <p class="text-sm text-indigo-800">${d.admin_comment}</p>
                    </div>` : ''}
                </div>
            `;
        })
        .catch(() => {
            spin.classList.add('hidden');
            body.classList.remove('hidden');
            body.innerHTML = '<p class="text-center text-red-500 py-8">Failed to load details. Please try again.</p>';
        });
}

function closeLogDetail() {
    document.getElementById('logDetailModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLogDetail(); });
</script>
@endpush

{{-- ── Work Log Detail Modal ───────────────────────────────────────────── --}}
<div id="logDetailModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4"
     onclick="if(event.target===this)closeLogDetail()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-sm"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto z-10">
        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 sticky top-0 bg-white rounded-t-2xl">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-clipboard-list text-blue-600 text-sm"></i>
                </div>
                <h3 class="text-base font-extrabold text-slate-900">Work Log Detail</h3>
            </div>
            <button onclick="closeLogDetail()"
                class="w-8 h-8 flex items-center justify-center rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        {{-- Body --}}
        <div class="px-6 py-5">
            <div id="logDetailSpinner" class="py-12 text-center text-slate-400">
                <i class="fas fa-spinner fa-spin text-2xl"></i>
                <p class="text-sm mt-2">Loading...</p>
            </div>
            <div id="logDetailBody" class="hidden"></div>
        </div>
    </div>
</div>
@endsection
