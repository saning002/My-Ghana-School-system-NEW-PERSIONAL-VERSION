@extends('layouts.app')

@section('title', 'Enter Exam Scores')
@section('subtitle', 'Record SBA sub-scores and exam marks for your assigned courses')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header Banner --}}
    <div class="card border border-amber-200/60 bg-gradient-to-r from-amber-50 to-white p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Teacher Studio</p>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Score Entry Form</h2>
                <p class="text-xs text-slate-600 mt-0.5">Enter student Continuous Assessment (SBA Test 1, Group Work, Test 2, Project) and Final Examination marks.</p>
            </div>
            <a href="{{ route('lecturer.exams.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors shrink-0">
                <i class="fas fa-arrow-left"></i> Back to Courses
            </a>
        </div>
    </div>

    {{-- Step 1: Course / Assignment Selection --}}
    <div class="card border border-slate-200 bg-white p-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-3 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold">1</span>
            Step 1 — Select Assigned Class / Program
        </h3>
        <form method="GET" action="{{ route('lecturer.exams.create') }}">
            <div class="flex flex-col sm:flex-row gap-3">
                <select name="assignment" onchange="this.form.submit()" class="flex-1 rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all">
                    <option value="">-- Choose Assigned Course & Program --</option>
                    @foreach($assignments as $assignment)
                        <option value="{{ $assignment->course_id }}|{{ $assignment->program_id }}"
                            {{ request('assignment') == $assignment->course_id.'|'.$assignment->program_id ? 'selected' : '' }}>
                            {{ $assignment->course->code }} — {{ $assignment->program->name }} (Year {{ $assignment->year }})
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn-gold px-6 py-3 rounded-xl text-sm font-bold shadow-sm shrink-0">
                    Select Class
                </button>
            </div>
        </form>
    </div>

    {{-- Step 2: Select Student --}}
    @if($selectedAssignment || $selectedProgram)
    <div class="card border border-slate-200 bg-white p-6">
        <h3 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-3 flex items-center gap-2">
            <span class="w-6 h-6 rounded-lg bg-amber-100 text-amber-800 flex items-center justify-center font-bold">2</span>
            Step 2 — Select Student
        </h3>

        @if($students->isEmpty())
            <p class="text-xs text-slate-400 text-center py-4">No enrolled students found for this course and program.</p>
        @else
            <form method="GET" action="{{ route('lecturer.exams.create') }}" id="student-picker-form">
                @if(request('assignment'))
                    <input type="hidden" name="assignment" value="{{ request('assignment') }}">
                @endif
                @if(request('program_id'))
                    <input type="hidden" name="program_id" value="{{ request('program_id') }}">
                @endif

                <div class="space-y-3" x-data="{ search: '' }">
                    <div class="relative">
                        <i class="fas fa-search absolute left-3.5 top-3.5 text-slate-400 text-xs"></i>
                        <input type="text" x-model="search" placeholder="Type student name or ID..." class="w-full pl-9 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:bg-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    </div>

                    <div class="flex gap-3">
                        <select name="student_id" id="student_id_select" onchange="this.form.submit()" class="flex-1 rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all">
                            <option value="">-- Choose Student --</option>
                            @foreach($students as $st)
                                <option value="{{ $st->id }}" {{ request('student_id') == $st->id ? 'selected' : '' }}>
                                    {{ $st->user->full_name ?? 'Unnamed' }} ({{ $st->student_id }})
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="btn-gold px-6 py-3 rounded-xl text-sm font-bold shadow-sm shrink-0">
                            Select
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
    @endif

    {{-- Step 3: Enter Scores --}}
    @if(($selectedAssignment || $selectedProgram) && $selectedStudent)
        @php
            $isPreCollegeFinal = ($selectedProgram && $selectedProgram->sequence === 1 && $currentAttempt === 1);
            $totalSubW = $totalSubW ?? 100;
            $subCols = [
                ['key'=>'test1',     'color'=>'emerald', 'bg'=>'#dcfce7', 'label'=>$sbaSubLabels['test1'] ?? 'Test 1',     'max'=>$sbaSubWeights['test1'] ?? 25],
                ['key'=>'groupwork', 'color'=>'purple',  'bg'=>'#f3e8ff', 'label'=>$sbaSubLabels['groupwork'] ?? 'Group Work', 'max'=>$sbaSubWeights['groupwork'] ?? 25],
                ['key'=>'test2',     'color'=>'blue',    'bg'=>'#dbeafe', 'label'=>$sbaSubLabels['test2'] ?? 'Test 2',     'max'=>$sbaSubWeights['test2'] ?? 25],
                ['key'=>'project',   'color'=>'rose',    'bg'=>'#ffe4e6', 'label'=>$sbaSubLabels['project'] ?? 'Project',   'max'=>$sbaSubWeights['project'] ?? 25],
            ];
        @endphp

        {{-- Attempt Tabs --}}
        @if($attempts->isNotEmpty())
            <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
                <span class="text-xs font-bold text-slate-500 mr-2">Attempt:</span>
                @foreach($attempts as $att)
                    <a href="{{ route('lecturer.exams.create', array_merge(request()->all(), ['attempt' => $att])) }}"
                       class="px-4 py-1.5 rounded-full text-xs font-bold transition-all {{ $att == $currentAttempt ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Attempt {{ $att }}
                    </a>
                @endforeach
                @php $newAtt = ($attempts->max() ?? 0) + 1; @endphp
                <a href="{{ route('lecturer.exams.create', array_merge(request()->all(), ['attempt' => $newAtt])) }}"
                   class="ml-auto px-3 py-1 rounded-full text-xs font-bold border border-dashed border-amber-400 text-amber-700 hover:bg-amber-50">
                    + New Attempt ({{ $newAtt }})
                </a>
            </div>
        @endif

        {{-- Grid Score Form --}}
        <form method="POST" action="{{ route('lecturer.exams.store') }}">
            @csrf
            <input type="hidden" name="student_id" value="{{ $selectedStudent->id }}">
            <input type="hidden" name="program_id" value="{{ $selectedProgram ? $selectedProgram->id : $selectedStudent->program_id }}">
            <input type="hidden" name="attempt" value="{{ $currentAttempt }}">

            <div class="card border border-slate-200 bg-white overflow-hidden shadow-sm">
                <div class="p-5 border-b border-slate-100 bg-slate-50/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-base flex items-center gap-2">
                            Step 3 — Score Entry Matrix
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-xs font-bold">Attempt {{ $currentAttempt }}</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            {{ $selectedStudent->user->full_name }} ({{ $selectedStudent->student_id }}) — {{ $selectedProgram->name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-blue-50 text-blue-800 text-xs font-bold border border-blue-100">
                            SBA {{ $quizPercentage }}%
                        </span>
                        <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-800 text-xs font-bold border border-amber-100">
                            Exam {{ $examPercentage }}%
                        </span>
                    </div>
                </div>

                {{-- Matrix Score Entry Table --}}
                <div class="table-wrap overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead>
                            <tr class="bg-amber-50/80 text-xs font-bold uppercase tracking-wider text-amber-900 border-b border-slate-200">
                                <th class="px-5 py-3">Course</th>
                                @if(!$isPreCollegeFinal)
                                    @foreach($subCols as $col)
                                        <th class="px-3 py-3 text-center font-bold text-{{ $col['color'] }}-900" style="background: {{ $col['bg'] }}">
                                            {{ $col['label'] }}<br>
                                            <span class="font-normal opacity-75">/{{ $col['max'] }}</span>
                                        </th>
                                    @endforeach
                                    <th class="px-3 py-3 text-center font-bold text-blue-900 bg-blue-100/70">
                                        Class Score<br>
                                        <span class="font-normal opacity-75">({{ $quizPercentage }}%)</span>
                                    </th>
                                @endif
                                <th class="px-4 py-3 text-center font-bold text-amber-900 bg-amber-100/70">
                                    Exam Score<br>
                                    <span class="font-normal opacity-75">({{ $examPercentage }}%)</span>
                                </th>
                                <th class="px-4 py-3 text-center font-bold text-slate-700 bg-slate-100">Aggregate</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($courses as $i => $course)
                                @php $scoreRec = $existing->get($course->id); @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3">
                                        <input type="hidden" name="scores[{{ $i }}][course_id]" value="{{ $course->id }}">
                                        <p class="font-bold text-slate-900 text-sm">{{ $course->name }}</p>
                                        <p class="text-xs font-mono text-slate-500">{{ $course->code }}</p>
                                    </td>

                                    @if(!$isPreCollegeFinal)
                                        @foreach($subCols as $col)
                                            <td class="px-2 py-3 text-center" style="background: {{ $col['bg'] }}30">
                                                <input type="number" step="0.5" min="0" max="{{ $col['max'] }}"
                                                    name="scores[{{ $i }}][{{ $col['key'] }}_score]"
                                                    id="{{ $col['key'] }}_{{ $i }}"
                                                    value="{{ old('scores.'.$i.'.'.$col['key'].'_score', $scoreRec?->{$col['key'].'_score'}) }}"
                                                    oninput="calcRow({{ $i }})"
                                                    class="w-16 px-2 py-1.5 rounded-lg border border-slate-200 text-center text-xs font-bold focus:border-amber-500 focus:ring-1 focus:ring-amber-500 bg-white"
                                                    placeholder="—">
                                            </td>
                                        @endforeach

                                        {{-- Class Score Readonly Display --}}
                                        <td class="px-3 py-3 text-center bg-blue-50/40">
                                            <span id="classScore_{{ $i }}" class="font-bold text-sm text-blue-800">
                                                {{ $scoreRec ? number_format($scoreRec->getEffectiveSbaScore(), 1) : '—' }}
                                            </span>
                                            <input type="hidden" name="scores[{{ $i }}][sba_score]" id="sbaHidden_{{ $i }}" value="{{ $scoreRec?->sba_score }}">
                                        </td>
                                    @else
                                        <input type="hidden" name="scores[{{ $i }}][sba_score]" value="">
                                    @endif

                                    {{-- Exam Score Input --}}
                                    <td class="px-3 py-3 text-center bg-amber-50/40">
                                        <input type="number" step="0.5" min="0" max="100"
                                            name="scores[{{ $i }}][exam_score]"
                                            id="exam_{{ $i }}"
                                            value="{{ old('scores.'.$i.'.exam_score', $scoreRec?->exam_score) }}"
                                            oninput="calcRow({{ $i }})"
                                            class="w-20 px-2 py-1.5 rounded-lg border border-slate-200 text-center text-xs font-bold focus:border-amber-500 focus:ring-1 focus:ring-amber-500 bg-white"
                                            placeholder="—">
                                    </td>

                                    {{-- Live Aggregate --}}
                                    <td class="px-4 py-3 text-center bg-slate-50">
                                        <span id="agg_{{ $i }}" class="font-extrabold text-sm text-slate-800">—</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Action Bar --}}
                <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-500 font-medium">Be sure to save scores after entry.</span>
                    <button type="submit" class="btn-gold inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                        <i class="fas fa-save"></i> Save Scores
                    </button>
                </div>
            </div>
        </form>

        <script>
            var sbaPct = {{ $quizPercentage }};
            var examPct = {{ $examPercentage }};
            var totalSubW = {{ $totalSubW }};
            var subKeys = ['test1', 'groupwork', 'test2', 'project'];

            function calcRow(i) {
                var subSum = 0;
                subKeys.forEach(function(k) {
                    var el = document.getElementById(k + '_' + i);
                    subSum += el ? (parseFloat(el.value) || 0) : 0;
                });

                var classScore = totalSubW > 0 ? (subSum / totalSubW) * sbaPct : 0;
                var cs = document.getElementById('classScore_' + i);
                if (cs) cs.textContent = classScore.toFixed(1);

                var hid = document.getElementById('sbaHidden_' + i);
                if (hid) hid.value = classScore.toFixed(2);

                var examEl = document.getElementById('exam_' + i);
                var examRaw = examEl ? (parseFloat(examEl.value) || 0) : 0;
                var examContrib = (examRaw * (examPct / 100));

                var total = classScore + examContrib;
                var agg = document.getElementById('agg_' + i);
                if (agg) agg.textContent = total.toFixed(1) + '%';
            }

            // Run initial calculation for pre-filled scores
            document.addEventListener('DOMContentLoaded', function() {
                @foreach($courses as $i => $c)
                    calcRow({{ $i }});
                @endforeach
            });
        </script>
    @endif
</div>
@endsection
