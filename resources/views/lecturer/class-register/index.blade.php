@extends('layouts.app')
@section('title','Class Register')
@section('subtitle', $program->name . ' — Daily Attendance Register')

@section('content')
<div class="space-y-5"
     x-data="{
        searchQuery: '',
        markAll(status) {
            document.querySelectorAll('input[type=radio][value='+status+']').forEach(r => {
                r.checked = true;
                r.dispatchEvent(new Event('change', { bubbles: true }));
            });
        }
     }">

{{-- Flash --}}
@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 text-sm text-red-800">
    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

{{-- Hero --}}
<div class="rounded-3xl relative overflow-hidden p-6 lg:p-8 text-white"
     style="background:linear-gradient(135deg,#064e3b 0%,#065f46 35%,#059669 70%,#34d399 100%)">
    <div class="pointer-events-none absolute -top-16 -right-16 h-56 w-56 rounded-full bg-white/4 blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-yellow-400/50 to-transparent"></div>

    <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-3.5 py-1 text-[11px] font-bold uppercase tracking-widest text-emerald-100 mb-2">
                <i class="fas fa-user-tie text-emerald-300 text-[10px]"></i> Class Teacher Register
            </div>
            <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">{{ $program->name }}</h1>
            <p class="text-emerald-100 text-sm mt-1">
                {{ $students->count() }} students &bull;
                @if($isEditMode)
                    <span class="text-amber-300 font-bold"><i class="fas fa-pencil mr-1"></i>Editing existing record</span>
                @else
                    <span class="text-emerald-200">New record for {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}</span>
                @endif
            </p>
        </div>

        {{-- Active period chip --}}
        @if($activePeriod)
        <div class="inline-flex items-center gap-1.5 rounded-full border border-yellow-400/40 bg-yellow-400/15 px-3.5 py-1.5 text-xs font-bold text-yellow-200">
            <i class="fas fa-calendar-check text-yellow-300 text-[10px]"></i>
            {{ $activePeriod->full_label }}
        </div>
        @endif
    </div>
</div>

{{-- Edit mode notice --}}
@if($isEditMode)
<div class="rounded-2xl bg-blue-50 border border-blue-200 px-5 py-3.5 flex items-center gap-3">
    <i class="fas fa-pencil text-blue-500 shrink-0"></i>
    <p class="text-sm text-blue-800 font-semibold">
        Editing existing register for <strong>{{ \Carbon\Carbon::parse($selectedDate)->format('D, M d Y') }}</strong>.
        Saving will overwrite the current record.
    </p>
</div>
@endif

{{-- No active period warning --}}
@if(!$activePeriod)
<div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-3.5 flex items-center gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500 shrink-0"></i>
    <p class="text-sm text-amber-800 font-semibold">No active period set. Ask your admin to activate a period before recording attendance.</p>
</div>
@endif

{{-- Filter bar --}}
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" action="{{ route('lecturer.class-register.index') }}"
          class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Period
                @if($activePeriod)
                <span class="ml-1 text-emerald-600 normal-case font-semibold">(active: {{ $activePeriod->name ?? $activePeriod->month }})</span>
                @endif
            </label>
            <select name="period_id"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400"
                required>
                <option value="">— Select period —</option>
                @foreach($periods as $p)
                <option value="{{ $p->id }}" {{ $selectedPeriodId == $p->id ? 'selected' : '' }}>
                    {{ $p->full_label }}{{ $p->is_active ? ' ★ Active' : '' }}
                </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                Date
                <span class="text-slate-400 font-normal normal-case ml-1">— pick any past date to edit</span>
            </label>
            <input type="date" name="date" value="{{ $selectedDate }}"
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-emerald-400"
                required>
        </div>
        <button type="submit"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i>
            {{ $isEditMode ? 'Edit Record' : 'Load Register' }}
        </button>
    </form>
</div>

{{-- Roster form --}}
@if($selectedPeriodId)
<form method="POST" action="{{ route('lecturer.class-register.store') }}">
    @csrf
    <input type="hidden" name="academic_period_id" value="{{ $selectedPeriodId }}">
    <input type="hidden" name="date" value="{{ $selectedDate }}">

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">

        {{-- Toolbar --}}
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <i class="fas fa-clipboard-list text-sm"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">
                        {{ $isEditMode ? 'Editing' : 'New' }} — {{ \Carbon\Carbon::parse($selectedDate)->format('l, M d Y') }}
                    </h3>
                    <p class="text-xs text-slate-500">{{ $students->count() }} students in {{ $program->name }}</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                {{-- Search --}}
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-2.5 text-slate-400 text-[10px]"></i>
                    <input type="text" x-model="searchQuery" placeholder="Filter students…"
                        class="pl-8 pr-3 py-2 rounded-xl border border-slate-200 bg-white text-xs focus:outline-none focus:ring-2 focus:ring-emerald-300 w-40">
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

        {{-- Student list --}}
        @if($students->isEmpty())
        <div class="py-14 text-center">
            <i class="fas fa-users-slash text-3xl text-slate-200 block mb-3"></i>
            <p class="text-sm font-semibold text-slate-400">No students found in {{ $program->name }}.</p>
        </div>
        @else

        {{-- Desktop table --}}
        <div class="hidden sm:block overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50/60 border-b border-slate-100">
                        <th class="px-5 py-3">#</th>
                        <th class="px-5 py-3">Student</th>
                        <th class="px-4 py-3">ID</th>
                        <th class="px-5 py-3 text-center">Attendance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($students as $i => $student)
                    @php $rec = $existing->get($student->id); @endphp
                    <tr class="hover:bg-slate-50 transition-colors"
                        x-show="!searchQuery || '{{ strtolower($student->student_id.' '.$student->user->full_name) }}'.includes(searchQuery.toLowerCase())">
                        <td class="px-5 py-3.5 text-xs text-slate-400 font-mono">{{ $i+1 }}</td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white text-xs font-bold">
                                    {{ strtoupper(substr($student->user->full_name ?? '?', 0, 1)) }}
                                </div>
                                <span class="font-semibold text-slate-800">{{ $student->user->full_name }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 font-mono text-xs text-slate-500">{{ $student->student_id }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <div class="inline-flex items-center gap-2 rounded-2xl bg-slate-100 p-1.5 border border-slate-200">
                                <label class="cursor-pointer">
                                    <input type="radio" name="attendances[{{ $student->id }}]" value="present"
                                           class="sr-only peer" required
                                           {{ $rec?->status === 'present' ? 'checked' : '' }}>
                                    <span class="block px-4 py-1.5 rounded-xl text-xs font-bold transition-all
                                                 peer-checked:bg-emerald-600 peer-checked:text-white text-slate-500 hover:text-emerald-700">
                                        <i class="fas fa-check mr-1"></i>Present
                                    </span>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="attendances[{{ $student->id }}]" value="absent"
                                           class="sr-only peer" required
                                           {{ $rec?->status === 'absent' ? 'checked' : '' }}>
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
            @php $rec = $existing->get($student->id); @endphp
            <div class="p-4 space-y-3"
                 x-show="!searchQuery || '{{ strtolower($student->student_id.' '.$student->user->full_name) }}'.includes(searchQuery.toLowerCase())">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 text-white text-xs font-bold">
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
                               {{ $rec?->status === 'present' ? 'checked' : '' }}>
                        <span class="flex items-center justify-center gap-1.5 w-full py-2.5 rounded-xl border-2 border-slate-200 text-xs font-bold
                                     transition-all peer-checked:border-emerald-500 peer-checked:bg-emerald-600 peer-checked:text-white text-slate-600 bg-white">
                            <i class="fas fa-check"></i> Present
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="attendances[{{ $student->id }}]" value="absent"
                               class="sr-only peer" required
                               {{ $rec?->status === 'absent' ? 'checked' : '' }}>
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
                <i class="fas fa-pencil text-blue-400 mr-1"></i> Editing record for {{ \Carbon\Carbon::parse($selectedDate)->format('M d, Y') }}
                @else
                First-time record for this date.
                @endif
            </p>
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-sm font-bold px-7 py-3 shadow-lg shadow-emerald-200 transition-all active:scale-95">
                <i class="fas fa-floppy-disk"></i>
                {{ $isEditMode ? 'Update Register' : 'Save Register' }}
            </button>
        </div>
        @endif
    </div>
</form>
@endif

</div>
@endsection
