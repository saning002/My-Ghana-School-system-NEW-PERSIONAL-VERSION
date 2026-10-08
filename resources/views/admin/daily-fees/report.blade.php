@extends('layouts.app')
@section('title','Daily Fee Report')
@section('subtitle','Detailed collection report for a specific date')

@section('content')
<div class="space-y-5">

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Date</label>
            <input type="date" name="date" value="{{ $date }}"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Load
        </button>
        <a href="{{ route('admin.daily-fees.pdf', ['date'=>$date]) }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-file-pdf text-xs"></i> Download PDF
        </a>
        <a href="{{ route('admin.daily-fees.index') }}"
           class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Back
        </a>
    </form>
</div>

<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    @foreach([
        ['Paid',     $records->count(),                                    'from-emerald-500 to-teal-600'],
        ['Unpaid',   $unpaid->count(),                                     'from-rose-500 to-red-600'],
        ['Exempted', $exempted->count(),                                   'from-amber-500 to-orange-500'],
        ['Collected','GH₵ '.number_format($records->sum('amount'),2),      'from-blue-500 to-cyan-600'],
    ] as [$l,$v,$g])
    <div class="rounded-2xl bg-gradient-to-br {{ $g }} p-4 text-white shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">{{ $l }}</p>
        <p class="text-2xl font-extrabold">{{ $v }}</p>
    </div>
    @endforeach
</div>

{{-- Paid list --}}
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Paid — {{ \Carbon\Carbon::parse($date)->format('l, F j Y') }}</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Student</th>
                <th class="px-4 py-3 text-left">Program</th>
                <th class="px-4 py-3 text-right">Amount Paid</th>
                <th class="px-4 py-3 text-left">Notes</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($records as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $r->student?->user?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $r->student?->program?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-emerald-700">GH₵ {{ number_format($r->amount,2) }}</td>
                    <td class="px-4 py-3 text-slate-400 text-xs">{{ $r->notes ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-8 text-sm text-slate-400">No payments recorded for this date.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Unpaid list --}}
@if($unpaid->isNotEmpty())
<div class="rounded-2xl border border-rose-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-rose-100 bg-rose-50">
        <h3 class="text-sm font-extrabold text-rose-800">Unpaid ({{ $unpaid->count() }} students)</h3>
    </div>
    <div class="divide-y divide-slate-50">
        @foreach($unpaid as $s)
        <div class="flex items-center gap-3 px-5 py-2.5">
            <p class="text-sm font-semibold text-slate-700 flex-1">{{ $s->user->full_name }}</p>
            <p class="text-xs text-slate-400 font-mono">{{ $s->student_id }}</p>
            <span class="text-xs text-slate-400">{{ $s->program?->name }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

</div>
@endsection
