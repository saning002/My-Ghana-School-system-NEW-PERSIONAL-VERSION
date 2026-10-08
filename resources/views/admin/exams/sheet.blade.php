@extends('layouts.app')

@section('header')
<div class="flex items-center justify-between">
    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
        <i class="fas fa-table text-blue-600"></i> Exams Sheet (Bulk Entry)
    </h2>
    <a href="{{ route('admin.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
        <i class="fas fa-arrow-left"></i> Back to Exams
    </a>
</div>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Program Selection Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('admin.exams.sheet') }}" method="GET" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:w-1/3">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select Program</label>
                <select name="program_id" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                    <option value="">-- Choose Program --</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ ($selectedProgram->id ?? null) == $prog->id ? 'selected' : '' }}>
                            {{ $prog->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full md:w-1/4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Attempt Number</label>
                <input type="number" name="attempt" value="{{ $currentAttempt ?? 1 }}" min="1" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm" required>
                @if($attempts->isNotEmpty())
                    <p class="text-xs text-gray-500 mt-2">Existing attempts: {{ $attempts->implode(', ') }}</p>
                @endif
            </div>

            <div class="w-full md:w-auto">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium text-sm shadow-sm transition-colors flex items-center gap-2">
                    <i class="fas fa-search"></i> Load Sheet
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        @if($attempts->isNotEmpty())
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-gray-800 text-lg">Available Attempts</h3>
                        <p class="text-sm text-gray-600">Load any saved attempt or download its order of merit sheet.</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    @foreach($attempts as $att)
                        <div class="rounded-xl border border-gray-200 p-4 bg-slate-50">
                            <div class="flex items-center justify-between gap-2 mb-3">
                                <span class="font-semibold text-gray-800">Attempt {{ $att }}</span>
                                @if($att == $currentAttempt)
                                    <span class="text-xs text-blue-700 bg-blue-100 px-2 py-1 rounded-full">Current</span>
                                @endif
                            </div>
                            <div class="space-y-2">
                                <a href="{{ route('admin.exams.sheet', ['program_id' => $selectedProgram->id, 'attempt' => $att]) }}" class="block w-full text-center bg-white border border-gray-300 hover:border-blue-400 text-gray-700 px-3 py-2 rounded-lg text-sm font-medium">Load</a>
                                <a href="{{ route('admin.exams.sheet.merit', ['program_id' => $selectedProgram->id, 'attempt' => $att]) }}" class="block w-full text-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded-lg text-sm font-medium">Download Merit PDF</a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="h-4"></div>
        @endif
        @if($students->count() == 0 || $courses->count() == 0)
            <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl border border-yellow-200 flex items-center gap-3">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
                <p class="text-sm font-medium">No students or courses found for this program in your branch.</p>
            </div>
        @else
            {{-- Percentage Settings Card --}}
            @php $isPreCollegeFinal = ($selectedProgram->sequence === 1 && $currentAttempt === 1); @endphp

            @if($isPreCollegeFinal)
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-start gap-3">
                <i class="fas fa-lock text-amber-500 text-lg mt-0.5"></i>
                <div>
                    <p class="text-sm font-bold text-amber-800">Pre-College Attempt 1 — Scores are final aggregates (out of 100)</p>
                    <p class="text-xs text-amber-700 mt-1">Scores for this attempt are treated as 100% exam score — no weighting applied.</p>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-sliders-h text-purple-600"></i> Score Weighting &amp; SBA Components
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Set the SBA vs Exam split (must add to 100%). Then configure each SBA sub-component weight (must add to 100%).
                </p>

                <form action="{{ route('admin.exams.sheet.percentages') }}" method="POST">
                    @csrf
                    {{-- Row 1: SBA% vs Exam% --}}
                    <div class="flex flex-wrap gap-4 items-end mb-5 pb-5 border-b border-gray-100">
                        <div>
                            <label class="block text-xs font-semibold text-blue-700 mb-1">Total SBA %</label>
                            <input type="number" name="quiz_percentage" value="{{ $quizPercentage }}"
                                   min="0" max="100" step="1" id="quiz_pct_input"
                                   oninput="syncExamPct(this.value)"
                                   class="w-28 rounded-lg border-blue-300 shadow-sm text-sm text-center font-bold" required>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-orange-700 mb-1">Exam %</label>
                            <input type="number" name="exam_percentage" value="{{ $examPercentage }}"
                                   min="0" max="100" step="1" id="exam_pct_input"
                                   oninput="syncQuizPct(this.value)"
                                   class="w-28 rounded-lg border-orange-300 shadow-sm text-sm text-center font-bold" required>
                        </div>
                        <p class="text-xs text-gray-400 self-end pb-1">
                            Currently: <strong class="text-blue-700">SBA {{ $quizPercentage }}%</strong> + <strong class="text-orange-700">Exam {{ $examPercentage }}%</strong> = 100%
                        </p>
                    </div>

                    {{-- Row 2: SBA sub-component weights --}}
                    <p class="text-xs font-bold text-gray-600 uppercase tracking-wide mb-3">
                        SBA Sub-Components — weights must add to 100
                    </p>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
                        @foreach([
                            ['test1',     'sba_test1_weight',     'sba_test1_label',     'Test 1',       'text-green-700',  'border-green-300'],
                            ['groupwork', 'sba_groupwork_weight', 'sba_groupwork_label', 'Group Work',   'text-purple-700', 'border-purple-300'],
                            ['test2',     'sba_test2_weight',     'sba_test2_label',     'Test 2',       'text-blue-700',   'border-blue-300'],
                            ['project',   'sba_project_weight',   'sba_project_label',   'Project Work', 'text-rose-700',   'border-rose-300'],
                        ] as [$key, $wField, $lField, $default, $color, $border])
                        <div class="bg-gray-50 rounded-xl p-4 border {{ $border }}">
                            <label class="block text-xs font-semibold {{ $color }} mb-1">Label</label>
                            <input type="text" name="{{ $lField }}"
                                   value="{{ $sbaSubLabels[$key] ?? $default }}"
                                   class="w-full rounded-lg border-gray-200 shadow-sm text-sm mb-2" placeholder="{{ $default }}">
                            <label class="block text-xs font-semibold {{ $color }} mb-1">Weight (%)</label>
                            <input type="number" name="{{ $wField }}"
                                   value="{{ $sbaSubWeights[$key] ?? 25 }}"
                                   min="0" max="100" step="1"
                                   class="w-full rounded-lg {{ $border }} shadow-sm text-sm text-center font-bold" required>
                        </div>
                        @endforeach
                    </div>

                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-sm flex items-center gap-2">
                        <i class="fas fa-save"></i> Save Weighting Settings
                    </button>
                </form>
            </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- Download Template Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fas fa-download text-green-600"></i> Download Excel Template</h3>
                        <p class="text-sm text-gray-600 mb-4">
                            Downloads a spreadsheet with two columns per course — SBA ({{ $quizPercentage }}%) and Exam ({{ $examPercentage }}%). Fill it offline and upload below.
                        </p>
                    </div>
                    <form action="{{ route('admin.exams.sheet.export') }}" method="GET">
                        <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                        <input type="hidden" name="attempt" value="{{ $currentAttempt }}">
                        <button type="submit" class="w-full bg-green-50 text-green-700 border border-green-200 hover:bg-green-100 px-4 py-2 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-file-excel"></i> Download Template
                        </button>
                    </form>
                    <form action="{{ route('admin.exams.sheet.merit') }}" method="GET" class="mt-3">
                        <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                        <input type="hidden" name="attempt" value="{{ $currentAttempt }}">
                        <button type="submit" class="w-full bg-blue-600 text-white border border-blue-700 hover:bg-blue-700 px-4 py-2 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2">
                            <i class="fas fa-trophy"></i> Download Order of Merit PDF
                        </button>
                    </form>
                </div>

                {{-- Upload Excel Card --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800 mb-2 flex items-center gap-2"><i class="fas fa-upload text-blue-600"></i> Upload Filled Excel Sheet</h3>
                        <p class="text-sm text-gray-600 mb-4">Upload the filled Excel template to bulk save quiz and exam scores.</p>
                    </div>
                    <form action="{{ route('admin.exams.sheet.import') }}" method="POST" enctype="multipart/form-data" class="flex gap-2">
                        @csrf
                        <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                        <input type="hidden" name="attempt" value="{{ $currentAttempt }}">
                        <input type="file" name="sheet_file" accept=".xlsx,.xls,.csv" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" required>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium text-sm transition-colors flex items-center justify-center gap-2 whitespace-nowrap">
                            <i class="fas fa-cloud-upload-alt"></i> Upload
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── SBA Download Card ─────────────────────────────── --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"
                 x-data="{ selectAll: true, selectedStudents: {{ json_encode($students->pluck('id')->values()) }} }">
                <h3 class="font-bold text-gray-800 mb-1 flex items-center gap-2">
                    <i class="fas fa-file-download text-teal-600"></i> Download SBA Scores
                </h3>
                <p class="text-sm text-gray-500 mb-4">
                    Export the recorded SBA sub-scores for this program &amp; attempt as an Excel file.
                    Choose whether to include exam scores and the final aggregate.
                </p>
                <form action="{{ route('admin.exams.sheet.download-sba') }}" method="GET"
                      class="space-y-4">
                    <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                    <input type="hidden" name="attempt"    value="{{ $currentAttempt }}">

                    {{-- Options row --}}
                    <div class="flex flex-wrap gap-5">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="include_exam" value="1"
                                   class="rounded text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-semibold text-gray-700">Include Exam Scores &amp; Aggregate</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="split_sba" value="1" checked
                                   class="rounded text-teal-600 focus:ring-teal-500">
                            <span class="text-sm font-semibold text-gray-700">Show 4 SBA Sub-Columns (Test 1, Group Work, etc.)</span>
                        </label>
                    </div>

                    {{-- Student filter --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-gray-600 uppercase tracking-wide">Filter Students</p>
                            <div class="flex gap-3">
                                <button type="button"
                                        @click="selectAll=true; selectedStudents={{ json_encode($students->pluck('id')->values()) }}"
                                        :class="selectAll ? 'bg-teal-600 text-white' : 'bg-gray-100 text-gray-600'"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors">
                                    All {{ $students->count() }} Students
                                </button>
                                <button type="button"
                                        @click="selectAll=false; selectedStudents=[]"
                                        :class="!selectAll ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600'"
                                        class="px-3 py-1 rounded-lg text-xs font-semibold transition-colors">
                                    Select Individually
                                </button>
                            </div>
                        </div>

                        {{-- Individual student checkboxes (shown when not all) --}}
                        <div x-show="!selectAll" x-cloak
                             class="max-h-48 overflow-y-auto border border-gray-200 rounded-xl p-3 bg-gray-50 grid grid-cols-2 sm:grid-cols-3 gap-2">
                            @foreach($students as $stu)
                            <label class="flex items-center gap-2 cursor-pointer hover:bg-white rounded-lg p-1.5 transition-colors">
                                <input type="checkbox"
                                       name="student_ids[]"
                                       value="{{ $stu->id }}"
                                       x-model="selectedStudents"
                                       :value="{{ $stu->id }}"
                                       class="rounded text-teal-600">
                                <span class="text-xs text-gray-700 truncate">
                                    {{ $stu->user->full_name ?? '—' }}<br>
                                    <span class="text-gray-400">{{ $stu->student_id }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>

                        {{-- Hidden inputs for all students when "All" mode --}}
                        <div x-show="selectAll" style="display:none;">
                            @foreach($students as $stu)
                            <input type="hidden" name="student_ids[]" value="{{ $stu->id }}">
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <button type="submit"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-sm flex items-center gap-2 transition-colors">
                            <i class="fas fa-file-excel"></i> Download Excel
                        </button>
                        <button type="submit" formaction="{{ route('admin.exams.sheet.download-sba-pdf') }}"
                                class="bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-lg font-bold text-sm shadow-sm flex items-center gap-2 transition-colors">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <p class="text-xs text-gray-400">
                            Excel opens in Google Sheets, Excel or Numbers. PDF is for printing.
                        </p>
                    </div>
                </form>
            </div>

            {{-- Matrix Manual Entry Card --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-4 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <i class="fas fa-edit text-orange-500"></i> Manual Grid Entry
                    </h3>
                    <span class="text-xs bg-white border border-gray-300 text-gray-600 px-3 py-1 rounded-full font-semibold">
                        {{ $students->count() }} Students &bull; {{ $courses->count() }} Courses
                    </span>
                </div>

                <form action="{{ route('admin.exams.sheet.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                    <input type="hidden" name="attempt" value="{{ $currentAttempt }}">

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm whitespace-nowrap">
                            <thead class="text-xs uppercase border-b border-gray-200">
                                <tr class="bg-gray-100 text-gray-700">
                                    <th class="px-4 py-3 font-semibold sticky left-0 bg-gray-100 z-10 border-r border-gray-200" rowspan="2">Student</th>
                                    @foreach($courses as $course)
                                        <th class="px-2 py-2 text-center border-r border-gray-200"
                                            colspan="{{ $isPreCollegeFinal ? 1 : 5 }}"
                                            title="{{ $course->name }}">
                                            <div class="truncate max-w-[320px] mx-auto">{{ $course->name }}</div>
                                            <div class="text-[10px] text-gray-500 font-normal">{{ $course->code }}</div>
                                        </th>
                                    @endforeach
                                </tr>
                                <tr>
                                    @foreach($courses as $course)
                                        @if(!$isPreCollegeFinal)
                                        {{-- 4 SBA sub-columns --}}
                                        @foreach([
                                            ['test1',     'green',  'Test 1'],
                                            ['groupwork', 'purple', 'Group Work'],
                                            ['test2',     'blue',   'Test 2'],
                                            ['project',   'rose',   'Project'],
                                        ] as [$key, $col, $def])
                                        <th class="px-1 py-2 text-center font-semibold text-{{ $col }}-700 bg-{{ $col }}-50 border-r border-gray-100 min-w-[80px] text-[10px]">
                                            {{ $sbaSubLabels[$key] ?? $def }}<br>
                                            <span class="font-normal text-{{ $col }}-400">/{{ $sbaSubWeights[$key] ?? 25 }}</span>
                                        </th>
                                        @endforeach
                                        @endif
                                        {{-- Exam column --}}
                                        <th class="px-2 py-2 text-center font-semibold text-orange-700 bg-orange-50 border-r border-gray-200 min-w-[80px] text-[10px]">
                                            Exam<br>
                                            <span class="font-normal text-orange-400">
                                                @if($isPreCollegeFinal)/100@else{{ $examPercentage }}%@endif
                                            </span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($students as $student)
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-2 font-medium text-gray-800 sticky left-0 bg-white border-r border-gray-200 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)] text-xs">
                                            {{ $student->user->full_name }}<br>
                                            <span class="text-gray-400 font-normal">{{ $student->student_id }}</span>
                                        </td>
                                        @foreach($courses as $course)
                                            @php $existing = $existingScores[$student->id][$course->id] ?? []; @endphp
                                            @if(!$isPreCollegeFinal)
                                            {{-- 4 SBA sub-score inputs --}}
                                            @foreach([
                                                ['test1_score',     'green'],
                                                ['groupwork_score', 'purple'],
                                                ['test2_score',     'blue'],
                                                ['project_score',   'rose'],
                                            ] as [$field, $col])
                                            <td class="px-1 py-1.5 text-center border-r border-gray-100 bg-{{ $col }}-50/20">
                                                <input type="number"
                                                       name="scores[{{ $student->id }}][{{ $course->id }}][{{ $field }}]"
                                                       value="{{ $existing[$field] ?? '' }}"
                                                       min="0" max="{{ $sbaSubWeights[str_replace(['_score','_score'], '', $field)] ?? 100 }}" step="0.5"
                                                       class="w-16 text-center rounded border-{{ $col }}-200 text-xs py-1 px-1 focus:border-{{ $col }}-500"
                                                       placeholder="—">
                                            </td>
                                            @endforeach
                                            @endif
                                            {{-- Exam score --}}
                                            <td class="px-1 py-1.5 text-center border-r border-gray-100 bg-orange-50/20">
                                                <input type="number"
                                                       name="scores[{{ $student->id }}][{{ $course->id }}][exam_score]"
                                                       value="{{ $existing['exam_score'] ?? '' }}"
                                                       min="0" max="100" step="0.5"
                                                       class="w-16 text-center rounded border-orange-200 text-xs py-1 px-1 focus:border-orange-500"
                                                       placeholder="—">
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="p-4 bg-gray-50 border-t border-gray-200 text-right">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-2.5 rounded-lg font-bold text-sm shadow-sm transition-all hover:shadow-md flex items-center justify-center gap-2 ml-auto">
                            <i class="fas fa-save"></i> Save All Grid Scores
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
</div>

<script>
function syncExamPct(val) {
    var v = parseFloat(val) || 0;
    var el = document.getElementById('exam_pct_input');
    if (el) el.value = Math.max(0, Math.min(100, 100 - v));
}
function syncQuizPct(val) {
    var v = parseFloat(val) || 0;
    var el = document.getElementById('quiz_pct_input');
    if (el) el.value = Math.max(0, Math.min(100, 100 - v));
}
</script>
@endsection
