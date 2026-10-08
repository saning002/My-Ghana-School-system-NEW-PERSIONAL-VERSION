@extends('layouts.app')

@section('title', 'Academic Reports')
@section('subtitle', 'View program students and preview report cards')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="card border border-amber-200/60 bg-gradient-to-r from-amber-50 to-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Teacher Studio</p>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Student Academic Reports</h2>
                <p class="text-xs text-slate-600 mt-0.5">Filter by program to view student enrollment and generate official report cards.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-white px-3.5 py-1.5 text-xs font-bold text-amber-800 shadow-sm shrink-0">
                <i class="fas fa-file-alt text-amber-500"></i> Report Cards Studio
            </div>
        </div>
    </div>

    {{-- Program Selection Card --}}
    <div class="card p-6 border border-slate-200 bg-white">
        <form method="GET" action="{{ route('lecturer.reports.index') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="program_id" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Select Program *</label>
                <select name="program_id" id="program_id" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all">
                    <option value="">-- Choose Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full btn-gold inline-flex items-center justify-center gap-2 py-3 px-5 rounded-xl text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                    <i class="fas fa-search text-xs"></i> Load Students
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <form method="POST" action="{{ route('lecturer.exams.bulk-report-cards') }}" id="bulkForm">
        @csrf
        <input type="hidden" name="program_id" value="{{ $selectedProgram->id }}">
        <input type="hidden" name="attempt" id="bulkAttempt" value="1">

        <div class="card border border-slate-200 bg-white overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-100 bg-slate-50/80 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-900">{{ $selectedProgram->name }}</h3>
                    <p class="text-xs text-slate-500">{{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }} enrolled</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select name="attempt" id="attemptSelect" onchange="document.getElementById('bulkAttempt').value=this.value"
                        class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                        @foreach(range(1,5) as $a)
                        <option value="{{ $a }}">Attempt {{ $a }}</option>
                        @endforeach
                    </select>
                    <button type="button" onclick="toggleAll()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                        <i class="fas fa-check-square text-slate-500"></i> Select All
                    </button>
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold shadow transition-all active:scale-95">
                        <i class="fas fa-file-pdf text-[10px]"></i> Bulk PDF (Selected)
                    </button>
                </div>
            </div>

            @if($students->isEmpty())
                <div class="p-12 text-center text-slate-400">
                    <i class="fas fa-user-slash text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-bold text-slate-600">No students are currently enrolled in this program.</p>
                </div>
            @else
                {{-- Desktop table --}}
                <div class="table-wrap overflow-x-auto hidden lg:block">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                                <th class="px-4 py-3 w-8"></th>
                                <th class="px-6 py-3">Student Name</th>
                                <th class="px-6 py-3">Reg. Number</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3 text-right">Report Card Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $student)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-4 py-4">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                               class="student-chk rounded border-slate-300 text-violet-600 focus:ring-violet-400">
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-900">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-xs shrink-0">
                                                {{ strtoupper(substr($student->user->full_name ?? 'S', 0, 1)) }}
                                            </div>
                                            <span>{{ $student->user->full_name ?? '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-xs font-bold text-slate-700">{{ $student->student_id }}</td>
                                    <td class="px-6 py-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ ucfirst($student->status ?? 'active') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('lecturer.reports.student', $student) }}?attempt={{ request('attempt',1) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold transition-colors shadow-sm">
                                            <i class="fas fa-file-alt text-amber-600"></i> Preview Card
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Mobile cards --}}
                <div class="lg:hidden divide-y divide-slate-100">
                    @foreach($students as $student)
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                   class="student-chk mt-1 rounded border-slate-300 text-violet-600 focus:ring-violet-400">
                            <div class="w-11 h-11 rounded-2xl bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-sm shrink-0 shadow-sm">
                                {{ strtoupper(substr($student->user->full_name ?? 'S', 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 text-sm leading-tight truncate">{{ $student->user->full_name ?? '—' }}</p>
                                        <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $student->student_id }}</p>
                                    </div>
                                    <span class="shrink-0 inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                        {{ ucfirst($student->status ?? 'active') }}
                                    </span>
                                </div>
                                <div class="mt-3 pt-3 border-t border-slate-100">
                                    <a href="{{ route('lecturer.reports.student', $student) }}?attempt={{ request('attempt',1) }}" class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 text-[11px] font-bold transition-colors shadow-sm">
                                        <i class="fas fa-file-alt text-amber-600"></i> Preview Report Card
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
        </form>
    @endif
</div>

@push('scripts')
<script>
function toggleAll() {
    const boxes = document.querySelectorAll('.student-chk');
    const allChecked = [...boxes].every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
}
</script>
@endpush
@endsection
