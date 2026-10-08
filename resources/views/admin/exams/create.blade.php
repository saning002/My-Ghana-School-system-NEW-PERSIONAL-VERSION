@extends('layouts.app')
@section('title','Enter Exam Scores')
@section('subtitle','Record class scores and exam scores per student')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- ── Step 1: Select Program ── --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h2 class="text-base font-bold" style="color:#78520a">Step 1 — Select Program</h2>
        </div>
        <div class="px-5 py-5">
            <form method="GET" action="{{ route('admin.exams.create') }}">
                <label class="block text-xs font-semibold text-gray-600 mb-1.5">Program *</label>
                <div class="flex gap-3">
                    <select name="program_id" required
                        class="flex-1 px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 bg-white">
                        <option value="">Select program…</option>
                        @foreach($programs as $prog)
                            <option value="{{ $prog->id }}"
                                {{ $selectedProgram && $selectedProgram->id == $prog->id ? 'selected' : '' }}>
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-gold px-5 py-3 rounded-xl text-sm font-semibold">
                        Select
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ── Step 2: Select Student ── --}}
    @if($selectedProgram)
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h2 class="text-base font-bold" style="color:#78520a">
                Step 2 — Select Student
                <span class="font-normal text-xs ml-2 opacity-70">{{ $selectedProgram->name }}</span>
            </h2>
        </div>
        <div class="px-5 py-5">
            @if($students->isEmpty())
                <p class="text-sm text-gray-400 text-center py-4">No students enrolled in this program yet.</p>
            @else
                <form method="GET" action="{{ route('admin.exams.create') }}" id="student-form">
                    <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">

                    {{-- Search box --}}
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Search by name or ID</label>
                    <input type="text" id="student-search" placeholder="Type name or student ID…"
                        class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 bg-white mb-3"
                        autocomplete="off">

                    {{-- Student select --}}
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Student *</label>
                    <div class="flex gap-3">
                        <select name="student_id" id="student-select" required
                            class="flex-1 px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 bg-white">
                            <option value="">Select student…</option>
                            @foreach($students as $stu)
                                <option value="{{ $stu->id }}"
                                    data-name="{{ strtolower($stu->user?->full_name ?? '') }}"
                                    data-sid="{{ strtolower($stu->student_id) }}"
                                    {{ $selectedStudent && $selectedStudent->id == $stu->id ? 'selected' : '' }}>
                                    {{ $stu->user?->full_name ?? '—' }} ({{ $stu->student_id }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-gold px-5 py-3 rounded-xl text-sm font-semibold">
                            Select
                        </button>
                    </div>

                    {{-- Live match list shown while typing --}}
                    <div id="search-results" class="mt-3 hidden"></div>
                </form>

                <script>
                (function () {
                    const searchInput  = document.getElementById('student-search');
                    const selectEl     = document.getElementById('student-select');
                    const resultsBox   = document.getElementById('search-results');
                    const allOptions   = Array.from(selectEl.querySelectorAll('option[value]')).filter(o => o.value !== '');

                    searchInput.addEventListener('input', function () {
                        const q = this.value.trim().toLowerCase();

                        if (!q) {
                            // Reset — show all options, hide results box
                            allOptions.forEach(o => o.hidden = false);
                            resultsBox.innerHTML = '';
                            resultsBox.classList.add('hidden');
                            return;
                        }

                        const matches = allOptions.filter(o =>
                            o.dataset.name.includes(q) || o.dataset.sid.includes(q)
                        );

                        // Hide non-matching options in the select
                        allOptions.forEach(o => { o.hidden = !matches.includes(o); });

                        // Show clickable cards below
                        if (matches.length === 0) {
                            resultsBox.innerHTML = '<p class="text-xs text-gray-400 py-2">No students match your search.</p>';
                            resultsBox.classList.remove('hidden');
                            return;
                        }

                        resultsBox.innerHTML = matches.map(o => `
                            <div class="flex items-center justify-between px-4 py-2.5 rounded-xl border border-yellow-100 bg-yellow-50/50 mb-2 cursor-pointer hover:bg-yellow-100 transition-colors"
                                 data-val="${o.value}" onclick="pickStudent(this)">
                                <span class="text-sm font-semibold text-gray-800">${o.textContent.trim()}</span>
                                <span class="text-xs text-amber-700 font-mono">${o.dataset.sid.toUpperCase()}</span>
                            </div>
                        `).join('');
                        resultsBox.classList.remove('hidden');
                    });

                    window.pickStudent = function (el) {
                        const val = el.dataset.val;
                        selectEl.value = val;
                        searchInput.value = selectEl.querySelector(`option[value="${val}"]`).textContent.trim();
                        resultsBox.innerHTML = '';
                        resultsBox.classList.add('hidden');
                        allOptions.forEach(o => { o.hidden = false; });
                        // Auto-submit the form
                        document.getElementById('student-form').submit();
                    };
                })();
                </script>
            @endif
        </div>
    </div>
    @endif

    {{-- ── Step 3: Enter Scores ── --}}
    @if($selectedProgram && $selectedStudent)
        @if($courses->isEmpty())
            <div class="card p-8 text-center">
                <i class="fas fa-book-open text-gray-200 text-4xl mb-3 block"></i>
                <p class="text-gray-400 text-sm">No courses found for <strong>{{ $selectedProgram->name }}</strong>. Add courses first.</p>
            </div>
        @else
            {{-- Attempt selector --}}
            @if($attempts->count() > 0)
            <div class="card overflow-hidden mb-4">
                <div class="px-5 py-4 flex flex-wrap items-center gap-3" style="background:#fef9c3">
                    <span class="text-xs font-semibold text-gray-600">Attempt:</span>
                    @foreach($attempts as $att)
                        <a href="{{ route('admin.exams.create', ['program_id' => $selectedProgram->id, 'student_id' => $selectedStudent->id, 'attempt' => $att]) }}"
                           class="px-4 py-1.5 rounded-full text-xs font-bold border transition-colors
                                  {{ $att == $currentAttempt ? 'border-amber-500 text-amber-800' : 'border-yellow-200 text-gray-500 bg-white hover:bg-yellow-50' }}"
                           style="{{ $att == $currentAttempt ? 'background:#fde68a' : '' }}">
                            Attempt {{ $att }}
                        </a>
                    @endforeach
                    {{-- New attempt button --}}
                    @php $newAttempt = ($attempts->max() ?? 0) + 1; @endphp
                    <a href="{{ route('admin.exams.create', ['program_id' => $selectedProgram->id, 'student_id' => $selectedStudent->id, 'attempt' => $newAttempt]) }}"
                       class="ml-auto px-4 py-1.5 rounded-full text-xs font-bold border border-dashed border-amber-400 text-amber-700 bg-white hover:bg-yellow-50 transition-colors">
                        <i class="fas fa-plus mr-1"></i> New Attempt ({{ $newAttempt }})
                    </a>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.exams.store') }}">
                @csrf
                <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
                <input type="hidden" name="attempt"    value="{{ $currentAttempt }}">

                <div class="card overflow-hidden mb-5">
                    <div class="px-5 py-4 border-b border-yellow-50 flex items-center justify-between" style="background:#fef9c3">
                        <div>
                            <h3 class="text-sm font-bold" style="color:#78520a">
                                Step 3 — Enter Scores
                                <span class="ml-2 px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fde68a;color:#78520a">
                                    Attempt {{ $currentAttempt }}
                                </span>
                            </h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $selectedStudent->user?->full_name ?? '—' }} &mdash; {{ $selectedProgram->name }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400">{{ $courses->count() }} course(s)</span>
                            @php $isPreCollegeFinal = ($selectedProgram->sequence === 1 && $currentAttempt === 1); @endphp
                            @if(!$isPreCollegeFinal)
                            <div class="flex gap-2 mt-1 justify-end">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#dbeafe;color:#1e40af">
                                    SBA {{ $quizPercentage }}%
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold" style="background:#fed7aa;color:#9a3412">
                                    Exam {{ $examPercentage }}%
                                </span>
                            </div>
                            @else
                            <div class="mt-1">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-800">
                                    <i class="fas fa-lock mr-1"></i>Final score /100
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($isPreCollegeFinal)
                    <div class="px-5 py-3 bg-amber-50 border-b border-amber-100 text-xs text-amber-700 flex items-center gap-2">
                        <i class="fas fa-info-circle"></i>
                        Pre-College attempt 1 scores are final marks out of 100. Enter only the Score column — quiz weighting does not apply.
                    </div>
                    @endif

                    <div class="table-wrap">
                        <table class="w-full">
                            <thead>
                                <tr style="background:#fef9c3">
                                    <th class="text-left px-5 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Course</th>
                                    @if(!$isPreCollegeFinal)
                                    {{-- 4 SBA sub-columns --}}
                                    @php
                                        $sbaLabels  = \App\Services\ReportCardService::getSbaSubLabels();
                                        $sbaWeights = \App\Services\ReportCardService::getSbaSubWeights();
                                        $totalSubW  = array_sum($sbaWeights);
                                        $subCols = [
                                            ['key'=>'test1',     'color'=>'#166534','bg'=>'#dcfce7','label'=>$sbaLabels['test1'],     'max'=>$sbaWeights['test1']],
                                            ['key'=>'groupwork', 'color'=>'#581c87','bg'=>'#f3e8ff','label'=>$sbaLabels['groupwork'], 'max'=>$sbaWeights['groupwork']],
                                            ['key'=>'test2',     'color'=>'#1e40af','bg'=>'#dbeafe','label'=>$sbaLabels['test2'],     'max'=>$sbaWeights['test2']],
                                            ['key'=>'project',   'color'=>'#9f1239','bg'=>'#ffe4e6','label'=>$sbaLabels['project'],   'max'=>$sbaWeights['project']],
                                        ];
                                    @endphp
                                    @foreach($subCols as $col)
                                    <th class="text-center px-2 py-3 text-xs font-semibold uppercase tracking-wider"
                                        style="color:{{ $col['color'] }};background:{{ $col['bg'] }}">
                                        {{ $col['label'] }}<br>
                                        <span class="font-normal normal-case opacity-75">/{{ $col['max'] }}</span>
                                    </th>
                                    @endforeach
                                    <th class="text-center px-2 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#1e40af;background:#eff6ff">
                                        Class Score<br><span class="font-normal normal-case opacity-75">({{ $quizPercentage }}%)</span>
                                    </th>
                                    @endif
                                    <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider" style="color:#9a3412;background:#fed7aa">
                                        @if($isPreCollegeFinal)Score (/100)
                                        @else Exam Score<br><span class="font-normal normal-case opacity-75">({{ $examPercentage }}%)</span>
                                        @endif
                                    </th>
                                    <th class="text-center px-3 py-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Aggregate</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-yellow-50">
                                @foreach($courses as $i => $course)
                                    @php $score = $existing->get($course->id); @endphp
                                    <tr class="hover:bg-yellow-50/30 transition-colors">
                                        <td class="px-5 py-3">
                                            <input type="hidden" name="scores[{{ $i }}][course_id]" value="{{ $course->id }}">
                                            <p class="text-sm font-semibold text-gray-800">{{ $course->name }}</p>
                                            <p class="text-xs text-gray-400 font-mono">{{ $course->code }}</p>
                                        </td>
                                        @if(!$isPreCollegeFinal)
                                        {{-- 4 SBA sub-score inputs --}}
                                        @foreach($subCols as $col)
                                        <td class="px-2 py-3 text-center" style="background:{{ $col['bg'] }}20">
                                            <input type="number"
                                                name="scores[{{ $i }}][{{ $col['key'] }}_score]"
                                                id="{{ $col['key'] }}_{{ $i }}"
                                                value="{{ old('scores.'.$i.'.'.$col['key'].'_score', $score?->{$col['key'].'_score'}) }}"
                                                min="0" max="{{ $col['max'] }}" step="0.5"
                                                oninput="calcRow({{ $i }})"
                                                class="w-16 px-1 py-2 border rounded-lg text-xs text-center focus:outline-none focus:ring-1 bg-white"
                                                style="border-color:{{ $col['color'] }}40"
                                                placeholder="—">
                                        </td>
                                        @endforeach
                                        {{-- Computed class score (read-only) --}}
                                        <td class="px-2 py-3 text-center" style="background:#eff6ff">
                                            <span id="classScore_{{ $i }}" class="font-bold text-sm text-blue-700">—</span>
                                            {{-- Hidden field to submit the class score as sba_score --}}
                                            <input type="hidden" name="scores[{{ $i }}][sba_score]" id="sbaHidden_{{ $i }}" value="">
                                        </td>
                                        @else
                                            <input type="hidden" name="scores[{{ $i }}][sba_score]" value="">
                                        @endif
                                        {{-- Exam score --}}
                                        <td class="px-3 py-3" style="background:#fff7ed">
                                            <input type="number"
                                                name="scores[{{ $i }}][exam_score]"
                                                id="exam_{{ $i }}"
                                                value="{{ old('scores.'.$i.'.exam_score', $score?->exam_score) }}"
                                                min="0" max="100" step="0.5"
                                                oninput="calcRow({{ $i }})"
                                                class="w-full px-2 py-2 border border-orange-200 rounded-lg text-sm text-center focus:outline-none focus:ring-2 focus:ring-orange-300 bg-white"
                                                placeholder="—">
                                        </td>
                                        {{-- Live aggregate --}}
                                        <td class="px-3 py-3 text-center">
                                            <span id="agg_{{ $i }}" class="font-bold text-sm text-gray-700">—</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="px-5 py-4 border-t border-yellow-50 flex gap-3" style="background:#fef9c3">
                        <button type="submit" class="btn-gold px-6 py-3 rounded-xl text-sm font-semibold shadow-sm">
                            <i class="fas fa-save mr-2"></i> Save Scores
                        </button>
                        @if($attempts->count() > 0)
                        <a href="{{ route('admin.exams.show', [$selectedStudent->id, $selectedProgram->id]) }}?attempt={{ $currentAttempt }}"
                           class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-semibold border border-yellow-200">
                            View Report Card
                        </a>
                        @endif
                        <a href="{{ route('admin.exams.index') }}"
                           class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-semibold border border-yellow-200">
                            Cancel
                        </a>
                    </div>
                </div>
            </form>

            <script>
            var sbaPct      = {{ $quizPercentage }};
            var examPct     = {{ $examPercentage }};
            var totalSubW   = {{ $totalSubW ?? \App\Services\ReportCardService::getTotalSubWeight() }};
            var subKeys     = ['test1','groupwork','test2','project'];

            function calcRow(i) {
                // Sum the 4 sub-scores
                var subSum = 0;
                subKeys.forEach(function(k) {
                    var el = document.getElementById(k + '_' + i);
                    subSum += el ? (parseFloat(el.value) || 0) : 0;
                });

                // Class Score = (subSum / totalSubWeight) × sbaPct
                var classScore = totalSubW > 0 ? (subSum / totalSubW) * sbaPct : 0;

                // Update the displayed class score
                var cs = document.getElementById('classScore_' + i);
                if (cs) cs.textContent = classScore.toFixed(2);

                // Update hidden field so it gets submitted
                var hid = document.getElementById('sbaHidden_' + i);
                if (hid) hid.value = classScore.toFixed(2);

                // Aggregate = classScore + examScore × (examPct/100)
                var exam = parseFloat(document.getElementById('exam_' + i)?.value) || 0;
                var agg  = classScore + (exam * examPct / 100);
                var el   = document.getElementById('agg_' + i);
                if (el) el.textContent = agg.toFixed(2);
            }

            document.addEventListener('DOMContentLoaded', function () {
                @foreach($courses as $i => $course)
                    calcRow({{ $i }});
                @endforeach
            });
            </script>
        @endif
    @endif

</div>
@endsection
