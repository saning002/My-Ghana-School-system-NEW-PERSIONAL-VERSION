@extends('owner.layout')
@section('title','Payments')
@section('page-title','Payments')
@section('topbar-actions')
    <a href="{{ route('owner.payments.create') }}" class="btn btn-sm btn-owner-primary"><i class="fas fa-plus me-1"></i> Record Payment</a>
@endsection

@section('content')
{{-- Filters --}}
<div class="owner-card mb-3">
    <div class="card-body py-2">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <select name="tenant_id" class="form-select form-select-sm" style="max-width:200px;">
                <option value="">All Schools</option>
                @foreach($schools as $s)
                    <option value="{{ $s->id }}" {{ request('tenant_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <select name="method" class="form-select form-select-sm" style="max-width:160px;">
                <option value="">All Methods</option>
                @foreach(['cash','mobile_money','bank_transfer','card','other'] as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control form-control-sm" style="max-width:150px;" value="{{ request('from') }}">
            <input type="date" name="to" class="form-control form-control-sm" style="max-width:150px;" value="{{ request('to') }}">
            <button class="btn btn-sm btn-secondary">Filter</button>
            <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

<div class="owner-table">
    <table class="table">
        <thead>
            <tr><th>Receipt #</th><th>School</th><th>Date</th><th>Amount</th><th>Method</th><th>Description</th><th>Months</th><th>Actions</th></tr>
        </thead>
        <tbody>
        @forelse($payments as $p)
        <tr>
            <td><code style="font-size:0.8rem;">{{ $p->receipt_number }}</code></td>
            <td>
                <a href="{{ route('owner.schools.show', $p->tenant_id) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">{{ $p->tenant->name ?? '—' }}</a>
            </td>
            <td>{{ $p->payment_date->format('d M Y') }}</td>
            <td><strong style="color:#16a34a;">{{ $p->currency }} {{ number_format($p->amount, 2) }}</strong></td>
            <td><span class="badge bg-light text-dark border" style="font-size:0.75rem;">{{ ucfirst(str_replace('_',' ',$p->payment_method)) }}</span></td>
            <td style="font-size:0.85rem;">{{ $p->description ?? '—' }}</td>
            <td>{{ $p->months_paid }}mo</td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('owner.payments.receipt', $p) }}" class="btn btn-sm btn-outline-secondary" title="Receipt"><i class="fas fa-file-pdf"></i></a>
                    <form method="POST" action="{{ route('owner.payments.email-receipt', $p) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-info" title="Email Receipt"><i class="fas fa-envelope"></i></button>
                    </form>
                    <form method="POST" action="{{ route('owner.payments.destroy', $p) }}" onsubmit="return confirm('Delete this payment?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                    </form>
                </div>
            </td>
        </tr>
        @empty
        <tr><td colspan="8" class="text-center text-muted py-4">No payments found.</td></tr>
        @endforelse
        </tbody>
    </table>
    @if($payments->hasPages())
    <div class="px-3 py-2">{{ $payments->links() }}</div>
    @endif
</div>
@endsection
