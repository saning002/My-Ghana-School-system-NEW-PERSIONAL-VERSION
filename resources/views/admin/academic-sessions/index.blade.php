@extends('layouts.app')
@section('title', 'Academic Sessions')
@section('subtitle', 'Manage academic years, semester/term periods, and the active period')

@section('content')
<div class="space-y-6">

{{-- ── Active Period Banner ─────────────────────────────────────────────── --}}
@if($activePeriod)
<div class="rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-lg shadow-emerald-200">
    <div class="flex items-center gap-3 text-white">
        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
            <i class="fas fa-bolt text-sm"></i>
        </span>
        <div>
            <p class="text-[11px] font-bold uppercase tracking-widest text-emerald-100">Currently Active Period</p>
            <p class="text-base font-extrabold">{{ $activePeriod->full_label }}</p>
        </div>
    </div>
    <form method="POST" action="{{ route('admin.academic-sessions.deactivate') }}">
        @csrf
        <input type="hidden" name="period_id" value="{{ $activePeriod->id }}">
        <button class="inline-flex items-center gap-2 rounded-xl bg-white/20 hover:bg-white/30 border border-white/30 px-4 py-2 text-sm font-bold text-white transition-all">
            <i class="fas fa-stop-circle"></i> Close Period
        </button>
    </form>
</div>
@else
<div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-4 flex items-center gap-3">
    <i class="fas fa-exclamation-triangle text-amber-500 text-lg shrink-0"></i>
    <div>
        <p class="text-sm font-bold text-amber-800">No active period set</p>
        <p class="text-xs text-amber-700 mt-0.5">Teachers cannot record attendance or scores until you activate a period below. Click <strong>Set Active</strong> on any period.</p>
    </div>
</div>
@endif

{{-- ── Flash messages ───────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 flex items-center gap-3">
    <i class="fas fa-circle-check text-emerald-500"></i>
    <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
</div>
@endif
@if(session('error'))
<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 flex items-center gap-3">
    <i class="fas fa-circle-exclamation text-red-500"></i>
    <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── Create Form ──────────────────────────────────────────────────── --}}
    <div class="glass-card p-6 self-start rounded-2xl border border-slate-200 bg-white shadow-sm">
        <h3 class="text-base font-extrabold text-slate-900 mb-0.5">Create New Session</h3>
        <p class="text-xs text-slate-500 mb-5">Periods are auto-generated based on the mode you choose.</p>

        <form method="POST" action="{{ route('admin.academic-sessions.store') }}" class="space-y-4">
            @csrf
            @if($errors->any())
            <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-xs text-red-700">
                @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
            </div>
            @endif

            {{-- Year --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Academic Year *</label>
                <input type="text" name="year" value="{{ old('year') }}" placeholder="e.g. 2026/2027"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400 focus:bg-white transition-all" required>
                <p class="text-[11px] text-slate-400 mt-1">Format: YYYY/YYYY</p>
            </div>

            {{-- Mode --}}
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-2">Session Mode *</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="semester" class="sr-only peer" {{ old('mode','semester')==='semester' ? 'checked' : '' }}>
                        <span class="flex flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 bg-slate-50 p-3 text-center transition-all
                                     peer-checked:border-violet-500 peer-checked:bg-violet-50 peer-checked:text-violet-800">
                            <i class="fas fa-layer-group text-lg text-violet-400 peer-checked:text-violet-600"></i>
                            <span class="text-xs font-extrabold">Semester</span>
                            <span class="text-[10px] text-slate-400">2 periods</span>
                        </span>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="mode" value="term" class="sr-only peer" {{ old('mode')==='term' ? 'checked' : '' }}>
                        <span class="flex flex-col items-center gap-1.5 rounded-xl border-2 border-slate-200 bg-slate-50 p-3 text-center transition-all
                                     peer-checked:border-amber-500 peer-checked:bg-amber-50 peer-checked:text-amber-800">
                            <i class="fas fa-calendar-days text-lg text-amber-400 peer-checked:text-amber-600"></i>
                            <span class="text-xs font-extrabold">Term</span>
                            <span class="text-[10px] text-slate-400">3 periods</span>
                        </span>
                    </label>
                </div>
            </div>

            <button type="submit"
                class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-500 hover:to-indigo-500 text-white text-sm font-bold py-3 shadow-md transition-all active:scale-95">
                <i class="fas fa-plus"></i> Create Session
            </button>
        </form>
    </div>

    {{-- ── Quick Stats ──────────────────────────────────────────────────── --}}
    <div class="lg:col-span-2 space-y-4">
        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <p class="text-2xl font-extrabold text-slate-900">{{ $sessions->count() }}</p>
                <p class="text-xs text-slate-500 mt-1">Sessions</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <p class="text-2xl font-extrabold text-slate-900">{{ $sessions->sum('periods_count') }}</p>
                <p class="text-xs text-slate-500 mt-1">Total Periods</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 text-center shadow-sm">
                <p class="text-lg font-extrabold text-slate-900">{{ $sessions->first()?->year ?? '—' }}</p>
                <p class="text-xs text-slate-500 mt-1">Latest</p>
            </div>
        </div>

        <div class="rounded-2xl bg-indigo-50 border border-indigo-100 p-4 flex gap-3">
            <i class="fas fa-lightbulb text-indigo-400 mt-0.5 shrink-0"></i>
            <div class="text-xs text-indigo-700 leading-relaxed space-y-1">
                <p class="font-bold text-indigo-800">How periods work</p>
                <p>Choose <strong>Semester</strong> to get <em>Semester 1</em> and <em>Semester 2</em>, or <strong>Term</strong> to get <em>Term 1, 2, 3</em>. Mode is locked once the session is created.</p>
                <p>Click <strong>Set Active</strong> on any period to make it the current one. Teachers will automatically see the active period when recording attendance or entering scores.</p>
            </div>
        </div>
    </div>
</div>

{{-- ── Sessions List ────────────────────────────────────────────────────── --}}
@forelse($sessions as $session)
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    {{-- Session header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50">
        <div class="flex items-center gap-3">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl
                {{ $session->mode === 'term' ? 'bg-amber-100 text-amber-700' : 'bg-violet-100 text-violet-700' }}">
                <i class="{{ $session->mode === 'term' ? 'fas fa-calendar-days' : 'fas fa-layer-group' }}"></i>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <p class="text-sm font-extrabold text-slate-900">{{ $session->year }}</p>
                    <span class="rounded-full px-2.5 py-0.5 text-[10px] font-extrabold uppercase tracking-wider
                        {{ $session->mode === 'term' ? 'bg-amber-100 text-amber-700' : 'bg-violet-100 text-violet-700' }}">
                        {{ ucfirst($session->mode) }}
                    </span>
                </div>
                <p class="text-xs text-slate-500">{{ $session->periods_count }} {{ $session->mode === 'term' ? 'terms' : 'semesters' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.academic-sessions.destroy', $session) }}"
              onsubmit="return confirm('Delete {{ $session->year }} and ALL its periods? Attendance records linked to these periods will also be deleted.')">
            @csrf @method('DELETE')
            <button class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition-colors">
                <i class="fas fa-trash text-xs"></i> Delete
            </button>
        </form>
    </div>

    {{-- Periods grid --}}
    <div class="px-6 py-5">
        @if($session->periods->isEmpty())
            <p class="text-xs text-red-500 text-center py-2">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No periods — delete and recreate this session.
            </p>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 {{ $session->mode === 'term' ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }} gap-3">
            @foreach($session->periods as $period)
            @php $isActive = $activePeriod?->id === $period->id; @endphp
            <div class="rounded-xl border-2 p-4 transition-all
                {{ $isActive
                    ? 'border-emerald-400 bg-emerald-50 shadow-md shadow-emerald-100'
                    : 'border-slate-200 bg-slate-50 hover:border-slate-300' }}">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="text-sm font-extrabold {{ $isActive ? 'text-emerald-800' : 'text-slate-800' }}">
                            {{ $period->name ?: $period->month }}
                        </p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ $session->year }} · ID #{{ $period->id }}</p>
                    </div>
                    @if($isActive)
                    <span class="shrink-0 inline-flex items-center gap-1 rounded-full bg-emerald-500 text-white text-[10px] font-extrabold px-2.5 py-1 uppercase tracking-wider">
                        <span class="h-1.5 w-1.5 rounded-full bg-white animate-pulse"></span> Active
                    </span>
                    @endif
                </div>

                <div class="mt-3 flex gap-2">
                    @if(!$isActive)
                    <form method="POST" action="{{ route('admin.academic-sessions.set-active') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <button class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold py-2 transition-all active:scale-95">
                            <i class="fas fa-bolt text-[10px]"></i> Set Active
                        </button>
                    </form>
                    @else
                    <form method="POST" action="{{ route('admin.academic-sessions.deactivate') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="period_id" value="{{ $period->id }}">
                        <button class="w-full inline-flex items-center justify-center gap-1.5 rounded-lg bg-slate-200 hover:bg-slate-300 text-slate-700 text-xs font-bold py-2 transition-all active:scale-95">
                            <i class="fas fa-stop-circle text-[10px]"></i> Deactivate
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@empty
<div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-16 text-center">
    <div class="inline-flex h-16 w-16 items-center justify-center rounded-3xl bg-slate-100 mb-4">
        <i class="fas fa-calendar-times text-slate-300 text-2xl"></i>
    </div>
    <p class="text-slate-600 font-semibold mb-1">No academic sessions yet</p>
    <p class="text-sm text-slate-400">Create your first session using the form above. Teachers need at least one active period to record attendance.</p>
</div>
@endforelse

</div>
@endsection
