@extends('owner.layout')
@section('title','Record Payment')
@section('page-title','Record Payment')
@section('topbar-actions')
    <a href="{{ route('owner.payments.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Back</a>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="owner-card">
    <div class="card-header">New Payment</div>
    <div class="card-body">
    <form method="POST" action="{{ route('owner.payments.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-8">
                <label class="form-label fw-semibold">School <span class="text-danger">*</span></label>
                <select name="tenant_id" class="form-select @error('tenant_id') is-invalid @enderror" required>
                    <option value="">— Select School —</option>
                    @foreach($schools as $s)
                        <option value="{{ $s->id }}" {{ (old('tenant_id', $selectedTenant?->id) == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
                @error('tenant_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Months Paid <span class="text-danger">*</span></label>
                <input type="number" name="months_paid" class="form-control" value="{{ old('months_paid', 1) }}" min="1" required>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount') }}" step="0.01" min="0.01" required placeholder="0.00">
                @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Currency</label>
                <select name="currency" class="form-select">
                    @foreach(['GHS','USD','EUR','GBP'] as $c)
                        <option value="{{ $c }}" {{ old('currency','GHS') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                <select name="payment_method" class="form-select" required>
                    @foreach(['cash','mobile_money','bank_transfer','card','other'] as $m)
                        <option value="{{ $m }}" {{ old('payment_method') === $m ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$m)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Reference / Transaction ID</label>
                <input type="text" name="reference" class="form-control" value="{{ old('reference') }}" placeholder="Bank ref, MoMo ID...">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <input type="text" name="description" class="form-control" value="{{ old('description') }}" placeholder="e.g. Monthly subscription — October 2024">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes (internal only)</label>
                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="alert alert-info mt-3" style="font-size:0.83rem; border-radius:8px;">
            <i class="fas fa-info-circle me-1"></i>
            Saving this payment will automatically extend the school's subscription by the number of months paid and set status to <strong>Active</strong>.
        </div>

        <div class="d-flex gap-2 mt-2">
            <button type="submit" class="btn btn-owner-primary"><i class="fas fa-save me-1"></i> Record Payment & Generate Receipt</button>
            <a href="{{ route('owner.payments.index') }}" class="btn btn-outline-secondary">Cancel</a>
        </div>
    </form>
    </div>
</div>
</div>
</div>
@endsection
