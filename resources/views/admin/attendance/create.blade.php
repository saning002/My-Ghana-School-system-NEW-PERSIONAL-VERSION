@extends('layouts.app')
@section('title', 'Mark Attendance')
@section('subtitle', 'Select a program, search students, and mark attendance')

@section('content')
<div class="max-w-4xl"
     x-data="attendanceApp()"
     x-init="init()">

    {{-- Step 1: Program & Course Selector --}}
    <div class="card overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h2 class="text-sm font-bold" style="color:#78520a">
                <i class="fas fa-filter mr-2"></i>Step 1 — Select Program
            </h2>
        </div>
        <div class="px-5 py-5">
            <div class="grid grid-cols-1 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Program *</label>
                    <select x-model="programId" @change="onProgramChange()"
                        class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
                        <option value="">Select program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                {{ $program->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Step 2: Live Student Search --}}
    <div class="card overflow-hidden mb-5" x-show="programId" x-cloak>
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold" style="color:#78520a">
                    <i class="fas fa-search mr-2"></i>Step 2 — Search &amp; Load Students
                </h2>
                <span class="text-xs text-gray-500" x-show="students.length > 0">
                    <span x-text="students.length"></span> student<span x-show="students.length !== 1">s</span> loaded
                </span>
            </div>
        </div>
        <div class="px-5 py-4">
            <div class="relative">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs pointer-events-none"></i>
                <input type="text"
                    x-model="searchQuery"
                    @input.debounce.300ms="fetchStudents()"
                    placeholder="Type a name or student ID to search..."
                    class="w-full pl-9 pr-10 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
                <button x-show="searchQuery" @click="searchQuery=''; fetchStudents()"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2">
                <i class="fas fa-info-circle mr-1"></i>
                Results appear as you type. Leave blank to load all active students in the program.
            </p>
        </div>

        {{-- Loading indicator --}}
        <div x-show="loading" class="px-5 py-6 text-center text-gray-400 text-sm">
            <i class="fas fa-spinner fa-spin mr-2"></i> Searching...
        </div>

        {{-- No results --}}
        <div x-show="!loading && programId && students.length === 0 && searchQuery.length > 0"
             class="px-5 py-8 text-center text-gray-400 text-sm">
            <i class="fas fa-users-slash text-gray-200 text-3xl mb-3 block"></i>
            No students found matching "<span x-text="searchQuery" class="font-semibold"></span>" in this program.
        </div>
    </div>

    {{-- Step 3: Mark Attendance Form --}}
    <form method="GET" action="{{ route('admin.attendance.template') }}" x-show="students.length > 0 && periodId" x-cloak class="card overflow-hidden mb-5">
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h2 class="text-sm font-bold" style="color:#78520a"><i class="fas fa-download mr-2"></i>Download Attendance Book</h2>
        </div>
        <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
            <input type="hidden" name="program_id" :value="programId">
            <input type="hidden" name="academic_period_id" :value="periodId">
            <input type="hidden" name="date" :value="attendanceDate">

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Template date</label>
                <input type="date" x-model="attendanceDate" class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
            </div>
            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-indigo-600 text-white rounded-xl text-sm font-semibold hover:bg-indigo-700 transition-colors">
                    <i class="fas fa-file-excel mr-2"></i> Download Template
                </button>
            </div>
            <div class="text-sm text-gray-500">
                This file is ready for bulk marking.
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.attendance.import') }}" enctype="multipart/form-data" x-show="students.length > 0 && periodId" x-cloak class="card overflow-hidden mb-5">
        @csrf
        <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
            <h2 class="text-sm font-bold" style="color:#78520a"><i class="fas fa-upload mr-2"></i>Import Marked Attendance</h2>
        </div>
        <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-3 gap-4 items-center">
            <input type="hidden" name="program_id" :value="programId">
            <input type="hidden" name="academic_period_id" :value="periodId">

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Attendance file</label>
                <input type="file" name="attendance_file" accept=".xlsx,.xls,.csv" required
                    class="w-full px-4 py-3 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
            </div>
            <input type="hidden" name="date" :value="attendanceDate">
            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-4 py-3 bg-emerald-600 text-white rounded-xl text-sm font-semibold hover:bg-emerald-700 transition-colors">
                    <i class="fas fa-check mr-2"></i> Import Attendance
                </button>
            </div>
            <div class="text-sm text-gray-500">
                Upload the completed attendance book to bulk mark attendance records.
            </div>
        </div>
    </form>

    <form method="POST" action="{{ route('admin.attendance.store') }}" x-show="students.length > 0" x-cloak>
        @csrf
        <input type="hidden" name="program_id" :value="programId">

        <div class="card overflow-hidden">
            {{-- Header with date/period selectors --}}
            <div class="px-5 py-4 border-b border-yellow-50" style="background:#fef9c3">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-bold" style="color:#78520a">
                            <i class="fas fa-clipboard-check mr-2"></i>Step 3 — Mark Attendance
                        </h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            <span x-text="students.length"></span> student<span x-show="students.length !== 1">s</span> listed
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Period *</label>
                            <select name="academic_period_id" x-model="periodId" required
                                class="px-3 py-2 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white">
                                <option value="">Select period</option>
                                @foreach($periods as $period)
                                    <option value="{{ $period->id }}">{{ $period->month }} ({{ $period->session->year ?? '' }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">Date *</label>
                            <input type="date" name="date" x-model="attendanceDate" value="{{ date('Y-m-d') }}" required
                                class="px-3 py-2 border border-yellow-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick mark all --}}
            <div class="px-5 py-3 border-b border-yellow-50 flex items-center gap-3 bg-white">
                <span class="text-xs text-gray-500 font-semibold">Mark all:</span>
                <button type="button" @click="markAll('present')"
                    class="px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg text-xs font-semibold hover:bg-emerald-100 transition-colors">
                    <i class="fas fa-check mr-1"></i> All Present
                </button>
                <button type="button" @click="markAll('absent')"
                    class="px-3 py-1.5 bg-red-50 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-100 transition-colors">
                    <i class="fas fa-times mr-1"></i> All Absent
                </button>
            </div>

            {{-- Student list (rendered by Alpine from JSON) --}}
            <div class="divide-y divide-yellow-50">
                <template x-for="student in students" :key="student.id">
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-yellow-50/30 transition-colors">
                        <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-sm flex-shrink-0"
                             x-text="student.full_name.charAt(0).toUpperCase()"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-800 truncate" x-text="student.full_name"></p>
                            <p class="text-xs text-gray-400 font-mono" x-text="student.student_id"></p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <label class="flex items-center gap-2 cursor-pointer px-3 py-2 bg-emerald-50 hover:bg-emerald-100 rounded-xl transition-colors">
                                <input type="radio" :name="'attendances[' + student.id + ']'" value="present"
                                    class="attendance-radio w-4 h-4 text-emerald-600 focus:ring-emerald-500" required>
                                <span class="text-xs font-semibold text-emerald-700">Present</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer px-3 py-2 bg-red-50 hover:bg-red-100 rounded-xl transition-colors">
                                <input type="radio" :name="'attendances[' + student.id + ']'" value="absent"
                                    class="attendance-radio w-4 h-4 text-red-600 focus:ring-red-500">
                                <span class="text-xs font-semibold text-red-700">Absent</span>
                            </label>
                        </div>
                    </div>
                </template>
            </div>

            {{-- Save button --}}
            <div class="px-5 py-4 border-t border-yellow-50 flex items-center gap-3" style="background:#fef9c3">
                <button type="submit" class="btn-gold px-6 py-3 rounded-xl text-sm font-semibold shadow-sm">
                    <i class="fas fa-save mr-2"></i> Save Attendance
                </button>
                <a href="{{ route('admin.attendance.index') }}"
                   class="px-6 py-3 bg-white text-gray-700 rounded-xl text-sm font-semibold border border-yellow-200">
                    Cancel
                </a>
            </div>
        </div>
    </form>

    {{-- Prompt when no program selected --}}
    <div class="card p-12 text-center" x-show="!programId" x-cloak>
        <i class="fas fa-graduation-cap text-gray-200 text-5xl mb-4 block"></i>
        <p class="text-gray-400 font-medium text-sm">Select a program above to search and load students.</p>
    </div>

</div>

<script>
function attendanceApp() {
    return {
        programId:      '{{ request('program_id', '') }}',
        periodId:       '{{ request('academic_period_id', '') }}',
        attendanceDate: '{{ request('date', date('Y-m-d')) }}',
        searchQuery:    '',
        students:       [],
        loading:        false,
        searchUrl:      '{{ route('admin.attendance.students') }}',

        init() {
            if (this.programId) {
                this.fetchStudents();
            }
        },

        onProgramChange() {
            this.searchQuery = '';
            this.students    = [];
            if (this.programId) {
                this.fetchStudents();
            }
        },

        fetchStudents() {
            if (!this.programId) { this.students = []; return; }
            this.loading = true;
            const params = new URLSearchParams({ program_id: this.programId, q: this.searchQuery });
            fetch(`${this.searchUrl}?${params}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => { this.students = data; })
            .catch(() => { this.students = []; })
            .finally(() => { this.loading = false; });
        },

        markAll(status) {
            document.querySelectorAll(`.attendance-radio[value="${status}"]`).forEach(r => r.checked = true);
        }
    };
}
</script>
@endsection
