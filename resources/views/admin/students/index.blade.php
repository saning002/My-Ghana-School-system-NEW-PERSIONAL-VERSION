@extends('layouts.app')
@section('title','Students')
@section('subtitle','All registered students')

@section('content')

{{-- Bulk action bar --}}
<div id="bulkBar" class="hidden sticky top-2 z-30 mb-4 flex items-center gap-3 bg-indigo-600 text-white px-5 py-3 rounded-2xl shadow-lg">
    <span class="text-sm font-bold"><span id="bulkCount">0</span> selected</span>
    <div class="flex gap-2 ml-auto">
        <form method="POST" action="{{ route('admin.students.bulk-approve') }}" id="bulkApproveForm">
            @csrf
            <div id="bulkApproveIds"></div>
            <button type="submit"
                onclick="return fillBulkIds('bulkApproveIds') && confirm('Approve selected students?')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white text-xs font-bold transition-all active:scale-95">
                <i class="fas fa-check text-[10px]"></i> Approve
            </button>
        </form>
        <form method="POST" action="{{ route('admin.students.bulk-delete') }}" id="bulkDeleteForm">
            @csrf @method('DELETE')
            <div id="bulkDeleteIds"></div>
            <button type="submit"
                onclick="return fillBulkIds('bulkDeleteIds') && confirm('Delete selected students? This cannot be undone.')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-400 text-white text-xs font-bold transition-all active:scale-95">
                <i class="fas fa-trash text-[10px]"></i> Delete
            </button>
        </form>
        <button onclick="clearAll()" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-white/20 hover:bg-white/30 text-white text-xs font-bold transition-all">
            <i class="fas fa-times text-[10px]"></i> Clear
        </button>
    </div>
</div>

{{-- Search + action buttons --}}
<div class="flex flex-col gap-4 mb-4">
    <form method="GET" action="{{ route('admin.students.index') }}" class="flex flex-wrap gap-3 items-end">
        <div class="relative flex-1 min-w-[200px] group">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400 group-focus-within:text-yellow-600 transition-colors">
                <i class="fas fa-search text-sm"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Search by name, ID, or email..."
                class="w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-2xl text-[14px] text-slate-800 focus:outline-none focus:border-yellow-500 focus:ring-4 focus:ring-yellow-500/10 shadow-sm transition-all placeholder:text-slate-400">
        </div>
        <select name="program_id" onchange="this.form.submit()"
            class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 shadow-sm">
            <option value="">All Programs</option>
            @foreach($programs as $prog)
            <option value="{{ $prog->id }}" {{ request('program_id')==$prog->id?'selected':'' }}>{{ $prog->name }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()"
            class="rounded-2xl border border-slate-200 bg-white px-3 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 shadow-sm">
            <option value="">All Statuses</option>
            @foreach(['active','graduated','suspended','manifestation','withdrawn'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-6 py-3 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl text-[14px] font-bold shadow active:scale-95 shrink-0">Search</button>
        @if(request()->hasAny(['search','program_id','status']))
        <a href="{{ route('admin.students.index') }}" class="w-12 h-12 flex items-center justify-center bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600 rounded-2xl text-sm transition-colors shrink-0" title="Clear">
            <i class="fas fa-times"></i>
        </a>
        @endif
    </form>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2">
        <button type="button" id="selectAllBtn" onclick="toggleSelectAll()"
            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-indigo-50 text-indigo-700 rounded-xl text-xs font-semibold hover:bg-indigo-100 transition-colors">
            <i class="fas fa-check-square text-[10px]"></i>
            <span class="hidden sm:inline">Select All</span><span class="sm:hidden">Select</span>
        </button>
        <a href="{{ route('admin.students.template') }}"
            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200 transition-colors"
            title="Download Excel template">
            <i class="fas fa-file-excel text-green-600 text-[10px]"></i>
            <span class="hidden sm:inline">Template</span><span class="sm:hidden">Excel</span>
        </a>
        <button type="button" onclick="document.getElementById('bulkImportModal').classList.remove('hidden')"
            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-amber-50 text-amber-700 rounded-xl text-xs font-semibold hover:bg-amber-100 transition-colors">
            <i class="fas fa-file-upload text-[10px]"></i>
            <span class="hidden sm:inline">Bulk Add</span><span class="sm:hidden">Import</span>
        </button>
        <a href="{{ route('admin.students.bulk-promote') }}"
            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 bg-blue-50 text-blue-700 rounded-xl text-xs font-semibold hover:bg-blue-100 transition-colors">
            <i class="fas fa-level-up-alt text-[10px]"></i>
            <span class="hidden sm:inline">Bulk Promote</span><span class="sm:hidden">Promote</span>
        </a>
        <a href="{{ route('admin.students.create') }}"
            class="inline-flex items-center justify-center gap-2 px-3 py-2.5 btn-gold rounded-xl text-xs font-bold shadow-sm active:scale-95 transition-all col-span-2 sm:col-span-1">
            <i class="fas fa-plus text-[10px]"></i> Add Student
        </a>
    </div>
</div>

<div class="flex items-center justify-between mb-3">
    <p class="text-xs text-gray-500">
        {{ $students->total() }} student{{ $students->total() !== 1 ? 's' : '' }}
        @if(request('search'))
            matching <span class="font-semibold text-primary-600">"{{ request('search') }}"</span>
        @endif
    </p>
</div>

@if(session('import_credentials_url'))
<div class="mb-4 p-3 rounded-lg bg-white border border-slate-100 text-sm">
    <strong>Imported credentials:</strong>
    <a href="{{ session('import_credentials_url') }}" target="_blank" class="ml-2 text-amber-600 font-semibold">Download CSV</a>
</div>
@endif

{{-- Mobile Cards --}}
<div class="space-y-3 lg:hidden">
    @forelse($students as $student)
    @php $isNew = ! $student->is_approved; @endphp
    <div class="card p-4 cursor-pointer hover:shadow-md transition-shadow relative" onclick="window.location='{{ route('admin.students.show', $student) }}'">
        @if($isNew)
        <span class="absolute top-3 right-3 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full shadow-sm">NEW</span>
        @endif
        <div class="flex items-center gap-3 mb-3">
            <input type="checkbox" class="student-chk rounded border-slate-300 text-indigo-600 focus:ring-indigo-400 shrink-0"
                   value="{{ $student->id }}" onclick="event.stopPropagation(); updateBulkBar()">
            <div class="w-11 h-11 rounded-full flex-shrink-0 overflow-hidden border-2 border-amber-100">
                @if($student->photo)
                    <img src="{{ filter_var($student->photo, FILTER_VALIDATE_URL) ? $student->photo : route('admin.students.photo', $student) }}" class="w-full h-full object-cover" alt="{{ $student->user->full_name }}">
                @else
                    <div class="w-full h-full bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base">
                        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
                    </div>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <a href="{{ route('admin.students.show', $student) }}" class="text-sm font-bold text-gray-800 truncate hover:text-primary-600 transition-colors block" onclick="event.stopPropagation()">{{ $student->user->full_name }}</a>
                <p class="text-xs text-gray-400 truncate">{{ $student->user->email }}</p>
            </div>
            <span class="text-[11px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider flex-shrink-0
                {{ $student->status==='active'?'bg-green-50 text-green-700 ring-1 ring-green-600/20':($student->status==='graduated'?'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20':($student->status==='manifestation'?'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20':($student->status==='withdrawn'?'bg-gray-100 text-gray-500 ring-1 ring-gray-400/30':'bg-red-50 text-red-700 ring-1 ring-red-600/20'))) }}">
                {{ ucfirst($student->status) }}
            </span>
        </div>
        <div class="grid grid-cols-2 gap-2 mb-3">
            <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-xs text-gray-400 mb-0.5">Student ID</p>
                <p class="text-xs font-semibold text-gray-700 font-mono">{{ $student->student_id }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg px-3 py-2">
                <p class="text-xs text-gray-400 mb-0.5">Program</p>
                <p class="text-xs font-semibold text-gray-700 truncate">{{ $student->program->name??'—' }}</p>
            </div>
        </div>
        <div class="flex gap-2" onclick="event.stopPropagation()">
            <a href="{{ route('admin.students.show',$student) }}" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-primary-50 text-primary-600 rounded-xl text-xs font-semibold active:bg-primary-100">
                <i class="fas fa-eye text-xs"></i> View
            </a>
            <a href="{{ route('admin.students.edit',$student) }}" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 bg-amber-50 text-amber-600 rounded-xl text-xs font-semibold active:bg-amber-100">
                <i class="fas fa-pen text-xs"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.students.destroy',$student) }}" onsubmit="return confirm('Remove this student?')" class="flex-1">
                @csrf @method('DELETE')
                <button type="submit" class="w-full flex items-center justify-center gap-1.5 py-2.5 bg-red-50 text-red-600 rounded-xl text-xs font-semibold active:bg-red-100">
                    <i class="fas fa-trash text-xs"></i> Delete
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center">
        <i class="fas fa-user-graduate text-gray-200 text-5xl mb-4 block"></i>
        <p class="text-gray-400 font-medium text-sm">No students registered yet.</p>
        <a href="{{ route('admin.students.create') }}" class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 bg-primary-600 text-white rounded-xl text-sm font-semibold">
            <i class="fas fa-plus"></i> Add First Student
        </a>
    </div>
    @endforelse
</div>

{{-- Desktop Table --}}
<div class="hidden lg:block card overflow-hidden shadow-sm border border-slate-200/60">
    <div class="table-wrap">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-slate-50/80 border-b border-slate-200/60">
                    <th class="px-4 py-4 w-10">
                        <input type="checkbox" id="selectAllChk" onchange="selectAll(this)"
                               class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-400">
                    </th>
                    <th class="px-6 py-4 text-[12px] font-bold text-slate-500 uppercase tracking-widest">Student</th>
                    <th class="px-6 py-4 text-[12px] font-bold text-slate-500 uppercase tracking-widest">ID / Email</th>
                    <th class="px-6 py-4 text-[12px] font-bold text-slate-500 uppercase tracking-widest">Program</th>
                    <th class="px-6 py-4 text-[12px] font-bold text-slate-500 uppercase tracking-widest">Status</th>
                    <th class="px-6 py-4 text-[12px] font-bold text-slate-500 uppercase tracking-widest text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100/80">
                @forelse($students as $student)
                @php $isNew = ! $student->is_approved; @endphp
                <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer group" onclick="window.location='{{ route('admin.students.show', $student) }}'">
                    <td class="px-4 py-4" onclick="event.stopPropagation()">
                        <input type="checkbox" class="student-chk rounded border-slate-300 text-indigo-600 focus:ring-indigo-400"
                               value="{{ $student->id }}" onchange="updateBulkBar()">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-full overflow-hidden flex-shrink-0">
                                @if($student->photo)
                                    <img src="{{ filter_var($student->photo, FILTER_VALIDATE_URL) ? $student->photo : route('admin.students.photo', $student) }}" class="w-full h-full object-cover" alt="{{ $student->user->full_name }}">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-yellow-500 to-yellow-600 text-white flex items-center justify-center font-bold text-sm">
                                        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.students.show', $student) }}" class="text-[14px] font-bold text-slate-800 group-hover:text-yellow-600 transition-colors tracking-tight" onclick="event.stopPropagation()">{{ $student->user->full_name }}</a>
                                    @if($isNew)
                                    <span class="bg-emerald-500 text-white text-[9px] font-black uppercase tracking-widest px-2 py-0.5 rounded-full shadow-sm">NEW</span>
                                    @endif
                                </div>
                                <p class="text-[12px] text-slate-400 mt-0.5">{{ $student->churchBranch->name??'No Branch' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-[13px] font-bold text-slate-700 font-mono tracking-tight">{{ $student->student_id }}</p>
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ $student->user->email }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <p class="text-[13px] font-semibold text-slate-700 max-w-xs truncate">{{ $student->program->name??'—' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-[11px] px-2.5 py-1 rounded-full font-bold uppercase tracking-wider
                            {{ $student->status==='active'?'bg-green-50 text-green-700 ring-1 ring-green-600/20':($student->status==='graduated'?'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20':($student->status==='manifestation'?'bg-yellow-50 text-yellow-700 ring-1 ring-yellow-600/20':($student->status==='withdrawn'?'bg-gray-100 text-gray-500 ring-1 ring-gray-400/30':'bg-red-50 text-red-700 ring-1 ring-red-600/20'))) }}">
                            {{ ucfirst($student->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation()">
                            <a href="{{ route('admin.students.show',$student) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-yellow-50 hover:text-yellow-600 transition-colors" title="View">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <a href="{{ route('admin.students.edit',$student) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-yellow-50 hover:text-yellow-600 transition-colors" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.students.destroy',$student) }}" onsubmit="return confirm('Delete this student? This action cannot be undone.')">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-100 text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="w-16 h-16 rounded-full bg-slate-50 text-slate-300 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-graduate text-2xl"></i>
                        </div>
                        <p class="text-slate-500 font-medium">No students found.</p>
                        @if(request('search'))
                        <a href="{{ route('admin.students.index') }}" class="text-sm font-bold text-yellow-600 hover:underline mt-2 inline-block">Clear search</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
        {{ $students->links('pagination::tailwind') }}
    </div>
    @endif
</div>

@if($students->hasPages())
<div class="mt-4 lg:hidden">{{ $students->links() }}</div>
@endif

{{-- ── BULK IMPORT MODAL (includes program selector to avoid the temp-file error) ── --}}
<div id="bulkImportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden" onclick="event.stopPropagation()">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-extrabold text-slate-800">Bulk Import Students</h3>
            <button type="button" onclick="document.getElementById('bulkImportModal').classList.add('hidden')"
                class="w-8 h-8 flex items-center justify-center rounded-lg bg-slate-200 text-slate-500 hover:bg-slate-300 transition-colors">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>
        <div class="px-6 py-5 space-y-4">
            <div class="text-sm text-slate-500 space-y-2">
                <p>Upload the filled template (XLSX/CSV/ZIP). Existing emails or student IDs will be skipped.</p>
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800">
                    <i class="fas fa-lightbulb text-amber-500 mr-1"></i>
                    <strong>Tip:</strong> Select the default program below so you won't be asked again after uploading.
                    If your file already has a Program column, leave it as "Auto-detect".
                </div>
            </div>
            <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">File (Excel / CSV / ZIP) *</label>
                    <input type="file" name="sheet_file" accept=".xlsx,.xls,.csv,.zip" required
                        class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100 transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                        Default Program <span class="font-normal text-slate-400">(used when file has no Program column)</span>
                    </label>
                    <select name="default_program_id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                        <option value="">— Auto-detect from file —</option>
                        @foreach(\App\Models\Program::orderBy('sequence')->get() as $prog)
                        <option value="{{ $prog->id }}">{{ $prog->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3 pt-1">
                    <button type="button" onclick="document.getElementById('bulkImportModal').classList.add('hidden')"
                        class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold transition-colors">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold shadow transition-all active:scale-95">
                        <i class="fas fa-upload mr-1.5 text-xs"></i> Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(session('select_program_for_import'))
    @include('admin.students.select-program-modal')
@endif

<script>
document.addEventListener('click', function(e) {
    const modal = document.getElementById('bulkImportModal');
    if (!modal || modal.classList.contains('hidden')) return;
    if (e.target === modal) modal.classList.add('hidden');
});

function getChecked() {
    return [...document.querySelectorAll('.student-chk:checked')].map(c => c.value);
}

function updateBulkBar() {
    const ids = getChecked();
    const bar = document.getElementById('bulkBar');
    document.getElementById('bulkCount').textContent = ids.length;
    bar.classList.toggle('hidden', ids.length === 0);
    const all = document.querySelectorAll('.student-chk');
    const hdr = document.getElementById('selectAllChk');
    if (hdr) hdr.checked = all.length > 0 && ids.length === all.length;
}

function selectAll(chk) {
    document.querySelectorAll('.student-chk').forEach(c => c.checked = chk.checked);
    updateBulkBar();
}

function toggleSelectAll() {
    const boxes = document.querySelectorAll('.student-chk');
    const allChecked = [...boxes].every(b => b.checked);
    boxes.forEach(b => b.checked = !allChecked);
    updateBulkBar();
}

function clearAll() {
    document.querySelectorAll('.student-chk').forEach(c => c.checked = false);
    const hdr = document.getElementById('selectAllChk');
    if (hdr) hdr.checked = false;
    updateBulkBar();
}

function fillBulkIds(containerId) {
    const ids = getChecked();
    if (ids.length === 0) { alert('Please select at least one student.'); return false; }
    const container = document.getElementById(containerId);
    container.innerHTML = ids.map(id => `<input type="hidden" name="student_ids[]" value="${id}">`).join('');
    return true;
}
</script>

@endsection
