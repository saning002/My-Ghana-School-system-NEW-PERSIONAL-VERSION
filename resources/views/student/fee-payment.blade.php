@extends('student.layout')
@section('title', 'Pay Fees Online')

@section('content')
<div class="container py-4" style="max-width:560px;">

    <div class="card border-0 shadow-sm" style="border-radius:16px;overflow:hidden;">
        {{-- Header --}}
        <div style="background:linear-gradient(135deg,#0f3460,#16213e);padding:1.75rem;color:#fff;">
            <h5 class="fw-bold mb-0"><i class="fas fa-credit-card me-2"></i> Pay Fees Online</h5>
            <p style="opacity:0.7;font-size:0.85rem;margin:0.25rem 0 0;">Secure payment powered by Paystack</p>
        </div>

        <div class="card-body p-4">

            @if(session('error'))
            <div class="alert alert-danger" style="border-radius:10px;font-size:0.875rem;">
                {{ session('error') }}
            </div>
            @endif

            {{-- Fee Summary --}}
            <div style="background:#f8fafc;border-radius:12px;padding:1.25rem;margin-bottom:1.5rem;border:1px solid #e2e8f0;">
                <div style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;color:#94a3b8;margin-bottom:0.75rem;">
                    Your Fee Summary
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#64748b;font-size:0.875rem;">Programme Fees</span>
                    <strong>GHS {{ number_format($feeSummary['program_total'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#64748b;font-size:0.875rem;">Exam Fees</span>
                    <strong>GHS {{ number_format($feeSummary['exam_total'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#64748b;font-size:0.875rem;">Total Billed</span>
                    <strong>GHS {{ number_format($feeSummary['total'], 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="color:#64748b;font-size:0.875rem;">Total Paid</span>
                    <strong style="color:#16a34a;">GHS {{ number_format($feeSummary['paid'], 2) }}</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span style="font-weight:700;color:#1e293b;">Outstanding Balance</span>
                    <span style="font-weight:800;font-size:1.1rem;color:{{ $balance > 0 ? '#e94560' : '#16a34a' }};">
                        GHS {{ number_format($balance, 2) }}
                    </span>
                </div>
            </div>

            @if($balance <= 0)
            <div class="alert alert-success" style="border-radius:10px;font-size:0.875rem;">
                <i class="fas fa-check-circle me-1"></i>
                Your fees are fully paid. No outstanding balance.
            </div>
            @else

            <form method="POST" action="{{ route('portal.fee.pay.initialize') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Amount to Pay (GHS) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text">GHS</span>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                            value="{{ old('amount', number_format($balance, 2, '.', '')) }}"
                            min="1" max="{{ $balance }}" step="0.01" required>
                    </div>
                    <div class="form-text">Maximum: GHS {{ number_format($balance, 2) }} (outstanding balance)</div>
                    @error('amount')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email for Receipt <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $student->user?->email ?? '') }}" required>
                    @error('email')<div class="text-danger" style="font-size:0.8rem;">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Payer Name</label>
                    <input type="text" name="payer_name" class="form-control"
                        value="{{ old('payer_name', $student->user?->full_name ?? '') }}"
                        placeholder="Parent/Guardian or Student name">
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Description (optional)</label>
                    <input type="text" name="description" class="form-control"
                        value="{{ old('description') }}"
                        placeholder="e.g. First semester fees">
                </div>

                {{-- Payment method quick select --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Preferred Payment Method</label>
                    <div class="d-flex gap-2 flex-wrap">
                        @foreach([['card','fas fa-credit-card','Card'],['mobile_money','fas fa-mobile-alt','Mobile Money'],['bank','fas fa-university','Bank Transfer']] as [$val,$icon,$label])
                        <label style="flex:1;min-width:100px;cursor:pointer;">
                            <input type="radio" name="preferred_channel" value="{{ $val }}" class="d-none" {{ $val === 'mobile_money' ? 'checked' : '' }}>
                            <div class="text-center p-2 rounded border channel-btn {{ $val === 'mobile_money' ? 'border-primary bg-primary bg-opacity-10' : '' }}" style="font-size:0.8rem;">
                                <i class="{{ $icon }} mb-1 d-block" style="font-size:1.1rem;"></i>
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                    <div class="form-text">Paystack supports all methods above. Your selection is a preference only.</div>
                </div>

                <button type="submit" class="btn w-100 fw-bold py-2" style="background:linear-gradient(135deg,#0ba360,#3cba92);color:#fff;border-radius:10px;font-size:0.95rem;">
                    <i class="fas fa-lock me-2"></i> Pay GHS <span id="btnAmount">{{ number_format($balance, 2) }}</span> Securely
                </button>
            </form>

            <div class="text-center mt-3" style="font-size:0.75rem;color:#94a3b8;">
                <i class="fas fa-shield-alt me-1"></i>
                Payments are secured and processed by Paystack. Card · Mobile Money · Bank Transfer.
            </div>

            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
// Update button amount as user types
document.querySelector('input[name="amount"]')?.addEventListener('input', function() {
    const val = parseFloat(this.value) || 0;
    document.getElementById('btnAmount').textContent = val.toLocaleString('en-GH', {minimumFractionDigits:2, maximumFractionDigits:2});
});

// Channel button highlight
document.querySelectorAll('input[name="preferred_channel"]').forEach(radio => {
    radio.addEventListener('change', function() {
        document.querySelectorAll('.channel-btn').forEach(btn => {
            btn.classList.remove('border-primary','bg-primary','bg-opacity-10');
        });
        this.nextElementSibling.classList.add('border-primary','bg-primary','bg-opacity-10');
    });
});
</script>
@endpush
@endsection
