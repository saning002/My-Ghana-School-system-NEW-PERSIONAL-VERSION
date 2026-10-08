@extends('layouts.app')
@section('title','Financial Report')
@section('subtitle','Fees, payments and outstanding balances')

@push('styles')
<style>
@media print {
    nav, aside, .no-print, form, .pagination { display: none !important; }
    body { font-size: 11px; }
}
</style>
@endpush

@section('content')

{{-- Global Summary --}}
<div class="grid grid-cols-3 gap-3 mb-5 no-print">
    <div class="card p-4 text-center" style="border-top:3px solid #D4A017">
        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase">Total Billed</p>
        <p class="text-xl font-bold" style="color:#78520a">GH₵ {{ number_format($global['billed'],2) }}</p>
    </div>
    <div class="card p-4 text-center" style="border-top:3px solid #16a34a">
        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase">Collected</p>
        <p class="text-xl font-bold text-green-700">GH₵ {{ number_format($global['collected'],2) }}</p>
    </div>
    <div class="card p-4 text-center" style="border-top:3px solid #dc2626">
        <p class="text-xs text-gray-500 mb-1 font-semibold uppercase">Outstanding</p>
        <p class="text-xl font-bold text-red-600">GH₵ {{ number_format($global['outstanding'],2) }}</p>
    </div>
</div>

{{-- Filters + Action Buttons --}}
<div class="card p-4 mb-5 no-print">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-32">
            <label class="block text-xs font-semibold text-gray-500 mb-1.5">{{ $programLabelSingular }}</label>
            <select name="program_id" class="w-full px-3 py-2.5 border border-yellow-200 rounded-xl text-sm bg-white focus:outline-none focus:ring-2">
                <option value="">All {{ $programLabel }}</option>
                @foreach($programs as $p)
                <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="submit" class="btn-gold px-4 py-2.5 rounded-xl text-sm font-semibold"><i class="fas fa-filter mr-1"></i>Filter</button>
            <a href="{{ route('admin.reports.financial') }}" class="px-4 py-2.5 bg-gray-100 text-gray-600 rounded-xl text-sm font-semibold"><i class="fas fa-times"></i></a>
            <a href="{{ route('admin.reports.financial.preview', request()->query()) }}" target="_blank"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">
                <i class="fas fa-eye"></i> Preview
            </a>
            <a href="{{ route('admin.reports.financial.pdf', request()->query()) }}"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-red-50 text-red-700 hover:bg-red-100 {{ $studentFees->isEmpty() ? 'opacity-50 pointer-events-none' : '' }}">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <a href="{{ route('admin.reports.financial.excel', request()->query()) }}"
               class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold bg-green-50 text-green-700 hover:bg-green-100 {{ $studentFees->isEmpty() ? 'opacity-50 pointer-events-none' : '' }}">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <button type="button" onclick="window.print()" class="flex items-center gap-1.5 px-4 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:#D4A017">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </form>
</div>

{{-- Table --}}
<div class="card overflow-hidden">
    <div class="table-wrap">
        <table class="w-full">
            <thead>
                <tr style="background:#fef9c3">
                    <th class="text-left px-5 py-4 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Student</th>
                    <th class="text-left px-5 py-4 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">{{ $programLabelSingular }}</th>
                    <th class="text-right px-5 py-4 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Total Fees</th>
                    <th class="text-right px-5 py-4 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Paid</th>
                    <th class="text-right px-5 py-4 text-xs font-semibold uppercase tracking-wider" style="color:#78520a">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-yellow-50">
                @forelse($studentFees as $row)
                <tr class="hover:bg-yellow-50/30 transition-colors">
                    <td class="px-5 py-4">
                        <p class="text-sm font-semibold text-ink">{{ $row['student']->user->full_name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $row['student']->student_id }}</p>
                    </td>
                    <td class="px-5 py-4 text-sm text-gray-600">{{ $row['student']->program->name??'—' }}</td>
                    <td class="px-5 py-4 text-sm font-semibold text-right" style="color:#78520a">GH₵ {{ number_format($row['total'],2) }}</td>
                    <td class="px-5 py-4 text-sm font-semibold text-right text-green-700">GH₵ {{ number_format($row['paid'],2) }}</td>
                    <td class="px-5 py-4 text-sm font-bold text-right {{ $row['balance']>0?'text-red-600':'text-green-600' }}">GH₵ {{ number_format($row['balance'],2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-12 text-center text-gray-400 text-sm">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($students->hasPages())
    <div class="px-5 py-4 border-t border-yellow-50">{{ $students->links() }}</div>
    @endif
</div>
@endsection
