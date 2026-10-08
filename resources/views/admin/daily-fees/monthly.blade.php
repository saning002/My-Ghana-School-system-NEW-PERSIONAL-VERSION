@extends('layouts.app')
@section('title','Monthly Fee Report')
@section('subtitle','Daily collection totals for the selected month')

@section('content')
<div class="space-y-5">

<div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Month</label>
            <input type="month" name="month" value="{{ $month }}"
                class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Load
        </button>
        <a href="{{ route('admin.daily-fees.index') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold px-4 py-2.5 transition-colors">
            <i class="fas fa-arrow-left text-xs"></i> Back
        </a>
    </form>
</div>

<div class="grid grid-cols-3 gap-4">
    @foreach([
        ['Total Collected','GH₵ '.number_format($monthTotal,2),'from-emerald-500 to-teal-600'],
        ['Days with Payments',$daysWithPayment,'from-indigo-500 to-violet-600'],
        ['Avg per Day','GH₵ '.number_format($daysWithPayment>0?$monthTotal/$daysWithPayment:0,2),'from-amber-500 to-orange-500'],
    ] as [$l,$v,$g])
    <div class="rounded-2xl bg-gradient-to-br {{ $g }} p-5 text-white shadow-sm">
        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">{{ $l }}</p>
        <p class="text-2xl font-extrabold">{{ $v }}</p>
    </div>
    @endforeach
</div>

<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">{{ \Carbon\Carbon::createFromDate($year,$mon,1)->format('F Y') }} — Daily Breakdown</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Date</th>
                <th class="px-4 py-3 text-center">Students Paid</th>
                <th class="px-4 py-3 text-right">Total Collected</th>
                <th class="px-4 py-3 text-center">Action</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($dailyTotals as $row)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ \Carbon\Carbon::parse($row->date)->format('D, M d Y') }}</td>
                    <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $row->students_paid }}</td>
                    <td class="px-4 py-3 text-right font-extrabold text-emerald-700">GH₵ {{ number_format($row->total,2) }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.daily-fees.report', ['date'=>$row->date]) }}"
                           class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors">
                            View →
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-10 text-sm text-slate-400">No payments recorded this month.</td></tr>
                @endforelse
            </tbody>
            @if($dailyTotals->isNotEmpty())
            <tfoot><tr class="border-t-2 border-slate-200 bg-slate-50">
                <td class="px-5 py-3 font-extrabold text-slate-800">Total</td>
                <td class="px-4 py-3 text-center font-bold text-slate-700">—</td>
                <td class="px-4 py-3 text-right font-extrabold text-emerald-700 text-base">GH₵ {{ number_format($monthTotal,2) }}</td>
                <td></td>
            </tr></tfoot>
            @endif
        </table>
    </div>
</div>

</div>
@endsection
