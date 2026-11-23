@extends('layouts.app')
@section('title','Payment Records')
@section('subtitle','View, filter and print all fee payment transactions')

@section('content')

<div class="page-hero-banner mb-5">
    <div class="hero-left">
        <h1><i class="fas fa-receipt mr-2" style="color:var(--brand)"></i>Payment Records</h1>
        <p>All fee payment transactions — filter by student, programme or date range</p>
    </div>
    <div class="hero-actions">
        {{-- Print current filter --}}
        <a href="{{ route('admin.fees.payment-records.print', request()->query()) }}" target="_blank"
            class="btn-secondary-action">
            <i class="fas fa-print"></i> Print
        </a>
        <a href="{{ route('admin.fees.index') }}" class="btn-secondary-action">
            <i class="fas fa-arrow-left"></i> Back to Fees
        </a>
    </div>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="flex items-center gap-3 px-5 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm font-semibold mb-5">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif

{{-- Filters --}}
<form method="GET" class="card p-5 mb-5">
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
        {{-- Programme --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Programme</label>
            <select name="program_id" class="form-input">
                <option value="">All Programmes</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        {{-- Student --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Student</label>
            <select name="student_id" class="form-input">
                <option value="">All Students</option>
                @foreach($students as $st)
                <option value="{{ $st->id }}" {{ request('student_id') == $st->id ? 'selected' : '' }}>
                    {{ $st->user?->full_name }} — {{ $st->student_id }}
                </option>
                @endforeach
            </select>
        </div>

        {{-- From --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">From Date</label>
            <input type="date" name="from" value="{{ request('from') }}" class="form-input">
        </div>

        {{-- To --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">To Date</label>
            <input type="date" name="to" value="{{ request('to') }}" class="form-input">
        </div>

        {{-- Method + buttons --}}
        <div>
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-500 mb-1">Method</label>
            <div class="flex gap-2">
                <select name="method" class="form-input flex-1">
                    <option value="">All Methods</option>
                    @foreach(['cash','bank_transfer','mobile_money','paystack','cheque','other'] as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ ucwords(str_replace('_',' ',$m)) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn-primary-action shrink-0"><i class="fas fa-filter"></i></button>
                <a href="{{ route('admin.fees.payment-records') }}" class="btn-secondary-action shrink-0"><i class="fas fa-times"></i></a>
            </div>
        </div>
    </div>
</form>

{{-- Summary strip --}}
<div class="flex flex-wrap gap-4 mb-4 text-sm">
    <div class="flex items-center gap-2 px-4 py-2 bg-green-50 border border-green-200 rounded-xl font-bold text-green-800">
        <i class="fas fa-coins text-green-600"></i>
        Total: GH₵ {{ number_format($total, 2) }}
    </div>
    <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 border border-blue-200 rounded-xl font-semibold text-blue-700">
        <i class="fas fa-list text-blue-500"></i>
        {{ $payments->total() }} payment{{ $payments->total() !== 1 ? 's' : '' }}
    </div>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-200 text-xs font-bold uppercase tracking-wide text-slate-500">
                    <th class="px-5 py-3 text-left">Date &amp; Time</th>
                    <th class="px-5 py-3 text-left">Student</th>
                    <th class="px-5 py-3 text-left">Programme</th>
                    <th class="px-5 py-3 text-right">Amount</th>
                    <th class="px-5 py-3 text-left">Method</th>
                    <th class="px-5 py-3 text-left">Receipt #</th>
                    <th class="px-5 py-3 text-left">Notes</th>
                    <th class="px-4 py-3 text-center">Print</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($payments as $pmt)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-5 py-3 whitespace-nowrap">
                        <div class="font-semibold text-slate-800">{{ \Carbon\Carbon::parse($pmt->payment_date)->format('d M Y') }}</div>
                        <div class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($pmt->created_at)->format('h:i A') }}</div>
                    </td>
                    <td class="px-5 py-3">
                        <div class="font-bold text-slate-800">{{ $pmt->student?->user?->full_name ?? '—' }}</div>
                        <div class="text-xs text-slate-400 font-mono">{{ $pmt->student?->student_id ?? '' }}</div>
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $pmt->student?->program?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-right font-extrabold text-emerald-700">
                        GH₵ {{ number_format($pmt->amount_paid, 2) }}
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-600">
                            {{ ucwords(str_replace('_',' ', $pmt->payment_method ?? 'cash')) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-xs font-mono text-slate-500">{{ $pmt->receipt_number ?? '—' }}</td>
                    <td class="px-5 py-3 text-xs text-slate-400 max-w-[160px] truncate">{{ $pmt->notes ?? '' }}</td>
                    <td class="px-4 py-3 text-center">
                        <a href="{{ route('admin.reports.fee-statement.pdf', ['student_id' => $pmt->student_id]) }}"
                            target="_blank"
                            class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
                            <i class="fas fa-file-pdf text-xs"></i> PDF
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-12 text-center text-slate-400">
                        <i class="fas fa-receipt text-3xl block mb-2 opacity-30"></i>
                        No payment records found for the selected filters.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($payments->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $payments->links() }}
    </div>
    @endif
</div>

@endsection
