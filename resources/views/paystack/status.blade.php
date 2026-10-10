<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if($status === 'success') Payment Successful
        @elseif($status === 'failed') Payment Failed
        @else Payment Pending
        @endif
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .status-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        /* Header bands */
        .band-success { background: linear-gradient(135deg, #10b981, #059669); }
        .band-failed  { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .band-pending { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .band {
            padding: 2.5rem 2rem;
            text-align: center;
            color: #fff;
        }
        .band .icon-circle {
            width: 72px; height: 72px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem;
        }
        .band h2  { font-weight: 800; margin: 0; font-size: 1.5rem; }
        .band p   { opacity: 0.85; margin: 0.5rem 0 0; font-size: 0.9rem; }

        .card-body { padding: 2rem; }

        /* Transaction details table */
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.6rem 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.875rem;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-row .label  { color: #94a3b8; }
        .detail-row .value  { font-weight: 600; color: #1e293b; text-align: right; max-width: 60%; word-break: break-word; }

        /* Amount highlight */
        .amount-highlight {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.25rem;
            text-align: center;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .amount-highlight .label { font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; }
        .amount-highlight .amount { font-size: 2rem; font-weight: 800; color: #10b981; line-height: 1.2; }
        .amount-highlight .amount.failed { color: #ef4444; }

        /* Action buttons */
        .btn-success-action {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff; border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .btn-success-action:hover { opacity: 0.9; color: #fff; }

        .btn-retry {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff; border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-retry:hover { opacity: 0.9; color: #fff; }

        .btn-outline-go {
            border: 2px solid #e2e8f0;
            color: #64748b;
            padding: 0.7rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s;
        }
        .btn-outline-go:hover { border-color: #94a3b8; color: #1e293b; }

        .paystack-footer {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid #f1f5f9;
            font-size: 0.75rem;
            color: #94a3b8;
        }

        /* Confetti for success */
        @keyframes float-up {
            0%   { transform: translateY(0) rotate(0deg); opacity: 1; }
            100% { transform: translateY(-100px) rotate(360deg); opacity: 0; }
        }
        .confetti-piece {
            position: fixed;
            width: 8px; height: 8px;
            border-radius: 2px;
            animation: float-up 2s ease-out forwards;
        }
    </style>
</head>
<body>

<div class="status-card">

    {{-- Status band --}}
    <div class="band band-{{ $status }}">
        <div class="icon-circle">
            @if($status === 'success')
                <i class="fas fa-check"></i>
            @elseif($status === 'failed')
                <i class="fas fa-times"></i>
            @else
                <i class="fas fa-clock"></i>
            @endif
        </div>
        <h2>
            @if($status === 'success') Payment Successful!
            @elseif($status === 'failed') Payment Failed
            @else Payment Pending
            @endif
        </h2>
        <p>{{ $message ?? '' }}</p>
    </div>

    <div class="card-body">

        {{-- Transaction details (if available) --}}
        @if(isset($transaction) && $transaction)

            <div class="amount-highlight">
                <div class="label">Amount {{ $status === 'success' ? 'Paid' : 'Attempted' }}</div>
                <div class="amount {{ $status !== 'success' ? 'failed' : '' }}">
                    GHS {{ number_format($transaction->amount, 2) }}
                </div>
            </div>

            <div class="mb-4">
                <div class="detail-row">
                    <span class="label">Reference</span>
                    <span class="value"><code style="font-size:0.8rem;">{{ $transaction->reference }}</code></span>
                </div>
                @if($transaction->type === 'subscription')
                <div class="detail-row">
                    <span class="label">School</span>
                    <span class="value">{{ $transaction->tenant?->name ?? '—' }}</span>
                </div>
                <div class="detail-row">
                    <span class="label">Plan Period</span>
                    <span class="value">{{ $transaction->months_paid }} month{{ $transaction->months_paid > 1 ? 's' : '' }}</span>
                </div>
                @if($status === 'success' && $transaction->tenant?->subscription_ends_at)
                <div class="detail-row">
                    <span class="label">Access Valid Until</span>
                    <span class="value" style="color:#10b981;">
                        {{ $transaction->tenant->subscription_ends_at->format('d M Y') }}
                    </span>
                </div>
                @endif
                @else
                <div class="detail-row">
                    <span class="label">Student</span>
                    <span class="value">{{ $transaction->student_name ?? '—' }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Email</span>
                    <span class="value">{{ $transaction->payer_email }}</span>
                </div>
                @if($transaction->channel)
                <div class="detail-row">
                    <span class="label">Payment Method</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $transaction->channel)) }}</span>
                </div>
                @endif
                @if($transaction->paid_at)
                <div class="detail-row">
                    <span class="label">Date & Time</span>
                    <span class="value">{{ $transaction->paid_at->format('d M Y, H:i') }}</span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="label">Status</span>
                    <span class="value">
                        @if($status === 'success')
                            <span style="color:#10b981;"><i class="fas fa-check-circle me-1"></i>Confirmed</span>
                        @elseif($status === 'failed')
                            <span style="color:#ef4444;"><i class="fas fa-times-circle me-1"></i>Failed</span>
                        @else
                            <span style="color:#f59e0b;"><i class="fas fa-clock me-1"></i>Pending</span>
                        @endif
                    </span>
                </div>
            </div>

        @endif

        {{-- Action buttons --}}
        <div class="d-flex gap-2 flex-wrap justify-content-center">

            @if($status === 'success')
                @if(($type ?? '') === 'subscription')
                    {{-- School admin: go back to their login --}}
                    @if(isset($transaction) && $transaction->tenant)
                        <a href="{{ url('/school/' . $transaction->tenant->slug . '/login') }}" class="btn-success-action">
                            <i class="fas fa-sign-in-alt me-1"></i> Go to School Login
                        </a>
                    @endif
                @else
                    {{-- Student: go back to portal --}}
                    <a href="{{ route('portal.dashboard') }}" class="btn-success-action">
                        <i class="fas fa-home me-1"></i> Back to Dashboard
                    </a>
                @endif

                {{-- Print / Save --}}
                <button onclick="window.print()" class="btn-outline-go">
                    <i class="fas fa-print me-1"></i> Print Receipt
                </button>

            @elseif($status === 'failed')
                {{-- Retry --}}
                @if(isset($transaction))
                    @if(($type ?? '') === 'subscription' && $transaction->tenant)
                        <a href="{{ url('/owner/schools/' . $transaction->tenant->id . '/pay') }}" class="btn-retry">
                            <i class="fas fa-redo me-1"></i> Try Again
                        </a>
                    @else
                        <a href="{{ route('portal.fee.pay') }}" class="btn-retry">
                            <i class="fas fa-redo me-1"></i> Try Again
                        </a>
                    @endif
                @endif
                <a href="javascript:history.back()" class="btn-outline-go">Go Back</a>

            @else
                {{-- Pending --}}
                <a href="{{ route('portal.dashboard') }}" class="btn-outline-go">
                    <i class="fas fa-home me-1"></i> Back to Dashboard
                </a>
            @endif

        </div>

        @if($status === 'success')
        <div class="text-center mt-3" style="font-size:0.8rem;color:#94a3b8;">
            A confirmation has been sent to your email address.
        </div>
        @endif

    </div>

    <div class="paystack-footer">
        <i class="fas fa-shield-alt me-1"></i>
        Secured by Paystack &nbsp;·&nbsp; Card &nbsp;·&nbsp; Mobile Money &nbsp;·&nbsp; Bank Transfer
    </div>
</div>

@if($status === 'success')
<script>
// Simple confetti burst on success
const colors = ['#10b981','#3b82f6','#f59e0b','#e94560','#8b5cf6'];
for (let i = 0; i < 30; i++) {
    const el = document.createElement('div');
    el.className = 'confetti-piece';
    el.style.cssText = `
        left: ${Math.random() * 100}vw;
        top: ${Math.random() * 60 + 20}vh;
        background: ${colors[Math.floor(Math.random() * colors.length)]};
        animation-delay: ${Math.random() * 1}s;
        animation-duration: ${1.5 + Math.random()}s;
    `;
    document.body.appendChild(el);
    setTimeout(() => el.remove(), 3000);
}
</script>
@endif

</body>
</html>
