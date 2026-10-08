@extends('layouts.app')

@section('title', 'Mark Attendance')
@section('subtitle', 'Record or edit attendance for your assigned courses')

@section('content')
<div class="space-y-5"
     x-data="{
        searchQuery: '',
        markAll(status) {
            document.querySelectorAll('input[type=radio][value='+status+']').forEach(r => {
                r.checked = true;
                r.dispatchEvent(new Event('change'));
            });
        }
     }">

    {{-- ── Active Period Banner ──────────────────────────────────────────── --}}
    @if($activePeriod)
    <div class="rounded-2xl bg-gradient-to-r from-violet-600 to-indigo-600 px-5 py-3.5 flex items-center gap-3 shadow-md shadow-indigo-200">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-white/20">
            <i class="fas fa-bolt text-white text-xs"></i>
        </span>
        <div>
            <p class="text-[10px] font-bold uppercase tracking-widest text-indigo-200">Active Period</p>
            <p class="text-sm font-extrabold text-white">{{ $activePeriod->full_label }}</p>
        </div>
        <span class="ml-auto flex items-center gap-1.5 rounded-full bg-emerald-400 text-white text-[10px] font-extrabold px-2.5 py-1 uppercase">
            <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Live
        </span>
    </div>
    @else
    <div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-3.5 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-amber-500 shrink-0"></i>
        <p class="text-sm text-amber-800 font-semibold">No active period set. Ask your admin to activate a period before recording attendance.</p>
    </div>
    @endif

    {{-- ── Edit Mode Notice ─────────────────────────────────────────────── --}}
    @if($isEditMode)
    <div class="rounded-2xl bg-blue-50 border border-blue-200 px-5 py-3.5 flex items-center gap-3">
        <i class="fas fa-pencil text-blue-500 shrink-0"></i>
        <p class="text-sm text-blue-800 font-semibold">
            Editing existing attendance for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('D, M d Y') }}</strong>.
            Changes will overwrite the saved record.
        </p>
    </div>
    @endif

    {{-- ── Flash Messages ───────────────────────────────────────────────── --}}
    @if(session('success'))
    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3.5 flex items-center gap-3">
        <i class="fas fa-circle-check text-emerald-500"></i>
        <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
    </div>
    @endif
    @if($errors->any())
    <div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3.5">
        <p class="text-sm font-semibold text-red-800 mb-1"><i class="fas fa-circle-exclamation mr-1"></i>Please fix:</p>
        <ul class="list-disc list-inside text-xs text-red-700 space-y-0.5">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- ── Course & Period Filter ───────────────────────────────────────── --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <h3 class="text-sm font-extrabold text-slate-800 mb-4"><i class="fas fa-filter text-violet-400 mr-1.5"></i>Select Class & Date</h3>
        <form method="GET" action="{{ route('lecturer.attendance.create') }}"
              class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">

            {{-- Assignment --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Class *</label>
                <select name="assignment" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:bg-white transition-all" required>
                    <option value="">-- Select Class --</option>
                    @foreach($assignments as $a)
                    <option value="{{ $a->program_id }}"
                        {{ request('assignment') == $a->program_id ? 'selected' : '' }}>
                        {{ $a->program->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Period --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Period *
                    @if($activePeriod)
                    <span class="ml-1 text-emerald-600 normal-case font-semibold">(active: {{ $activePeriod->name ?: $activePeriod->month }})</span>
                    @endif
                </label>
                <select name="period_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:bg-white transition-all" required>
                    <option value="">-- Select Period --</option>
                    @foreach($periods as $p)
                    <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                        {{ $p->full_label }}{{ $p->is_active ? ' ★ Active' : '' }}
                    </option>
                    @endforeach
                </select>
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Date *</label>
                <input type="date" name="date" value="{{ $selectedDate }}"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-violet-400 focus:bg-white transition-all" required>
                <p class="text-[10px] text-slate-400 mt-1">Pick any past date to edit that day's record.</p>
            </div>

            <button type="submit"
                class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
                <i class="fas fa-search text-xs"></i>
                {{ $isEditMode ? 'Edit Record' : 'Load Roster' }}
            </button>
        </form>
    </div>

    {{-- ── Roster Form ──────────────────────────────────────────────────── --}}
    @if(request()->filled('assignment') && $selectedPeriodId)
    @php $pId = request('assignment'); @endphp

    <form method="POST" action="{{ route('lecturer.attendance.store') }}">
        @csrf
        <input type="hidden" name="program_id"         value="{{ $pId }}">
        <input type="hidden" name="academic_period_id" value="{{ $selectedPeriodId }}">
        <input type="hidden" name="date"               value="{{ $selectedDate }}">

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

            {{-- Toolbar --}}
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                        <i class="fas fa-users text-sm"></i>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-900 text-sm">
                            {{ $isEditMode ? 'Editing' : 'New' }} — {{ \Carbon\Carbon::parse($selectedDate)->format('D, M d Y') }}
                        </h3>
                        <p class="text-xs text-slate-500">{{ $students->count() }} student{{ $students->count() !== 1 ? 's' : '' }} loaded</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    {{-- Search --}}
                    <div class="relative">
                        <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-[10px]"></i>
                        <input type="text" x-model="searchQuery" placeholder="Filter students…"
                            class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-violet-300 w-40">
                    </div>
                    @if($students->isNotEmpty())
                    <button type="button" @click="markAll('present')"
                        class="rounded-xl bg-emerald-100 hover:bg-emerald-200 text-emerald-800 text-xs font-bold px-3 py-2 transition-colors">
                        <i class="fas fa-check-double mr-1"></i> All Present
                    </button>
                    <button type="button" @click="markAll('absent')"
                        class="rounded-xl bg-red-100 hover:bg-red-200 text-red-800 text-xs font-bold px-3 py-2 transition-colors">
                        <i class="fas fa-times-circle mr-1"></i> All Absent
                    </button>
                    @endif
                </div>
            </div>

            @if($students->isEmpty())
            <div class="py-16 text-center">
                <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 mb-3">
                    <i class="fas fa-users-slash text-slate-300 text-xl"></i>
                </div>
                <p class="text-sm font-medium text-slate-500">No students enrolled in this course.</p>
            </div>
            @else

            {{-- Desktop table --}}
            <div class="hidden sm:block overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/60 border-b border-slate-100">
                            <th class="px-5 py-3">ID</th>
                            <th class="px-5 py-3">Student</th>
                            <th class="px-5 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($students as $student)
                        @php $existing = $existingByStudent->get($student->id); @endphp
                        <tr class="hover:bg-slate-50 transition-colors"
                            x-show="!searchQuery || '{{ strtolower($student->student_id.' '.$student->user->full_name) }}'.includes(searchQuery.toLowerCase())">
                            <td class="px-5 py-3.5 font-mono text-xs font-bold text-slate-600">{{ $student->student_id }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 text-white text-xs font-bold">
                                        {{ strtoupper(substr($student->user->full_name ?? '?', 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-slate-800">{{ $student->user->full_name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 text-center">
                                <div class="inline-flex items-center gap-2 rounded-2xl bg-slate-100 p-1.5 border border-slate-200">
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}]" value="present"
                                               class="sr-only peer" required
                                               {{ $existing?->status === 'present' ? 'checked' : '' }}>
                                        <span class="block px-4 py-1.5 rounded-xl text-xs font-bold transition-all
                                                     peer-checked:bg-emerald-600 peer-checked:text-white text-slate-500 hover:text-emerald-700">
                                            <i class="fas fa-check mr-1"></i>Present
                                        </span>
                                    </label>
                                    <label class="cursor-pointer">
                                        <input type="radio" name="attendances[{{ $student->id }}]" value="absent"
                                               class="sr-only peer" required
                                               {{ $existing?->status === 'absent' ? 'checked' : '' }}>
                                        <span class="block px-4 py-1.5 rounded-xl text-xs font-bold transition-all
                                                     peer-checked:bg-red-600 peer-checked:text-white text-slate-500 hover:text-red-700">
                                            <i class="fas fa-times mr-1"></i>Absent
                                        </span>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div class="sm:hidden divide-y divide-slate-100">
                @foreach($students as $student)
                @php $existing = $existingByStudent->get($student->id); @endphp
                <div class="p-4 space-y-3"
                     x-show="!searchQuery || '{{ strtolower($student->student_id.' '.$student->user->full_name) }}'.includes(searchQuery.toLowerCase())">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 text-white text-xs font-bold">
                            {{ strtoupper(substr($student->user->full_name ?? '?', 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm">{{ $student->user->full_name }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ $student->student_id }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $student->id }}]" value="present"
                                   class="sr-only peer" required
                                   {{ $existing?->status === 'present' ? 'checked' : '' }}>
                            <span class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl border-2 border-slate-200 text-xs font-bold
                                         transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600 bg-white">
                                <i class="fas fa-check"></i> Present
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="radio" name="attendances[{{ $student->id }}]" value="absent"
                                   class="sr-only peer" required
                                   {{ $existing?->status === 'absent' ? 'checked' : '' }}>
                            <span class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl border-2 border-slate-200 text-xs font-bold
                                         transition-all peer-checked:border-red-500 peer-checked:bg-red-600 peer-checked:text-white text-slate-600 bg-white">
                                <i class="fas fa-times"></i> Absent
                            </span>
                        </label>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Save footer --}}
            <div class="px-5 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between gap-3 flex-wrap">
                <p class="text-xs text-slate-400">
                    @if($isEditMode)
                        <i class="fas fa-pencil text-blue-400 mr-1"></i> Editing record for {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }} — saving overwrites existing.
                    @else
                        Saving for the first time on this date.
                    @endif
                </p>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-bold px-7 py-3 shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    <i class="fas fa-floppy-disk"></i>
                    {{ $isEditMode ? 'Update Attendance' : 'Save Attendance' }}
                </button>
            </div>
            @endif
        </div>
    </form>
    @endif

</div>
@endsection
