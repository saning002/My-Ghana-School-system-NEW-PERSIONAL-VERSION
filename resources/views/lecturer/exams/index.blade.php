@extends('layouts.app')
@section('title', 'Exam Scores')
@section('subtitle', 'Score entry, batch entry, and report card downloads')

@section('content')
<div class="space-y-6">

    {{-- Header Banner --}}
    <div class="card border border-amber-200/60 bg-gradient-to-r from-amber-50 via-amber-50/40 to-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Exam Studio</p>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Course Score Entry</h2>
                <p class="text-xs text-slate-600 mt-0.5">Enter scores, download report cards individually or in bulk.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('lecturer.exams.create') }}"
                   class="btn-gold inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl text-xs font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                    <i class="fas fa-plus"></i> Enter Scores
                </a>
                @if(\App\Models\ClassTeacherAssignment::isClassTeacher(auth()->id() ?? 0))
                <a href="{{ route('lecturer.class-performance.index') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md transition-all active:scale-95">
                    <i class="fas fa-chart-bar"></i> Class Report
                </a>
                @endif
            </div>
        </div>
    </div>

    {{-- Report Card Downloads --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 bg-slate-50">
            <div>
                <h3 class="text-sm font-extrabold text-slate-800">
                    <i class="fas fa-file-pdf text-rose-500 mr-1.5"></i>Report Card Downloads
                </h3>
                <p class="text-xs text-slate-400 mt-0.5">Download individual or bulk report cards for students in your classes</p>
            </div>
        </div>

        <div class="p-5 space-y-4">
            <form method="GET" action="{{ route('lecturer.exams.report-card') }}" class="grid grid-cols-1 sm:grid-cols-4 gap-3 items-end pb-4 border-b border-slate-100">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Program *</label>
                    <select name="program_id" required id="reportProgramId"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400"
                        onchange="loadReportStudents(this.value)">
                        <option value="">— Select —</option>
                        @foreach($assignments->pluck('program')->unique('id') as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Student *</label>
                    <select name="student_id" required id="reportStudentId"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
                        <option value="">— Select program first —</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Attempt</label>
                    <select name="attempt" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-rose-400">
                        @foreach(range(1,5) as $a)<option value="{{ $a }}">Attempt {{ $a }}</option>@endforeach
                    </select>
                </div>
                <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold py-2.5 px-4 shadow transition-all active:scale-95">
                    <i class="fas fa-file-pdf text-[10px]"></i> Download PDF
                </button>
            </form>

            {{-- Bulk download --}}
            <form method="POST" action="{{ route('lecturer.exams.bulk-report-cards') }}" id="bulkReportForm">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end mb-3">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Program (bulk) *</label>
                        <select name="program_id" required id="bulkProgramId"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400"
                            onchange="loadBulkStudents(this.value)">
                            <option value="">— Select —</option>
                            @foreach($assignments->pluck('program')->unique('id') as $prog)
                            <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1.5">Attempt</label>
                        <select name="attempt" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400">
                            @foreach(range(1,5) as $a)<option value="{{ $a }}">Attempt {{ $a }}</option>@endforeach
                        </select>
                    </div>
                    <button type="button" onclick="submitBulk()"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold py-2.5 px-4 shadow transition-all active:scale-95">
                        <i class="fas fa-users text-[10px]"></i> Bulk PDF (Selected)
                    </button>
                </div>

                {{-- Student checkboxes (loaded via JS) --}}
                <div id="bulkStudentList" class="hidden rounded-xl border border-slate-200 bg-slate-50 p-3 space-y-1.5 max-h-48 overflow-y-auto">
                    <p class="text-xs text-slate-400 text-center py-3">Select a program to see students</p>
                </div>
            </form>
        </div>
    </div>

    {{-- Course Cards Grid --}}
    <div>
        <h3 class="text-sm font-extrabold text-slate-800 mb-4">
            <i class="fas fa-book-open text-amber-500 mr-1.5"></i>Your Assigned Courses
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
            @forelse($assignments as $assignment)
            <div class="card p-6 border border-slate-200 bg-white hover:border-amber-300 hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $assignment->course->code }}
                        </span>
                        <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                            Year {{ $assignment->year }}
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 leading-snug mb-2">{{ $assignment->course->name }}</h3>
                    <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-4">
                        {{ $assignment->course->description ?? 'No description available.' }}
                    </p>
                </div>
                <div class="space-y-2 pt-3 border-t border-slate-100">
                    <div class="flex flex-wrap items-center gap-2 text-xs mb-2">
                        <span class="px-3 py-1 rounded-xl bg-amber-50 text-amber-800 font-bold border border-amber-100">
                            {{ $assignment->program->name }}
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('lecturer.courses.scores', ['courseId'=>$assignment->course_id,'programId'=>$assignment->program_id]) }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-all active:scale-95">
                            <i class="fas fa-table-cells-large text-[10px]"></i> Batch Entry
                        </a>
                        <a href="{{ route('lecturer.exams.create') }}?assignment={{ $assignment->course_id }}|{{ $assignment->program_id }}"
                           class="flex-1 inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all active:scale-95">
                            <i class="fas fa-pen text-amber-400 text-[10px]"></i> Enter Scores
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full card p-12 text-center text-slate-400">
                <i class="fas fa-book-reader text-4xl mb-3 text-slate-300"></i>
                <p class="text-sm font-bold text-slate-600">No assigned courses yet.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function loadReportStudents(programId) {
    const sel = document.getElementById('reportStudentId');
    if (!programId) { sel.innerHTML = '<option value="">— Select program first —</option>'; return; }
    sel.innerHTML = '<option value="">Loading...</option>';
    fetch('/lecturer/api/programs/' + programId + '/students')
        .then(r => r.json())
        .then(students => {
            sel.innerHTML = '<option value="">— Select student —</option>' +
                students.map(s => `<option value="${s.id}">${s.student_id} — ${s.full_name}</option>`).join('');
        })
        .catch(() => { sel.innerHTML = '<option value="">Error loading</option>'; });
}

function loadBulkStudents(programId) {
    const container = document.getElementById('bulkStudentList');
    if (!programId) { container.classList.add('hidden'); return; }
    container.classList.remove('hidden');
    container.innerHTML = '<p class="text-xs text-slate-400 text-center py-3"><i class="fas fa-spinner fa-spin mr-1"></i>Loading...</p>';

    fetch('/lecturer/api/programs/' + programId + '/students')
        .then(r => r.json())
        .then(students => {
            if (!students.length) { container.innerHTML = '<p class="text-xs text-slate-400 text-center py-3">No students found</p>'; return; }

            const selectAllBtn = `<label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-indigo-700 pb-2 border-b border-slate-200 mb-2">
                <input type="checkbox" id="selectAllBulk" class="rounded accent-indigo-600"
                    onchange="document.querySelectorAll('.bulkStudentCb').forEach(cb=>cb.checked=this.checked)">
                Select All (${students.length})
            </label>`;

            container.innerHTML = selectAllBtn + students.map(s =>
                `<label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 hover:text-slate-900">
                    <input type="checkbox" name="student_ids[]" value="${s.id}" class="bulkStudentCb rounded accent-indigo-600 w-3.5 h-3.5">
                    ${s.student_id} — ${s.full_name}
                </label>`
            ).join('');
        })
        .catch(() => { container.innerHTML = '<p class="text-xs text-red-400 text-center py-3">Error loading students</p>'; });
}

function submitBulk() {
    const checked = document.querySelectorAll('.bulkStudentCb:checked');
    if (!checked.length) { alert('Select at least one student first.'); return; }
    document.getElementById('bulkReportForm').submit();
}
</script>
@endpush
