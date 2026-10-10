<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pay Subscription — {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0f3460, #16213e); min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; padding: 1rem; }
        .card { border: none; border-radius: 20px; box-shadow: 0 25px 60px rgba(0,0,0,0.4); max-width: 480px; width: 100%; overflow: hidden; }
        .card-header-custom { background: linear-gradient(135deg, #e94560, #c62a47); padding: 2rem; color: #fff; text-align: center; }
        .card-header-custom h3 { font-weight: 800; margin: 0; }
        .card-header-custom p { opacity: 0.85; margin: 0.4rem 0 0; font-size: 0.9rem; }
        .card-body { padding: 2rem; }
        .plan-box { background: #f8fafc; border-radius: 12px; padding: 1.25rem; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; }
        .plan-name { font-weight: 700; color: #1e293b; font-size: 1.1rem; }
        .plan-price { font-size: 2rem; font-weight: 800; color: #e94560; }
        .plan-cycle { font-size: 0.8rem; color: #94a3b8; }
        .total-box { background: linear-gradient(135deg, #e94560, #c62a47); color: #fff; border-radius: 12px; padding: 1rem 1.25rem; margin: 1.25rem 0; display: flex; justify-content: space-between; align-items: center; }
        .total-box .label { font-size: 0.85rem; opacity: 0.85; }
        .total-box .value { font-size: 1.5rem; font-weight: 800; }
        .btn-pay { background: #0ba360; background: linear-gradient(135deg, #0ba360, #3cba92); border: none; color: #fff; padding: 0.85rem; border-radius: 12px; font-weight: 700; font-size: 1rem; width: 100%; transition: opacity 0.2s; }
        .btn-pay:hover { opacity: 0.9; color: #fff; }
        .paystack-badge { text-align: center; margin-top: 1rem; }
        .paystack-badge img { height: 28px; opacity: 0.6; }
        .form-label { font-weight: 600; color: #475569; font-size: 0.875rem; }
    </style>
</head>
<body>
<div class="card">
    <div class="card-header-custom">
        <div style="font-size:2.5rem;margin-bottom:0.5rem;"><i class="fas fa-shield-alt"></i></div>
        <h3>Pay Subscription</h3>
        <p>{{ $tenant->name }}</p>
    </div>
    <div class="card-body">

        @if(session('error'))
        <div class="alert alert-danger" style="border-radius:10px;font-size:0.875rem;">{{ session('error') }}</div>
        @endif

        @if($plan)
        <div class="plan-box">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="plan-name">{{ $plan->name }} Plan</div>
                    <div class="plan-cycle">Billed {{ $plan->billing_cycle }}</div>
                </div>
                <div class="text-end">
                    <div class="plan-price">GHS {{ number_format($plan->price, 2) }}</div>
                    <div class="plan-cycle">per month</div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-warning" style="font-size:0.875rem;border-radius:10px;">
            No plan assigned to this school yet. Contact the platform owner.
        </div>
        @endif

        <form method="POST" action="{{ route('paystack.subscription.initialize', $tenant) }}" id="payForm">
            @csrf

            <div class="mb-3">
                <label class="form-label">Your Email Address <span class="text-danger">*</span></label>
                <input type="email" name="payer_email" class="form-control @error('payer_email') is-invalid @enderror"
                    value="{{ old('payer_email', $tenant->admin_email) }}" required>
                @error('payer_email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" name="payer_name" class="form-control"
                    value="{{ old('payer_name', $tenant->admin_name) }}">
            </div>

            <div class="mb-3">
                <label class="form-label">Number of Months <span class="text-danger">*</span></label>
                <select name="months" class="form-select" id="monthsSelect" required>
                    @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ $i == 1 ? 'selected' : '' }}>
                        {{ $i }} month{{ $i > 1 ? 's' : '' }}
                        @if($plan) — GHS {{ number_format($plan->price * $i, 2) }} @endif
                    </option>
                    @endfor
                </select>
            </div>

            @if($plan)
            <div class="total-box">
                <div>
                    <div class="label">Total to Pay</div>
                    <div style="font-size:0.75rem;opacity:0.7;" id="periodLabel">1 month</div>
                </div>
                <div class="value" id="totalAmount">GHS {{ number_format($plan->price, 2) }}</div>
            </div>
            @endif

            <button type="submit" class="btn-pay" {{ !$plan ? 'disabled' : '' }}>
                <i class="fas fa-lock me-2"></i> Pay Securely with Paystack
            </button>
        </form>

        <div class="paystack-badge">
            <small style="color:#94a3b8;font-size:0.75rem;">Secured by Paystack · Card · Mobile Money · Bank Transfer</small>
        </div>
    </div>
</div>

@if($plan)
<script>
const price = {{ $plan->price }};
document.getElementById('monthsSelect').addEventListener('change', function() {
    const months = parseInt(this.value);
    const total  = price * months;
    document.getElementById('totalAmount').textContent = 'GHS ' + total.toLocaleString('en-GH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('periodLabel').textContent  = months + ' month' + (months > 1 ? 's' : '');
});
</script>
@endif
</body>
</html>
