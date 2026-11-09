@extends('owner.layout')
@section('title', 'Per-Student Invoices')
@section('page-title', 'Per-Student Invoice Overview')

@section('topbar-actions')
    <a href="{{ route('owner.dashboard') }}" class="btn btn-sm btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
    </a>
@endsection

@section('content')

{{-- ── Stats cards ─────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#eff6ff;color:#3b82f6;"><i class="fas fa-file-invoice"></i></div>
            </div>
            <div class="stat-value">{{ $stats['total'] }}</div>
            <div class="stat-label">Total Invoices</div>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#dcfce7;color:#16a34a;"><i class="fas fa-check-circle"></i></div>
            </div>
            <div class="stat-value">{{ $stats['paid'] }}</div>
            <div class="stat-label">Paid</div>
            <small class="text-success">GHS {{ number_format($stats['total_amount_paid'], 2) }}</small>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#fef3c7;color:#d97706;"><i class="fas fa-clock"></i></div>
            </div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
            <div class="stat-label">Pending</div>
            <small class="text-warning">GHS {{ number_format($stats['total_amount_pending'], 2) }}</small>
        </div>
    </div>
    <div class="col-6 col-xl-3">
        <div class="stat-card">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="stat-icon" style="background:#fef2f2;color:#dc2626;"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
            <div class="stat-value">{{ $stats['overdue'] }}</div>
            <div class="stat-label">Overdue</div>
        </div>
    </div>
</div>

{{-- ── Filters ─────────────────────────────────────────────────────────── --}}
<div class="owner-card mb-4">
    <div class="card-body">
        <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
            <div>
                <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;color:#64748b;">Status</label>
                <select name="status" class="form-select form-select-sm" style="width:auto;">
                    <option value="">All Statuses</option>
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="paid" {{ $status === 'paid' ? 'selected' : '' }}>Paid</option>
                    <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Overdue</option>
                </select>
            </div>
            <div>
                <label class="form-label mb-1" style="font-size:.75rem;font-weight:600;color:#64748b;">School</label>
                <select name="school_id" class="form-select form-select-sm" style="width:auto;">
                    <option value="">All Schools</option>
                    @foreach($perStudentSchools as $school)
                    <option value="{{ $school->id }}" {{ (string)$schoolId === (string)$school->id ? 'selected' : '' }}>
                        {{ $school->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-sm btn-owner-primary align-self-end">
                <i class="fas fa-filter me-1"></i> Filter
            </button>
            <a href="{{ route('owner.invoices.index') }}" class="btn btn-sm btn-outline-secondary align-self-end">
                <i class="fas fa-redo"></i> Reset
            </a>
        </form>
    </div>
</div>

{{-- ── Invoices table ──────────────────────────────────────────────────── --}}
<div class="owner-card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0 fw-bold">
            <i class="fas fa-file-invoice-dollar me-1"></i> Per-Student Invoices
        </h5>
        <span class="badge bg-secondary">{{ $invoices->total() }} total</span>
    </div>
    <div class="owner-table">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Invoice #</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">School</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Term</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Students</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Rate</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Amount</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Due Date</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Status</th>
                    <th style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;color:#94a3b8;">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                @php
                    $isOverdue = $inv->status === 'pending' && $inv->due_date && $inv->due_date < now()->toDateString();
                @endphp
                <tr>
                    <td>
                        <a href="{{ route('owner.schools.show', $inv->tenant_id) }}" style="font-weight:600;color:#1e293b;text-decoration:none;font-size:.82rem;">
                            {{ $inv->invoice_number }}
                        </a>
                    </td>
                    <td style="font-weight:600;font-size:.82rem;">
                        {{ $inv->tenant?->name ?? '—' }}
                    </td>
                    <td style="font-size:.78rem;color:#64748b;">{{ $inv->term_label ?? '—' }}</td>
                    <td style="font-size:.82rem;">{{ number_format($inv->student_count) }}</td>
                    <td style="font-size:.82rem;">GHS {{ number_format($inv->rate_per_student, 2) }}</td>
                    <td style="font-weight:700;font-size:.85rem;">
                        GHS {{ number_format($inv->amount, 2) }}
                    </td>
                    <td style="font-size:.78rem;color:{{ $isOverdue ? '#dc2626' : '#64748b' }};font-weight:{{ $isOverdue ? '700' : '400' }};">
                        {{ $inv->due_date?->format('d M Y') ?? '—' }}
                    </td>
                    <td>
                        @if($inv->status === 'paid')
                        <span class="badge bg-success" style="font-size:.65rem;">Paid</span>
                        @elseif($isOverdue)
                        <span class="badge bg-danger" style="font-size:.65rem;">Overdue</span>
                        @elseif($inv->status === 'pending')
                        <span class="badge bg-warning text-dark" style="font-size:.65rem;">Pending</span>
                        @else
                        <span class="badge bg-secondary" style="font-size:.65rem;">{{ ucfirst($inv->status) }}</span>
                        @endif
                    </td>
                    <td style="font-size:.72rem;color:#94a3b8;">{{ $inv->created_at->format('d M Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5">
                        <i class="fas fa-file-invoice text-muted mb-2" style="font-size:2rem;opacity:.3;"></i>
                        <p class="text-muted mb-0">No invoices found.</p>
                        <p class="text-muted" style="font-size:.75rem;">Invoices are generated when schools on per-student billing click "Generate Invoice" on their subscription page, or automatically by the daily cron job.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($invoices->hasPages())
<div class="d-flex justify-content-center mt-4">
    {{ $invoices->links() }}
</div>
@endif

@endsection
