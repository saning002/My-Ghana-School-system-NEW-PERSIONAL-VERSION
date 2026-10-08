@extends('layouts.app')
@section('title','Daily Fees')
@section('subtitle','Mark student daily fee payments, manage exemptions and scholarships')

@section('content')
<div class="space-y-5">

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

@if(isset($migrationNeeded) && $migrationNeeded)

{{-- Migration not yet run --}}
<div class="rounded-2xl bg-amber-50 border border-amber-200 px-5 py-5 flex items-start gap-3">
    <i class="fas fa-triangle-exclamation text-amber-500 text-xl shrink-0 mt-0.5"></i>
    <div>
        <p class="text-sm font-bold text-amber-800">Database migration required</p>
        <p class="text-xs text-amber-700 mt-1 leading-relaxed">
            The daily fees tables haven't been created yet on your live database.<br>
            Go to <strong>Render → your service → Shell</strong> and run:<br>
            <code class="bg-amber-100 px-2 py-0.5 rounded font-mono mt-1 inline-block">php artisan migrate</code>
        </p>
    </div>
</div>

@else

{{-- KPI row --}}
<div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
    @foreach([
        ['Expected',   'val'=>$summary['expected'],                              'bg'=>'from-indigo-500 to-violet-600', 'icon'=>'fa-users'],
        ['Paid Today', 'val'=>$summary['paid'],                                  'bg'=>'from-emerald-500 to-teal-600',  'icon'=>'fa-check-circle'],
        ['Exempted',   'val'=>$summary['exempted'],                              'bg'=>'from-amber-500 to-orange-500',  'icon'=>'fa-graduation-cap'],
        ['Unpaid',     'val'=>$summary['unpaid'],                                'bg'=>'from-rose-500 to-red-600',      'icon'=>'fa-exclamation-circle'],
        ['Collected',  'val'=>'GH₵ '.number_format($summary['total_collected'],2),'bg'=>'from-blue-500 to-cyan-600',   'icon'=>'fa-coins'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm">
        <div class="flex items-center justify-between mb-2">
            <p class="text-[10px] font-bold uppercase tracking-wider text-white/70">{{ $k[0] }}</p>
            <i class="fas {{ $k['icon'] }} text-white/50 text-sm"></i>
        </div>
        <p class="text-2xl font-extrabold">{{ $k['val'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Exemptions & Custom Fees Management ─────────────────────────────── --}}
<div class="rounded-2xl border border-amber-200 bg-amber-50/30 shadow-sm overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-amber-100 bg-amber-50">
        <div>
            <h3 class="text-sm font-extrabold text-amber-900">
                <i class="fas fa-graduation-cap text-amber-600 mr-1.5"></i>Exemptions &amp; Scholarship Students
            </h3>
            <p class="text-xs text-amber-700 mt-0.5">Set students who are fully exempt (pay GH₵ 0) or on partial daily fees</p>
        </div>
        <button type="button" onclick="document.getElementById('exemptionListPanel').classList.toggle('hidden')"
            class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-3 py-2 shadow transition-all active:scale-95">
            <i class="fas fa-list text-[10px]"></i> View / Manage
        </button>
    </div>

    <div id="exemptionListPanel" class="hidden">
        <div class="px-5 py-4 border-b border-amber-100">
            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-3">Current Exemptions</h4>
            @php
                $exemptions = collect();
                try {
                    $exemptions = \App\Models\StudentFeeExemption::with(['student.user','granter'])
                        ->get()
                        ->filter(fn($e) => $e->isActive());
                } catch(\Throwable $ex) {}
            @endphp
            @if($exemptions->isEmpty())
            <p class="text-xs text-slate-400 py-2">No active exemptions yet.</p>
            @else
            <div class="space-y-2">
                @foreach($exemptions as $ex)
                <div class="flex items-center gap-3 rounded-xl bg-white border border-amber-100 px-4 py-2.5">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-800 truncate">{{ $ex->student?->user?->full_name }}</p>
                        <p class="text-[11px] text-slate-400 font-mono">{{ $ex->student?->student_id }}</p>
                    </div>
                    <span class="rounded-full text-[10px] font-extrabold px-2.5 py-0.5
                        {{ $ex->isFull() ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $ex->isFull() ? 'Full Exempt' : 'GH₵'.number_format($ex->daily_override,2).'/day' }}
                    </span>
                    <span class="text-[10px] text-slate-500">
                        {{ ucfirst($ex->exemption_type) }}
                        @if($ex->reason) · {{ \Illuminate\Support\Str::limit($ex->reason,25) }}@endif
                    </span>
                    @if($ex->valid_until)
                    <span class="text-[10px] text-slate-400">Until {{ $ex->valid_until->format('M d, Y') }}</span>
                    @else
                    <span class="text-[10px] text-emerald-600 font-semibold">Permanent</span>
                    @endif
                    {{-- Remove exemption --}}
                    <form method="POST" action="{{ route('admin.daily-fees.exemption.remove') }}">
                        @csrf
                        <input type="hidden" name="student_id" value="{{ $ex->student_id }}">
                        <button type="submit" class="text-red-400 hover:text-red-600 text-xs transition-colors"
                                onclick="return confirm('Remove exemption for {{ addslashes($ex->student?->user?->full_name ?? '') }}?')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Add new exemption --}}
        <div class="px-5 py-4">
            <h4 class="text-xs font-bold uppercase tracking-wider text-amber-800 mb-3">Add / Update Exemption</h4>
            <form method="POST" action="{{ route('admin.daily-fees.exemption') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Student *</label>
                    <select name="student_id" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="">— Select student —</option>
                        @foreach($students as $s)
                        <option value="{{ $s->id }}">{{ $s->student_id }} — {{ $s->user->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Type *</label>
                    <select name="exemption_type" required class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        @foreach(\App\Models\StudentFeeExemption::$types as $v => $l)
                        <option value="{{ $v }}">{{ $l }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">
                        Daily Override
                        <span class="text-slate-400 font-normal normal-case">(blank = full exempt)</span>
                    </label>
                    <div class="flex items-center gap-1 border border-slate-200 bg-white rounded-xl px-3 py-2.5 focus-within:ring-2 focus-within:ring-amber-400">
                        <span class="text-slate-400 text-sm font-bold shrink-0">GH₵</span>
                        <input type="number" name="daily_override" min="0" step="0.01" placeholder="0.00"
                            class="flex-1 border-0 p-0 text-sm font-semibold focus:ring-0 focus:outline-none bg-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Reason</label>
                    <input type="text" name="reason" maxlength="300" placeholder="e.g. Church scholarship"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Valid From</label>
                    <input type="date" name="valid_from"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">
                        Valid Until <span class="text-slate-400 font-normal">(blank = permanent)</span>
                    </label>
                    <input type="date" name="valid_until"
                        class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div class="sm:col-span-2 lg:col-span-2 flex items-end">
                    <button type="submit"
                        class="w-full rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-sm font-bold py-2.5 shadow transition-all active:scale-95">
                        <i class="fas fa-graduation-cap mr-1.5"></i> Save Exemption
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Filters + date --}}
<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Date</label>
            <input type="date" name="date" value="{{ $today }}"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program</label>
            <select name="program_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All Programs</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Load
        </button>
        <a href="{{ route('admin.daily-fees.report', ['date'=>$today]) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 transition-colors">
            <i class="fas fa-file-lines text-xs"></i> Daily Report
        </a>
        <a href="{{ route('admin.daily-fees.monthly') }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 transition-colors">
            <i class="fas fa-calendar-alt text-xs"></i> Monthly
        </a>
        <a href="{{ route('admin.daily-fees.pdf', ['date'=>$today]) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf text-xs"></i> PDF
        </a>
    </form>
</div>

{{-- Bulk mark form --}}
<form method="POST" action="{{ route('admin.daily-fees.store') }}">
    @csrf
    <input type="hidden" name="date" value="{{ $today }}">

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100 bg-slate-50 flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <input type="checkbox" id="selectAll" class="w-4 h-4 rounded cursor-pointer accent-indigo-600"
                    onchange="document.querySelectorAll('.studentCb').forEach(cb => { if(!cb.disabled) cb.checked = this.checked; })">
                <div>
                    <h3 class="text-sm font-extrabold text-slate-800">
                        {{ \Carbon\Carbon::parse($today)->format('l, F j Y') }}
                    </h3>
                    <p class="text-xs text-slate-500">Daily Rate: <strong class="text-indigo-700">GH₵ {{ number_format($dailyRate,2) }}</strong> per student</p>
                </div>
            </div>
            <button type="submit"
                class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2 shadow transition-all active:scale-95">
                <i class="fas fa-check-double text-[10px]"></i> Mark Selected Paid
            </button>
        </div>

        <div class="divide-y divide-slate-50">
            @forelse($students as $student)
            @php
                $hasPaid      = $paid->has($student->id);
                $exemption    = $student->feeExemption;
                $isExempted   = $exemption?->isActive();
                $isFull       = $isExempted && $exemption->isFull();
                $effectiveRate = $isExempted && !$isFull ? (float)$exemption->daily_override : $dailyRate;
            @endphp
            <div class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 transition-colors {{ $isFull ? 'opacity-55' : '' }}">

                {{-- Checkbox --}}
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                    class="studentCb w-4 h-4 rounded cursor-pointer accent-emerald-600"
                    {{ ($hasPaid || $isFull) ? 'disabled' : '' }}
                    {{ ($hasPaid || $isFull) ? 'checked' : '' }}>

                {{-- Avatar --}}
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-white text-xs font-bold uppercase">
                    {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
                </div>

                {{-- Info --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-bold text-slate-800 truncate">{{ $student->user->full_name }}</p>
                    <p class="text-[11px] text-slate-400 font-mono truncate">{{ $student->student_id }} &bull; {{ $student->program?->name }}</p>
                </div>

                {{-- Exemption badge --}}
                @if($isExempted)
                <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold
                    {{ $isFull ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                    {{ $isFull ? 'Full Exempt' : 'Partial GH₵'.number_format($effectiveRate,2) }}
                    @if($exemption->reason)
                        <span class="font-normal opacity-70">· {{ Str::limit($exemption->reason,20) }}</span>
                    @endif
                </span>
                @endif

                {{-- Paid / unpaid status --}}
                @if($hasPaid)
                <div class="shrink-0 flex items-center gap-1.5">
                    <span class="rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2.5 py-0.5">
                        ✓ GH₵{{ number_format($paid[$student->id]->amount,2) }}
                    </span>
                    {{-- Use JS-based unmark to avoid nested form issue --}}
                    <button type="button"
                        onclick="unmarkFee({{ $student->id }}, '{{ $today }}')"
                        class="text-[10px] font-bold text-red-400 hover:text-red-600 transition-colors">
                        Unmark
                    </button>
                </div>
                @elseif(!$isFull)
                <span class="shrink-0 rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold px-2.5 py-0.5">
                    Unpaid
                </span>
                @endif

                {{-- Exemption icon --}}
                <button type="button"
                    onclick="openExemption({{ $student->id }}, '{{ addslashes($student->user->full_name) }}')"
                    class="shrink-0 text-slate-300 hover:text-amber-500 transition-colors" title="Manage exemption / scholarship">
                    <i class="fas fa-graduation-cap"></i>
                </button>
            </div>
            @empty
            <div class="py-14 text-center">
                <i class="fas fa-users-slash text-3xl text-slate-200 block mb-2"></i>
                <p class="text-sm text-slate-400 font-semibold">No students found.</p>
            </div>
            @endforelse
        </div>
    </div>
</form>

@endif {{-- end @else (migration check) --}}

</div>{{-- end .space-y-5 --}}

{{-- Hidden unmark form — outside all other forms to avoid nesting --}}
<form id="unmarkForm" method="POST" action="{{ route('admin.daily-fees.unmark') }}" style="display:none">
    @csrf
    <input type="hidden" name="student_id" id="unmarkStudentId">
    <input type="hidden" name="date" id="unmarkDate">
</form>

{{-- Exemption Modal --}}
<div id="exemptionModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-7">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-extrabold text-slate-900" id="exemptionModalTitle">Set Exemption / Scholarship</h3>
            <button onclick="closeExemption()" class="text-slate-400 hover:text-slate-700"><i class="fas fa-xmark text-lg"></i></button>
        </div>

        <form method="POST" action="{{ route('admin.daily-fees.exemption') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="student_id" id="exemptionStudentId">

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Exemption Type *</label>
                <select name="exemption_type" required class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @foreach(\App\Models\StudentFeeExemption::$types as $v => $l)
                    <option value="{{ $v }}">{{ $l }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">
                    Daily Override Amount
                    <span class="text-slate-400 font-normal normal-case">(leave blank = full exemption, pays GH₵ 0)</span>
                </label>
                <input type="number" name="daily_override" min="0" step="0.01" placeholder="e.g. 2.00"
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Reason / Notes</label>
                <input type="text" name="reason" maxlength="300" placeholder="e.g. Church scholarship, sponsor name..."
                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Valid From</label>
                    <input type="date" name="valid_from"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">
                        Valid Until <span class="text-slate-400 font-normal">(blank = permanent)</span>
                    </label>
                    <input type="date" name="valid_until"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="flex-1 rounded-xl bg-amber-500 hover:bg-amber-400 text-white text-sm font-bold py-2.5 transition-colors active:scale-95">
                    <i class="fas fa-save mr-1"></i> Save Exemption
                </button>
                <button type="button" onclick="closeExemption()"
                    class="px-5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-bold">
                    Cancel
                </button>
            </div>
        </form>

        <form method="POST" action="{{ route('admin.daily-fees.exemption.remove') }}" class="mt-3 border-t border-slate-100 pt-3">
            @csrf
            <input type="hidden" name="student_id" id="removeExemptionStudentId">
            <button type="submit"
                class="w-full text-xs font-semibold text-red-500 hover:text-red-700 transition-colors py-1.5 rounded-lg hover:bg-red-50">
                <i class="fas fa-trash mr-1"></i> Remove existing exemption
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function unmarkFee(studentId, date) {
    if (!confirm('Unmark this payment?')) return;
    document.getElementById('unmarkStudentId').value = studentId;
    document.getElementById('unmarkDate').value = date;
    document.getElementById('unmarkForm').submit();
}

function openExemption(id, name) {
    document.getElementById('exemptionStudentId').value = id;
    document.getElementById('removeExemptionStudentId').value = id;
    document.getElementById('exemptionModalTitle').textContent = 'Exemption — ' + name;
    document.getElementById('exemptionModal').classList.remove('hidden');
    document.getElementById('exemptionModal').classList.add('flex');
}
function closeExemption() {
    document.getElementById('exemptionModal').classList.add('hidden');
    document.getElementById('exemptionModal').classList.remove('flex');
}
document.getElementById('exemptionModal').addEventListener('click', function(e) {
    if (e.target === this) closeExemption();
});
</script>
@endpush
