@extends('layouts.app')

@section('title', 'Batch Score Entry — '.$course->name)
@section('subtitle', $program->name.' · Attempt '.$attempt)

@push('head')
<style>
    .batch-table th { white-space: nowrap; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .score-input {
        width: 72px; text-align: center;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        padding: 5px 4px; font-size: 13px; font-weight: 600;
        transition: border-color .15s, box-shadow .15s;
        background: #fff;
    }
    .score-input:focus { outline: none; border-color: #818cf8; box-shadow: 0 0 0 3px rgba(129,140,248,.18); }
    .score-input.error { border-color: #f43f5e; background: #fff1f2; }
    .grade-badge {
        display: inline-block; min-width: 36px; text-align: center;
        border-radius: 999px; padding: 2px 10px;
        font-size: 12px; font-weight: 800; letter-spacing: .03em;
    }
    .pass-badge  { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .fail-badge  { background:#fee2e2; color:#b91c1c; border:1px solid #fecaca; }
    .agg-cell    { font-weight: 800; font-size: 14px; }

    /* sticky header */
    .sticky-head th { position: sticky; top: 0; z-index: 10; background: #f8fafc; }

    /* row highlight on hover */
    .score-row:hover td { background: #f5f3ff; }

    /* gradient header bar */
    .page-header {
        background: linear-gradient(135deg, #312e81 0%, #4c1d95 40%, #7c3aed 75%, #a78bfa 100%);
        border-radius: 1.25rem;
        padding: 1.5rem 2rem;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- ── Page Header ── --}}
    <div class="page-header relative overflow-hidden">
        <div class="pointer-events-none absolute -top-10 -right-10 h-48 w-48 rounded-full bg-white/5 blur-2xl"></div>
        <div class="relative z-10 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('lecturer.dashboard') }}" class="text-white/60 hover:text-white text-xs transition-colors">Dashboard</a>
                    <span class="text-white/30 text-xs">/</span>
                    <span class="text-white/80 text-xs">Batch Score Entry</span>
                </div>
                <h1 class="text-2xl font-extrabold tracking-tight">{{ $course->name }}</h1>
                <p class="text-violet-200 text-sm mt-0.5">{{ $program->name }} &bull; {{ $course->code }} &bull; {{ $students->count() }} students</p>
            </div>

            {{-- Attempt selector --}}
            <div class="flex flex-wrap items-center gap-2.5">
                <form method="GET" class="flex items-center gap-2">
                    <label class="text-xs font-bold text-violet-200">Attempt</label>
                    <select name="attempt" onchange="this.form.submit()"
                        class="rounded-xl bg-white/15 border border-white/20 text-white text-sm font-bold px-3 py-2 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-white/30">
                        @foreach(range(1, max(3, $attempts->max() ?? 1)) as $a)
                        <option value="{{ $a }}" {{ $attempt == $a ? 'selected' : '' }}>Attempt {{ $a }}</option>
                        @endforeach
                    </select>
                </form>

                {{-- Excel template download --}}
                <a href="{{ route('lecturer.courses.scores.template', ['courseId'=>$courseId,'programId'=>$programId]) }}"
                   class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 px-4 py-2 text-sm font-bold text-white shadow transition-all active:scale-95">
                    <i class="fas fa-file-excel"></i> Excel Template
                </a>

                {{-- Auto-fill modal trigger --}}
                <button type="button" onclick="openAutoFill()"
                    class="inline-flex items-center gap-2 rounded-xl bg-amber-400 hover:bg-amber-300 px-4 py-2 text-sm font-bold text-amber-900 shadow transition-all active:scale-95">
                    <i class="fas fa-wand-magic-sparkles"></i> Quick Fill
                </button>
            </div>
        </div>
    </div>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3.5 flex items-center gap-3">
        <i class="fas fa-circle-check text-emerald-500"></i>
        <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
    </div>
    @endif
    @if($errors->any())
    <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3.5">
        <p class="text-sm font-semibold text-red-800 mb-1"><i class="fas fa-circle-exclamation mr-1"></i> Please fix the following:</p>
        <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── SBA Weight Info Bar ── --}}
    <div class="rounded-2xl bg-indigo-50 border border-indigo-100 px-5 py-3.5 flex flex-wrap gap-x-6 gap-y-2 items-center">
        <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">SBA Weights</span>
        @foreach($sbaSubLabels as $key => $label)
        <span class="text-xs text-indigo-600">
            <span class="font-bold">{{ $label }}</span>
            <span class="text-indigo-400">/ {{ $sbaSubWeights[$key] }}</span>
        </span>
        @endforeach
        <span class="text-xs text-indigo-500">Total sub-weight: <strong>{{ $totalSubW }}</strong></span>
        <span class="ml-auto text-xs text-indigo-500">Class Score: <strong>{{ $quizPct }}%</strong> &bull; Exam: <strong>{{ $examPct }}%</strong></span>
    </div>

    {{-- ── Batch Score Form ── --}}
    <form id="batchForm" method="POST"
          action="{{ route('lecturer.courses.scores.save', ['courseId'=>$courseId,'programId'=>$programId]) }}">
        @csrf
        <input type="hidden" name="attempt" value="{{ $attempt }}">

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{-- Toolbar --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50">
                <p class="text-sm font-bold text-slate-700">
                    <span id="filledCount">0</span> of {{ $students->count() }} rows filled
                </p>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-bold px-6 py-2.5 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    <i class="fas fa-floppy-disk"></i> Save All Scores
                </button>
            </div>

            {{-- Scrollable table --}}
            <div class="overflow-x-auto">
                <table class="batch-table w-full text-sm border-collapse">
                    <thead class="sticky-head">
                        <tr class="text-slate-500 border-b border-slate-100">
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Student</th>
                            <th class="px-3 py-3 text-center">{{ $sbaSubLabels['test1'] }}<br><span class="text-slate-400 font-normal normal-case text-[10px]">/{{ $sbaSubWeights['test1'] }}</span></th>
                            <th class="px-3 py-3 text-center">{{ $sbaSubLabels['groupwork'] }}<br><span class="text-slate-400 font-normal normal-case text-[10px]">/{{ $sbaSubWeights['groupwork'] }}</span></th>
                            <th class="px-3 py-3 text-center">{{ $sbaSubLabels['test2'] }}<br><span class="text-slate-400 font-normal normal-case text-[10px]">/{{ $sbaSubWeights['test2'] }}</span></th>
                            <th class="px-3 py-3 text-center">{{ $sbaSubLabels['project'] }}<br><span class="text-slate-400 font-normal normal-case text-[10px]">/{{ $sbaSubWeights['project'] }}</span></th>
                            <th class="px-3 py-3 text-center text-blue-600">Class Score<br><span class="text-slate-400 font-normal normal-case text-[10px]">/{{ $quizPct }}</span></th>
                            <th class="px-3 py-3 text-center text-purple-600">Exam Score<br><span class="text-slate-400 font-normal normal-case text-[10px]">/{{ $examPct }}</span></th>
                            <th class="px-3 py-3 text-center text-emerald-600">Aggregate<br><span class="text-slate-400 font-normal normal-case text-[10px]">/100</span></th>
                            <th class="px-3 py-3 text-center">Grade</th>
                            <th class="px-3 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $i => $student)
                        @php
                            $sc = $existing->get($student->id);
                        @endphp
                        <tr class="score-row border-b border-slate-50 transition-colors" data-row="{{ $i }}">
                            {{-- Hidden student_id --}}
                            <input type="hidden" name="scores[{{ $i }}][student_id]" value="{{ $student->id }}">

                            {{-- # --}}
                            <td class="px-4 py-3 text-slate-400 text-xs font-mono">{{ $i+1 }}</td>

                            {{-- Student name + ID --}}
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-800 text-sm leading-tight">{{ $student->user->full_name ?? '—' }}</p>
                                <p class="text-[11px] text-slate-400 font-mono">{{ $student->student_id }}</p>
                            </td>

                            {{-- Test 1 --}}
                            <td class="px-3 py-2 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $sbaSubWeights['test1'] }}"
                                    name="scores[{{ $i }}][test1]"
                                    value="{{ $sc?->test1_score !== null ? $sc->test1_score+0 : '' }}"
                                    class="score-input sub-score" data-row="{{ $i }}" data-col="test1"
                                    data-max="{{ $sbaSubWeights['test1'] }}"
                                    placeholder="—">
                            </td>

                            {{-- Group Work --}}
                            <td class="px-3 py-2 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $sbaSubWeights['groupwork'] }}"
                                    name="scores[{{ $i }}][groupwork]"
                                    value="{{ $sc?->groupwork_score !== null ? $sc->groupwork_score+0 : '' }}"
                                    class="score-input sub-score" data-row="{{ $i }}" data-col="groupwork"
                                    data-max="{{ $sbaSubWeights['groupwork'] }}"
                                    placeholder="—">
                            </td>

                            {{-- Test 2 --}}
                            <td class="px-3 py-2 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $sbaSubWeights['test2'] }}"
                                    name="scores[{{ $i }}][test2]"
                                    value="{{ $sc?->test2_score !== null ? $sc->test2_score+0 : '' }}"
                                    class="score-input sub-score" data-row="{{ $i }}" data-col="test2"
                                    data-max="{{ $sbaSubWeights['test2'] }}"
                                    placeholder="—">
                            </td>

                            {{-- Project --}}
                            <td class="px-3 py-2 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $sbaSubWeights['project'] }}"
                                    name="scores[{{ $i }}][project]"
                                    value="{{ $sc?->project_score !== null ? $sc->project_score+0 : '' }}"
                                    class="score-input sub-score" data-row="{{ $i }}" data-col="project"
                                    data-max="{{ $sbaSubWeights['project'] }}"
                                    placeholder="—">
                            </td>

                            {{-- Class Score (computed or manual) --}}
                            <td class="px-3 py-2 text-center">
                                <input type="number" step="0.01" min="0" max="{{ $totalSubW }}"
                                    name="scores[{{ $i }}][sba]"
                                    value="{{ $sc?->sba_score !== null ? $sc->sba_score+0 : '' }}"
                                    class="score-input sba-input" data-row="{{ $i }}"
                                    placeholder="auto"
                                    style="background:#eff6ff; border-color:#bfdbfe;">
                            </td>

                            {{-- Exam Score --}}
                            <td class="px-3 py-2 text-center">
                                <input type="number" step="0.01" min="0" max="100"
                                    name="scores[{{ $i }}][exam]"
                                    value="{{ $sc?->exam_score !== null ? $sc->exam_score+0 : '' }}"
                                    class="score-input exam-input" data-row="{{ $i }}"
                                    placeholder="—"
                                    style="background:#faf5ff; border-color:#ddd6fe;">
                            </td>

                            {{-- Aggregate (live computed) --}}
                            <td class="px-3 py-3 text-center agg-cell">
                                <span id="agg-{{ $i }}" class="text-slate-400">—</span>
                            </td>

                            {{-- Grade --}}
                            <td class="px-3 py-3 text-center">
                                <span id="grade-{{ $i }}" class="grade-badge bg-slate-100 text-slate-400">—</span>
                            </td>

                            {{-- Pass/Fail --}}
                            <td class="px-3 py-3 text-center">
                                <span id="status-{{ $i }}" class="grade-badge bg-slate-100 text-slate-400">—</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-16 text-slate-400">
                                <div class="flex flex-col items-center gap-3">
                                    <i class="fas fa-users-slash text-3xl text-slate-200"></i>
                                    <p class="text-sm font-medium">No students enrolled in this course yet.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Bottom save bar --}}
            @if($students->isNotEmpty())
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3 flex-wrap">
                <p class="text-xs text-slate-400">Changes are saved only when you click <strong class="text-slate-600">Save All Scores</strong>.</p>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-bold px-8 py-3 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    <i class="fas fa-floppy-disk"></i> Save All Scores
                </button>
            </div>
            @endif
        </div>
    </form>
</div>

{{-- ── Auto-Fill Modal ── --}}
<div id="autoFillModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-extrabold text-slate-900"><i class="fas fa-wand-magic-sparkles text-amber-500 mr-2"></i>Quick Fill</h3>
            <button onclick="closeAutoFill()" class="text-slate-400 hover:text-slate-700 transition-colors"><i class="fas fa-xmark text-lg"></i></button>
        </div>

        <div class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Fill column</label>
                <select id="fillCol" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-violet-300">
                    <option value="test1">{{ $sbaSubLabels['test1'] }} (all rows)</option>
                    <option value="groupwork">{{ $sbaSubLabels['groupwork'] }} (all rows)</option>
                    <option value="test2">{{ $sbaSubLabels['test2'] }} (all rows)</option>
                    <option value="project">{{ $sbaSubLabels['project'] }} (all rows)</option>
                    <option value="exam">Exam Score (all rows)</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1.5">Value to fill</label>
                <input type="number" id="fillValue" min="0" max="100" step="0.01" placeholder="e.g. 0  or  15"
                    class="w-full rounded-xl border border-slate-200 px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-violet-300">
            </div>
            <p class="text-[11px] text-slate-400">Only fills empty cells — existing values are preserved.</p>
            <div class="flex gap-3 pt-1">
                <button onclick="applyAutoFill(false)"
                    class="flex-1 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-sm font-bold py-2.5 transition-colors">
                    Fill Empty Only
                </button>
                <button onclick="applyAutoFill(true)"
                    class="flex-1 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 text-sm font-bold py-2.5 transition-colors">
                    Fill All Rows
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    // ── Config from PHP ──────────────────────────────────────────────────────
    const SBA_WEIGHTS  = @json($sbaSubWeights);   // {test1:25, groupwork:25, test2:25, project:25}
    const TOTAL_SUB_W  = {{ $totalSubW }};
    const QUIZ_PCT     = {{ $quizPct }};   // e.g. 50
    const EXAM_PCT     = {{ $examPct }};   // e.g. 50
    const PASS_SCORE   = 50;

    // ── Grade assignment ─────────────────────────────────────────────────────
    function assignGrade(score) {
        if (score === null || isNaN(score)) return { grade: '—', color: 'bg-slate-100 text-slate-400' };
        if (score >= 80) return { grade: 'A1', color: 'bg-emerald-100 text-emerald-800' };
        if (score >= 75) return { grade: 'A2', color: 'bg-emerald-100 text-emerald-700' };
        if (score >= 70) return { grade: 'A3', color: 'bg-teal-100 text-teal-800' };
        if (score >= 65) return { grade: 'B1', color: 'bg-blue-100 text-blue-800' };
        if (score >= 60) return { grade: 'B2', color: 'bg-blue-100 text-blue-700' };
        if (score >= 55) return { grade: 'B3', color: 'bg-indigo-100 text-indigo-800' };
        if (score >= 50) return { grade: 'C',  color: 'bg-amber-100 text-amber-800' };
        return { grade: 'F', color: 'bg-red-100 text-red-800' };
    }

    // ── Recalculate a single row ─────────────────────────────────────────────
    function calcRow(rowIdx) {
        const get = (sel) => {
            const el = document.querySelector(`input[data-row="${rowIdx}"][data-col="${sel}"]`);
            return el && el.value !== '' ? parseFloat(el.value) : null;
        };

        const t1 = get('test1'), gw = get('groupwork'), t2 = get('test2'), pw = get('project');
        const hasSub = (t1 !== null || gw !== null || t2 !== null || pw !== null);

        // Validate sub-score ranges — highlight red if over max
        ['test1','groupwork','test2','project'].forEach(k => {
            const el = document.querySelector(`input[data-row="${rowIdx}"][data-col="${k}"]`);
            if (!el) return;
            const max = parseFloat(el.dataset.max || 100);
            const v   = el.value !== '' ? parseFloat(el.value) : null;
            el.classList.toggle('error', v !== null && v > max);
        });

        // Compute SBA sum
        let sbaRaw = null;
        const sbaInput = document.querySelector(`.sba-input[data-row="${rowIdx}"]`);
        if (hasSub) {
            sbaRaw = (t1 ?? 0) + (gw ?? 0) + (t2 ?? 0) + (pw ?? 0);
            if (sbaInput) { sbaInput.value = sbaRaw.toFixed(2); }
        } else {
            sbaRaw = sbaInput && sbaInput.value !== '' ? parseFloat(sbaInput.value) : null;
        }

        // Exam
        const examInput = document.querySelector(`.exam-input[data-row="${rowIdx}"]`);
        const examRaw   = examInput && examInput.value !== '' ? parseFloat(examInput.value) : null;

        // Aggregate: (sbaRaw / totalSubW) × quizPct + (examRaw / 100) × examPct
        let agg = null;
        if (sbaRaw !== null && examRaw !== null) {
            const classPart = (sbaRaw / TOTAL_SUB_W) * QUIZ_PCT;
            const examPart  = (examRaw / 100)        * EXAM_PCT;
            agg = classPart + examPart;
            agg = Math.min(100, Math.max(0, agg));
        } else if (sbaRaw !== null) {
            agg = (sbaRaw / TOTAL_SUB_W) * QUIZ_PCT;
        } else if (examRaw !== null) {
            agg = (examRaw / 100) * EXAM_PCT;
        }

        // Update cells
        const aggEl    = document.getElementById(`agg-${rowIdx}`);
        const gradeEl  = document.getElementById(`grade-${rowIdx}`);
        const statusEl = document.getElementById(`status-${rowIdx}`);

        if (agg !== null) {
            const g = assignGrade(agg);
            const pass = agg >= PASS_SCORE;

            aggEl.textContent = agg.toFixed(1) + '%';
            aggEl.className   = 'font-extrabold text-sm ' + (pass ? 'text-emerald-700' : 'text-red-600');

            gradeEl.textContent = g.grade;
            gradeEl.className   = `grade-badge ${g.color}`;

            statusEl.textContent = pass ? 'Pass' : 'Fail';
            statusEl.className   = `grade-badge ${pass ? 'pass-badge' : 'fail-badge'}`;
        } else {
            aggEl.textContent    = '—';
            aggEl.className      = 'text-slate-300';
            gradeEl.textContent  = '—';
            gradeEl.className    = 'grade-badge bg-slate-100 text-slate-400';
            statusEl.textContent = '—';
            statusEl.className   = 'grade-badge bg-slate-100 text-slate-400';
        }

        updateFilledCount();
    }

    // ── Count filled rows ────────────────────────────────────────────────────
    function updateFilledCount() {
        let count = 0;
        document.querySelectorAll('.score-row').forEach((row, i) => {
            const hasSba  = row.querySelector('.sba-input')?.value  !== '';
            const hasExam = row.querySelector('.exam-input')?.value !== '';
            const hasSub  = [...row.querySelectorAll('.sub-score')].some(el => el.value !== '');
            if (hasSba || hasExam || hasSub) count++;
        });
        const el = document.getElementById('filledCount');
        if (el) el.textContent = count;
    }

    // ── Attach live listeners ────────────────────────────────────────────────
    document.querySelectorAll('.score-row').forEach((row, i) => {
        row.querySelectorAll('input[type="number"]').forEach(inp => {
            inp.addEventListener('input', () => calcRow(i));
        });
        // Initial calc for pre-filled rows
        calcRow(i);
    });

    // ── Tab through inputs ───────────────────────────────────────────────────
    document.querySelectorAll('.score-input').forEach((inp, idx, all) => {
        inp.addEventListener('keydown', e => {
            if (e.key === 'Enter' || e.key === 'Tab' && !e.shiftKey) {
                e.preventDefault();
                const next = all[idx + 1];
                if (next) next.focus();
            }
        });
    });

    // ── Auto-fill modal ──────────────────────────────────────────────────────
    window.openAutoFill  = () => {
        document.getElementById('autoFillModal').classList.remove('hidden');
        document.getElementById('autoFillModal').classList.add('flex');
    };
    window.closeAutoFill = () => {
        document.getElementById('autoFillModal').classList.add('hidden');
        document.getElementById('autoFillModal').classList.remove('flex');
    };

    window.applyAutoFill = (overwrite) => {
        const col = document.getElementById('fillCol').value;
        const val = document.getElementById('fillValue').value;
        if (val === '') return;

        let selector;
        if (col === 'exam') {
            selector = '.exam-input';
        } else {
            selector = `input[data-col="${col}"]`;
        }

        document.querySelectorAll(selector).forEach((inp, idx) => {
            if (overwrite || inp.value === '') {
                inp.value = val;
            }
        });

        // Recalc all rows
        document.querySelectorAll('.score-row').forEach((_, i) => calcRow(i));
        closeAutoFill();
    };

    // Close modal on backdrop click
    document.getElementById('autoFillModal').addEventListener('click', function(e) {
        if (e.target === this) closeAutoFill();
    });
})();
</script>
@endpush
