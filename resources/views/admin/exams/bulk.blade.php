extends('layouts.app')
@section('title', 'Bulk Report Cards')
@section('subtitle', 'Download or print report cards for multiple students at once')

@section('content'?'admin.exams.index'?'admin.exams.bulk') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-600 mb-1">Program</label>
                <select name="program_id" required
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--brand)] focus:ring-[var(--brand)] text-sm">
                    <option value="">– Choose program –</option>
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
                    class="w-full rounded-lg border-gray-300 shadow-sm focus:border-[var(--brand)] focus:ring-[var(--brand)] text-sm">
                    @foreach($attempts as $att)
                        <option value="{{ $att }}" {{ $att == $currentAttempt ? 'selected' : ''?'') }}"
                   data-sid="{{ strtolower($student->student_id) }}">
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                    class="student-checkbox w-4 h-4 rounded text-[var(--brand)] border-gray-300 focus:ring-[var(--brand)]"
                    onchange="updateCount()">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $student->user->full_name ?? '–'?'admin.exams.bulk.pdf') }}" target="_blank">
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
            "Print" opens the multi-page PDF in a new tab – use your browser'?'admin.exams.bulk.pdf') }}?print=1" target="_blank" class="hidden">
        @csrf
        <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
        <input type="hidden" name="attempt" value="{{ $currentAttempt }}">
        <input type="hidden" name="mode" value="print">
        <div id="print-student-inputs"></div>
    </form>

    @elseif($selectedProgram && $students->isEmpty())
    <div class="bg-[var(--primary-pale)] text-[var(--primary-text)] border border-[var(--brand)] rounded-xl p-5 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-[var(--brand)] text-xl"></i>
        <p class="text-sm font-medium">No students have scores for <strong>{{ $selectedProgram->name }}</strong> – Attempt {{ $currentAttempt }}.</p>
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
