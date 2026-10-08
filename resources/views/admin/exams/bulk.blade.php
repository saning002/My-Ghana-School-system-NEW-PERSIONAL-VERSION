@extends('layouts.app')
@section('title', 'Bulk Report Cards')
@section('subtitle', 'Download or print report cards for multiple students at once')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ── Header ── --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-file-pdf text-red-500"></i> Bulk Report Cards
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">Select a program and attempt, pick students, then download or print.</p>
        </div>
        <a href="{{ route('admin.exams.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Exams
        </a>
    </div>

    {{-- ── Step 1: Program + Attempt picker ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-yellow-400 text-white text-xs font-bold flex items-center justify-center">1</span>
            Select Program &amp; Attempt
        </h3>
        <form method="GET" action="{{ route('admin.exams.bulk') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
                <select name="program_id" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-400 focus:ring-yellow-400 text-sm">
                    <option value="">— Choose program —</option>
                    @foreach($programs as $prog)
                        <option value="{{ $prog->id }}" {{ ($selectedProgram?->id == $prog->id) ? 'selected' : '' }}>
                            {{ $prog->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if($selectedProgram && $attempts->isNotEmpty())
            <div class="w-48">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Attempt</label>
                <select name="attempt"
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-yellow-400 focus:ring-yellow-400 text-sm">
                    @foreach($attempts as $att)
                        <option value="{{ $att }}" {{ $att == $currentAttempt ? 'selected' : '' }}>
                            Attempt {{ $att }}
                        </option>
                    @endforeach
                </select>
            </div>
            @elseif($selectedProgram)
            <input type="hidden" name="attempt" value="1">
            @endif

            <button type="submit"
                class="px-5 py-2.5 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-semibold shadow-sm transition-colors flex items-center gap-2">
                <i class="fas fa-search"></i> Load Students
            </button>
        </form>

        @if($selectedProgram && $attempts->isEmpty())
            <p class="mt-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No exam scores have been entered for <strong>{{ $selectedProgram->name }}</strong> yet.
            </p>
        @endif
    </div>

    @if($selectedProgram && $students->isNotEmpty())
    {{-- ── Step 2: Student selector ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-1 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-yellow-400 text-white text-xs font-bold flex items-center justify-center">2</span>
            Select Students
            <span class="ml-auto text-xs text-gray-500 font-normal">
                <strong class="text-gray-700" id="selected-count">0</strong> of {{ $students->count() }} selected
            </span>
        </h3>
        <p class="text-xs text-gray-500 mb-4 ml-8">
            Showing students with scores for <strong>{{ $selectedProgram->name }}</strong> — Attempt {{ $currentAttempt }}
        </p>

        {{-- Select all / deselect all --}}
        <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100">
            <button type="button" onclick="selectAll()" id="btn-select-all"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors">
                <i class="fas fa-check-double mr-1"></i> Select All
            </button>
            <button type="button" onclick="deselectAll()"
                class="text-xs font-semibold px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 hover:bg-gray-200 transition-colors">
                <i class="fas fa-times mr-1"></i> Deselect All
            </button>
            {{-- Search --}}
            <input type="text" id="student-filter" placeholder="Filter by name or ID…"
                oninput="filterStudents(this.value)"
                class="ml-auto w-52 px-3 py-1.5 text-xs rounded-lg border border-gray-200 focus:outline-none focus:ring-1 focus:ring-yellow-300 bg-white">
        </div>

        {{-- Student checkboxes --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-96 overflow-y-auto pr-1" id="student-list">
            @foreach($students as $student)
            <label class="student-row flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-yellow-300 hover:bg-yellow-50/40 cursor-pointer transition-colors"
                   data-name="{{ strtolower($student->user->full_name ?? '') }}"
                   data-sid="{{ strtolower($student->student_id) }}">
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                    class="student-checkbox w-4 h-4 rounded text-yellow-500 border-gray-300 focus:ring-yellow-400"
                    onchange="updateCount()">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $student->user->full_name ?? '—' }}</p>
                    <p class="text-xs text-gray-500 font-mono">{{ $student->student_id }}</p>
                </div>
                <i class="fas fa-check text-yellow-500 text-xs opacity-0 check-icon"></i>
            </label>
            @endforeach
        </div>
    </div>

    {{-- ── Step 3: Actions ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h3 class="font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-yellow-400 text-white text-xs font-bold flex items-center justify-center">3</span>
            Download or Print
        </h3>

        <div id="no-selection-warning" class="mb-4 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg px-4 py-2 hidden">
            <i class="fas fa-exclamation-triangle mr-1"></i> Please select at least one student first.
        </div>

        <div class="flex flex-wrap gap-3">
            {{-- Download PDF --}}
            <form id="bulk-pdf-form" method="POST" action="{{ route('admin.exams.bulk.pdf') }}" target="_blank">
                @csrf
                <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
                <input type="hidden" name="attempt" value="{{ $currentAttempt }}">
                <div id="pdf-student-inputs"></div>
                <button type="button" onclick="submitBulk('pdf')"
                    class="inline-flex items-center gap-2 px-6 py-2.5 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                    <i class="fas fa-file-pdf"></i> Download PDF
                    <span class="text-xs opacity-80" id="pdf-count-label"></span>
                </button>
            </form>

            {{-- Print (opens same PDF in new tab) --}}
            <button type="button" onclick="submitBulk('print')"
                class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-700 hover:bg-gray-800 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                <i class="fas fa-print"></i> Print Report Cards
                <span class="text-xs opacity-80" id="print-count-label"></span>
            </button>
        </div>

        <p class="text-xs text-gray-400 mt-3">
            <i class="fas fa-info-circle mr-1"></i>
            "Print" opens the multi-page PDF in a new tab — use your browser's print dialog (Ctrl+P / ⌘+P) to print all cards.
        </p>
    </div>

    {{-- Hidden print form (opens in new tab without attachment header) --}}
    <form id="bulk-print-form" method="POST" action="{{ route('admin.exams.bulk.pdf') }}?print=1" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
        <input type="hidden" name="attempt" value="{{ $currentAttempt }}">
        <input type="hidden" name="mode" value="print">
        <div id="print-student-inputs"></div>
    </form>

    @elseif($selectedProgram && $students->isEmpty())
    <div class="bg-amber-50 text-amber-800 border border-amber-200 rounded-xl p-5 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 text-xl"></i>
        <p class="text-sm font-medium">No students have scores for <strong>{{ $selectedProgram->name }}</strong> — Attempt {{ $currentAttempt }}.</p>
    </div>
    @endif

</div>

<script>
function updateCount() {
    const checked = document.querySelectorAll('.student-checkbox:checked').length;
    document.getElementById('selected-count').textContent = checked;
    const label = checked > 0 ? '(' + checked + ')' : '';
    const el1 = document.getElementById('pdf-count-label');
    const el2 = document.getElementById('print-count-label');
    if (el1) el1.textContent = label;
    if (el2) el2.textContent = label;

    // Toggle check icon visual on labels
    document.querySelectorAll('.student-row').forEach(row => {
        const cb = row.querySelector('.student-checkbox');
        const icon = row.querySelector('.check-icon');
        if (cb && icon) icon.style.opacity = cb.checked ? '1' : '0';
        row.style.borderColor = cb && cb.checked ? '#f59e0b' : '';
        row.style.background  = cb && cb.checked ? 'rgba(254,243,199,0.5)' : '';
    });
}

function selectAll() {
    document.querySelectorAll('.student-checkbox').forEach(cb => {
        if (cb.closest('.student-row') && cb.closest('.student-row').style.display !== 'none') {
            cb.checked = true;
        }
    });
    updateCount();
}

function deselectAll() {
    document.querySelectorAll('.student-checkbox').forEach(cb => { cb.checked = false; });
    updateCount();
}

function filterStudents(q) {
    q = q.trim().toLowerCase();
    document.querySelectorAll('.student-row').forEach(row => {
        const match = !q || row.dataset.name.includes(q) || row.dataset.sid.includes(q);
        row.style.display = match ? '' : 'none';
    });
}

function submitBulk(mode) {
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    if (checkboxes.length === 0) {
        document.getElementById('no-selection-warning').classList.remove('hidden');
        return;
    }
    document.getElementById('no-selection-warning').classList.add('hidden');

    const formId       = mode === 'pdf' ? 'bulk-pdf-form'  : 'bulk-print-form';
    const containerId  = mode === 'pdf' ? 'pdf-student-inputs' : 'print-student-inputs';
    const container    = document.getElementById(containerId);

    // Clear previous hidden inputs
    container.innerHTML = '';

    checkboxes.forEach(cb => {
        const inp = document.createElement('input');
        inp.type  = 'hidden';
        inp.name  = 'student_ids[]';
        inp.value = cb.value;
        container.appendChild(inp);
    });

    document.getElementById(formId).submit();
}

// Init count
updateCount();
</script>
@endsection
