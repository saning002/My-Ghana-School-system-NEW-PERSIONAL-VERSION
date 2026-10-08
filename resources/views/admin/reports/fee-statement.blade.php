@extends('layouts.app')
@section('title','Fee Statement')
@section('subtitle','Charges, payments and outstanding balance per student')

@section('content')
<div class="space-y-5">

<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
    <form method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-4 items-end">
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Program</label>
            <select name="program_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                <option value="">-- Select Program --</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        @if($students->isNotEmpty())
        <div>
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Student</label>
            <select name="student_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                <option value="">-- Select Student --</option>
                @foreach($students as $s)
                <option value="{{ $s->id }}" {{ request('student_id')==$s->id?'selected':'' }}>{{ $s->student_id }} — {{ $s->user->full_name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-bold py-2.5 px-5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Load Statement
        </button>
    </form>
</div>

@if($student && $fees)
{{-- Statement Header --}}
<div class="rounded-2xl border border-amber-100 bg-gradient-to-r from-amber-50 to-orange-50 p-6 flex flex-col sm:flex-row sm:items-center gap-5">
    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-600 text-white text-xl font-extrabold shadow">
        {{ strtoupper(substr($student->user->full_name??'?',0,1)) }}
    </div>
    <div class="flex-1">
        <h2 class="text-lg font-extrabold text-slate-900">{{ $student->user->full_name }}</h2>
        <div class="flex flex-wrap gap-3 mt-1 text-xs text-slate-500 font-semibold">
            <span><i class="fas fa-id-card text-amber-500 mr-1"></i>{{ $student->student_id }}</span>
            <span><i class="fas fa-graduation-cap text-orange-400 mr-1"></i>{{ $student->program?->name }}</span>
        </div>
    </div>
    <a href="{{ route('admin.reports.fee-statement.pdf', ['student_id'=>$student->id]) }}"
       class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95 shrink-0">
        <i class="fas fa-file-pdf"></i> Download PDF
    </a>
</div>

{{-- Balance Summary --}}
<div class="grid grid-cols-3 gap-4">
    @php
    // Normalize fees array for backward compatibility
    $feesDisplay = [
        'billed' => $fees['total'] ?? $fees['billed'] ?? 0,
        'paid'   => $fees['paid'] ?? 0,
        'balance' => $fees['balance'] ?? 0,
    ];
    @endphp
    @foreach([
        ['label'=>'Total Billed', 'val'=>'GH₵ '.number_format($feesDisplay['billed'],2),   'bg'=>'from-blue-500 to-cyan-600'],
        ['label'=>'Total Paid',   'val'=>'GH₵ '.number_format($feesDisplay['paid'],2),     'bg'=>'from-emerald-500 to-teal-600'],
        ['label'=>'Balance',      'val'=>'GH₵ '.number_format($feesDisplay['balance'],2),  'bg'=>($feesDisplay['balance'])>0?'from-red-500 to-rose-600':'from-emerald-500 to-teal-600'],
    ] as $k)
    <div class="rounded-2xl bg-gradient-to-br {{ $k['bg'] }} p-4 text-white shadow-sm text-center">
        <p class="text-[10px] font-bold uppercase tracking-wider text-white/70 mb-1">{{ $k['label'] }}</p>
        <p class="text-xl font-extrabold">{{ $k['val'] }}</p>
    </div>
    @endforeach
</div>

{{-- Payments list --}}
@if(($student->payments??collect())->isNotEmpty())
<div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Payment History</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                    <th class="px-5 py-3 text-left">Date</th>
                    <th class="px-5 py-3 text-left">Description</th>
                    <th class="px-4 py-3 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @foreach($student->payments->sortByDesc('created_at') as $pay)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 text-slate-500 text-xs">{{ \Carbon\Carbon::parse($pay->created_at)->format('M d, Y') }}</td>
                    <td class="px-5 py-3 font-medium text-slate-700">{{ $pay->description ?? 'School Fees Payment' }}</td>
                    <td class="px-4 py-3 text-right font-bold text-emerald-700">GH₵ {{ number_format($pay->amount,2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@else
<div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-8 text-center">
    <p class="text-sm font-semibold text-slate-400">No payments recorded yet.</p>
</div>
@endif
@endif

</div>
@endsection
