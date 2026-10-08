@extends('staff-portal.layout')
@section('title','Daily Fees')
@section('subtitle','Daily fee collection summary (view only)')

@section('content')
<div class="space-y-5">

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([['Paid',$summary['paid'],'from-emerald-500 to-teal-600'],['Unpaid',$summary['unpaid'],'from-rose-500 to-red-600'],['Exempted',$summary['exempted'],'from-amber-500 to-orange-500'],['Collected','GH₵ '.number_format($summary['total_collected'],2),'from-blue-500 to-cyan-600']] as [$l,$v,$g])
    <div class="rounded-2xl bg-gradient-to-br {{ $g }} p-4 text-white shadow-sm text-center">
        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">{{ $l }}</p>
        <p class="text-xl font-extrabold">{{ $v }}</p>
    </div>
    @endforeach
</div>

<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Date</label>
            <input type="date" name="date" value="{{ $today }}" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Program</label>
            <select name="program_id" class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                <option value="">All</option>
                @foreach($programs as $p)<option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>@endforeach
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Filter
        </button>
    </form>
</div>

{{-- Desktop list (hidden on mobile) --}}
<div class="card overflow-hidden hidden lg:block">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">{{ \Carbon\Carbon::parse($today)->format('l, F j Y') }} — Daily Rate: GH₵ {{ number_format($dailyRate,2) }}</h3>
    </div>
    <div class="divide-y divide-slate-50">
        @forelse($students as $student)
        @php $hasPaid=$paid->has($student->id); $ex=$student->feeExemption; $isEx=$ex?->isActive(); $isFull=$isEx&&$ex->isFull(); @endphp
        <div class="flex items-center gap-3 px-4 py-3 {{ $isFull?'opacity-60':'' }}">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 text-white text-xs font-bold">{{ strtoupper(substr($student->user->full_name??'?',0,1)) }}</div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-800 truncate">{{ $student->user->full_name }}</p>
                <p class="text-[11px] text-slate-400 font-mono">{{ $student->student_id }} · {{ $student->program?->name }}</p>
            </div>
            @if($isFull)<span class="rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5">Exempt</span>
            @elseif($hasPaid)<span class="rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2.5 py-0.5">✓ GH₵{{ number_format($paid[$student->id]->amount,2) }}</span>
            @else<span class="rounded-full bg-slate-100 text-slate-500 text-[10px] font-semibold px-2.5 py-0.5">Unpaid</span>
            @endif
        </div>
        @empty
        <div class="py-10 text-center text-sm text-slate-400">No students found.</div>
        @endforelse
    </div>
</div>

{{-- Mobile cards (hidden on desktop) --}}
<div class="lg:hidden space-y-3">
    <div class="px-1 mb-2">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">{{ \Carbon\Carbon::parse($today)->format('l, M j Y') }} · Rate GH₵{{ number_format($dailyRate,0) }}</h3>
    </div>
    @forelse($students as $student)
    @php $hasPaid=$paid->has($student->id); $ex=$student->feeExemption; $isEx=$ex?->isActive(); $isFull=$isEx&&$ex->isFull(); @endphp
    <div class="card p-4 {{ $isFull?'opacity-60':'' }}">
        <div class="flex items-start gap-3">
            <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center text-white font-bold shrink-0 shadow-sm">
                {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 text-sm leading-tight truncate">{{ $student->user->full_name }}</p>
                        <p class="text-[11px] font-mono text-slate-500 mt-0.5">{{ $student->student_id }}</p>
                    </div>
                    @if($isFull)
                    <span class="shrink-0 rounded-full bg-amber-100 text-amber-800 text-[10px] font-extrabold px-2.5 py-0.5">Exempt</span>
                    @elseif($hasPaid)
                    <span class="shrink-0 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold px-2.5 py-0.5">Paid GH₵{{ number_format($paid[$student->id]->amount,0) }}</span>
                    @else
                    <span class="shrink-0 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold px-2.5 py-0.5">Unpaid</span>
                    @endif
                </div>
                <div class="mt-2 pt-2 border-t border-slate-100">
                    <p class="text-[11px] text-slate-600 font-medium truncate"><i class="fas fa-graduation-cap text-slate-400 text-[10px] mr-1"></i>{{ $student->program?->name ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-8 text-center">
        <i class="fas fa-calendar-day text-3xl text-slate-200 mb-2 block"></i>
        <p class="text-sm font-semibold text-slate-400">No students found.</p>
    </div>
    @endforelse
</div>

</div>
@endsection
