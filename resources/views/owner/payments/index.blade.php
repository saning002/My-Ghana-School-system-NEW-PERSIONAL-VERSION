@extends('owner.layout')
@section('title','All Payments')
@section('page-title','All Payments')
@section('topbar-actions')
    <a href="{{ route('owner.payments.create') }}" class="btn btn-sm btn-owner-primary">
        <i class="fas fa-plus me-1"></i> Record Payment
    </a>
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#dcfce7;color:#16a34a;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                <i class="fas fa-coins"></i>
            </div>
            <div class="stat-value" style="font-size:1.3rem;">GHS {{ number_format($grandTotal, 2) }}</div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#dbeafe;color:#2563eb;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div class="stat-value" style="font-size:1.3rem;">GHS {{ number_format($totalAmount, 2) }}</div>
            <div class="stat-label">Subscriptions</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#fef3c7;color:#d97706;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                <i class="fas fa-sms"></i>
            </div>
            <div class="stat-value" style="font-size:1.3rem;">GHS {{ number_format($msgTotalAmount, 2) }}</div>
            <div class="stat-label">Messaging Credits</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card text-center">
            <div class="stat-icon mx-auto mb-2" style="background:#f3e8ff;color:#7e22ce;width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;">
                <i class="fas fa-receipt"></i>
            </div>
            <div class="stat-value" style="font-size:1.3rem;">{{ $payments->total() + $msgPayments->count() }}</div>
            <div class="stat-label">Total Transactions</div>
        </div>
    </div>
</div>

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
                @foreach(['cash','mobile_money','bank_transfer','card','paystack','other'] as $m)
                    <option value="{{ $m }}" {{ request('method') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                @endforeach
            </select>
            <input type="date" name="from" class="form-control form-control-sm" style="max-width:150px;" value="{{ request('from') }}">
            <input type="date" name="to"   class="form-control form-control-sm" style="max-width:150px;" value="{{ request('to') }}">
            <button class="btn btn-sm btn-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
            <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </form>
    </div>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="paymentTabs">
    <li class="nav-item">
        <a class="nav-link active" data-bs-toggle="tab" href="#tab-subscriptions">
            <i class="fas fa-shield-alt me-1"></i> Subscriptions
            <span class="badge bg-secondary ms-1">{{ $payments->total() }}</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-bs-toggle="tab" href="#tab-messaging">
            <i class="fas fa-sms me-1"></i> Messaging Credits
            <span class="badge bg-secondary ms-1">{{ $msgPayments->count() }}</span>
        </a>
    </li>
</ul>

<div class="tab-content">

    {{-- Subscription Payments --}}
    <div class="tab-pane fade show active" id="tab-subscriptions">
        <div class="owner-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>School</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Description</th>
                        <th>Months</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($payments as $p)
                <tr>
                    <td>
                        <code style="font-size:0.8rem;">{{ $p->receipt_number }}</code>
                        @if($p->paid_online ?? false)
                            <span class="badge ms-1" style="background:#eff6ff;color:#2563eb;font-size:0.65rem;">Online</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('owner.schools.show', $p->tenant_id) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">
                            {{ $p->tenant->name ?? '—' }}
                        </a>
                    </td>
                    <td>{{ $p->payment_date->format('d M Y') }}</td>
                    <td><strong style="color:#16a34a;">GHS {{ number_format($p->amount, 2) }}</strong></td>
                    <td>
                        <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                            {{ ucfirst(str_replace('_',' ',$p->payment_method)) }}
                        </span>
                    </td>
                    <td style="font-size:0.85rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $p->description }}">
                        {{ $p->description ?? '—' }}
                    </td>
                    <td>{{ $p->months_paid }}mo</td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <a href="{{ route('owner.payments.receipt', $p) }}" class="btn btn-sm btn-outline-secondary" title="Receipt">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            <form method="POST" action="{{ route('owner.payments.email-receipt', $p) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-info" title="Email Receipt">
                                    <i class="fas fa-envelope"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('owner.payments.destroy', $p) }}" onsubmit="return confirm('Delete this payment?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-4">No subscription payments found.</td></tr>
                @endforelse
                </tbody>
                @if($payments->count())
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="3" class="text-end fw-bold" style="font-size:0.85rem;">Subtotal:</td>
                        <td colspan="5"><strong style="color:#16a34a;">GHS {{ number_format($totalAmount, 2) }}</strong></td>
                    </tr>
                </tfoot>
                @endif
            </table>
            @if($payments->hasPages())
            <div class="px-3 py-2">{{ $payments->links() }}</div>
            @endif
        </div>
    </div>

    {{-- Messaging Credit Payments --}}
    <div class="tab-pane fade" id="tab-messaging">
        <div class="owner-table">
            <table class="table">
                <thead>
                    <tr>
                        <th>Receipt #</th>
                        <th>School</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>SMS Credits</th>
                        <th>WA Credits</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($msgPayments as $p)
                <tr>
                    <td>
                        <code style="font-size:0.8rem;">{{ $p->receipt_number }}</code>
                        @if($p->payment_method === 'paystack')
                            <span class="badge ms-1" style="background:#eff6ff;color:#2563eb;font-size:0.65rem;">Online</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('owner.schools.show', $p->tenant_id) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">
                            {{ $p->tenant->name ?? '—' }}
                        </a>
                    </td>
                    <td>{{ $p->created_at->format('d M Y') }}</td>
                    <td><strong style="color:#16a34a;">GHS {{ number_format($p->amount, 2) }}</strong></td>
                    <td>
                        <span style="color:#2563eb;font-weight:700;">
                            +{{ number_format($p->sms_credits_added) }}
                        </span>
                    </td>
                    <td>
                        <span style="color:#16a34a;font-weight:700;">
                            +{{ number_format($p->whatsapp_credits_added) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge bg-light text-dark border" style="font-size:0.75rem;">
                            {{ ucfirst(str_replace('_',' ',$p->payment_method)) }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $p->status === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}" style="font-size:0.72rem;">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('owner.messaging.receipt', $p) }}" class="btn btn-sm btn-outline-secondary" title="Receipt">
                            <i class="fas fa-receipt"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No messaging credit payments found.</td></tr>
                @endforelse
                </tbody>
                @if($msgPayments->count())
                <tfoot>
                    <tr style="background:#f8fafc;">
                        <td colspan="3" class="text-end fw-bold" style="font-size:0.85rem;">Subtotal:</td>
                        <td colspan="6"><strong style="color:#16a34a;">GHS {{ number_format($msgTotalAmount, 2) }}</strong></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

{{-- Grand Total --}}
<div class="owner-card mt-3">
    <div class="card-body py-3 d-flex justify-content-between align-items-center">
        <div style="font-size:0.85rem;color:#64748b;">
            <i class="fas fa-info-circle me-1"></i>
            Showing {{ $payments->count() }} subscription + {{ $msgPayments->count() }} messaging credit payments
            @if(request('from') || request('to'))
                for selected date range
            @endif
        </div>
        <div style="font-size:1.1rem;font-weight:800;color:#1e293b;">
            Grand Total: <span style="color:#16a34a;">GHS {{ number_format($grandTotal, 2) }}</span>
        </div>
    </div>
</div>

@endsection
