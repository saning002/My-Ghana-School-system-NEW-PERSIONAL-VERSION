<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Messaging Credits — {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background:linear-gradient(135deg,#0f3460,#16213e); min-height:100vh; font-family:'Segoe UI',sans-serif; padding:2rem 1rem; }
        .wrapper { max-width:900px; margin:0 auto; }
        .page-title { color:#fff; font-size:1.5rem; font-weight:800; margin-bottom:0.25rem; }
        .page-sub { color:rgba(255,255,255,0.5); font-size:0.875rem; margin-bottom:2rem; }

        /* Balance cards */
        .balance-card { background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.1); border-radius:14px; padding:1.5rem; text-align:center; color:#fff; }
        .balance-card .value { font-size:2.5rem; font-weight:800; }
        .balance-card .label { font-size:0.8rem; opacity:0.6; text-transform:uppercase; letter-spacing:1px; margin-top:4px; }
        .balance-card .low { color:#f59e0b; }
        .balance-card .empty { color:#ef4444; }

        /* Package cards */
        .pkg-card { background:#fff; border-radius:14px; padding:1.5rem; cursor:pointer; transition:all 0.2s; border:2px solid transparent; }
        .pkg-card:hover { border-color:#e94560; transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.15); }
        .pkg-card.selected { border-color:#e94560; background:#fff8f9; }
        .pkg-card .price { font-size:1.75rem; font-weight:800; color:#e94560; }
        .pkg-card .name { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:0.25rem; }
        .pkg-card .desc { font-size:0.8rem; color:#94a3b8; margin-bottom:1rem; }
        .pkg-card .credits { display:flex; gap:1rem; justify-content:center; margin-bottom:1rem; }
        .pkg-card .credit-badge { background:#f8fafc; border-radius:8px; padding:0.5rem 0.75rem; text-align:center; }
        .pkg-card .credit-badge .num { font-weight:800; font-size:1.1rem; }
        .pkg-card .credit-badge .type { font-size:0.7rem; color:#94a3b8; text-transform:uppercase; }

        /* Buy form */
        .buy-form { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:14px; padding:1.5rem; }
        .form-label { color:rgba(255,255,255,0.7); font-size:0.875rem; font-weight:500; }
        .form-control { background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:#fff; border-radius:10px; }
        .form-control:focus { background:rgba(255,255,255,0.12); border-color:#e94560; color:#fff; box-shadow:0 0 0 3px rgba(233,69,96,0.2); }
        .form-control::placeholder { color:rgba(255,255,255,0.25); }
        .btn-pay { background:linear-gradient(135deg,#0ba360,#3cba92); border:none; color:#fff; padding:0.85rem; border-radius:12px; font-weight:700; font-size:1rem; width:100%; }
        .btn-pay:disabled { opacity:0.5; cursor:not-allowed; }
        .btn-pay:hover:not(:disabled) { opacity:0.9; color:#fff; }

        /* History */
        .history-card { background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.08); border-radius:12px; padding:1rem; }
        .history-row { display:flex; justify-content:space-between; align-items:center; padding:0.5rem 0; border-bottom:1px solid rgba(255,255,255,0.05); font-size:0.8rem; color:rgba(255,255,255,0.7); }
        .history-row:last-child { border-bottom:none; }
    </style>
</head>
<body>
<div class="wrapper">
    <div class="page-title">Buy Messaging Credits</div>
    <div class="page-sub">{{ $tenant->name }} — Credits are used for sending fee reminders via SMS and WhatsApp</div>

    {{-- Current Balance --}}
    <div class="row g-3 mb-4">
        <div class="col-6">
            <div class="balance-card">
                <div class="value {{ $credits->sms_credits == 0 ? 'empty' : ($credits->sms_credits < 20 ? 'low' : '') }}">
                    {{ number_format($credits->sms_credits) }}
                </div>
                <div class="label"><i class="fas fa-sms me-1"></i> SMS Credits</div>
                @if($credits->sms_credits == 0)
                    <small style="color:#ef4444;font-size:0.75rem;">No credits — top up to send SMS</small>
                @elseif($credits->sms_credits < 20)
                    <small style="color:#f59e0b;font-size:0.75rem;">Running low</small>
                @endif
            </div>
        </div>
        <div class="col-6">
            <div class="balance-card">
                <div class="value {{ $credits->whatsapp_credits == 0 ? 'empty' : ($credits->whatsapp_credits < 10 ? 'low' : '') }}">
                    {{ number_format($credits->whatsapp_credits) }}
                </div>
                <div class="label"><i class="fab fa-whatsapp me-1"></i> WhatsApp Credits</div>
                @if($credits->whatsapp_credits == 0)
                    <small style="color:#ef4444;font-size:0.75rem;">No credits — top up to send WhatsApp</small>
                @elseif($credits->whatsapp_credits < 10)
                    <small style="color:#f59e0b;font-size:0.75rem;">Running low</small>
                @endif
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Package selector --}}
        <div class="col-lg-7">
            <h6 style="color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1px;font-size:0.75rem;margin-bottom:1rem;">Select a Package</h6>
            <div class="row g-3" id="packageGrid">
                @foreach($packages as $pkg)
                <div class="col-md-6">
                    <div class="pkg-card" onclick="selectPackage({{ $pkg->id }}, {{ $pkg->price }}, {{ $pkg->sms_credits }}, {{ $pkg->whatsapp_credits }}, '{{ addslashes($pkg->name) }}')">
                        <div class="name">{{ $pkg->name }}</div>
                        <div class="desc">{{ $pkg->description }}</div>
                        <div class="credits">
                            <div class="credit-badge">
                                <div class="num" style="color:#3b82f6;">{{ number_format($pkg->sms_credits) }}</div>
                                <div class="type">SMS</div>
                            </div>
                            <div class="credit-badge">
                                <div class="num" style="color:#25d366;">{{ number_format($pkg->whatsapp_credits) }}</div>
                                <div class="type">WhatsApp</div>
                            </div>
                        </div>
                        <div class="price">GHS {{ number_format($pkg->price, 2) }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Buy form --}}
        <div class="col-lg-5">
            <h6 style="color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:1px;font-size:0.75rem;margin-bottom:1rem;">Complete Purchase</h6>
            <div class="buy-form">
                <form method="POST" action="{{ route('paystack.messaging.initialize', $tenant) }}">
                    @csrf
                    <input type="hidden" name="package_id" id="selectedPackageId" value="">

                    <div id="selectedSummary" style="background:rgba(255,255,255,0.05);border-radius:8px;padding:1rem;margin-bottom:1.25rem;text-align:center;display:none;">
                        <div style="color:rgba(255,255,255,0.5);font-size:0.75rem;margin-bottom:4px;">Selected Package</div>
                        <div id="summaryName" style="font-weight:700;color:#fff;font-size:1rem;"></div>
                        <div style="display:flex;justify-content:center;gap:1.5rem;margin:0.75rem 0;">
                            <div style="text-align:center;"><div id="summarySmS" style="font-weight:800;font-size:1.2rem;color:#3b82f6;"></div><div style="font-size:0.7rem;color:rgba(255,255,255,0.4);">SMS</div></div>
                            <div style="text-align:center;"><div id="summaryWa" style="font-weight:800;font-size:1.2rem;color:#25d366;"></div><div style="font-size:0.7rem;color:rgba(255,255,255,0.4);">WhatsApp</div></div>
                        </div>
                        <div id="summaryPrice" style="font-size:1.75rem;font-weight:800;color:#e94560;"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email for Receipt <span class="text-danger">*</span></label>
                        <input type="email" name="payer_email" class="form-control" value="{{ $tenant->admin_email }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Payer Name</label>
                        <input type="text" name="payer_name" class="form-control" value="{{ $tenant->admin_name }}">
                    </div>

                    <button type="submit" class="btn-pay" id="payBtn" disabled>
                        <i class="fas fa-lock me-2"></i> <span id="payBtnText">Select a Package First</span>
                    </button>
                    <div style="text-align:center;font-size:0.72rem;color:rgba(255,255,255,0.3);margin-top:0.75rem;">
                        Secured by Paystack · Card · Mobile Money
                    </div>
                </form>
            </div>

            {{-- Recent purchases --}}
            @if($history->count())
            <div class="history-card mt-3">
                <div style="color:rgba(255,255,255,0.5);font-size:0.72rem;text-transform:uppercase;letter-spacing:1px;margin-bottom:0.5rem;">Recent Purchases</div>
                @foreach($history as $h)
                <div class="history-row">
                    <div>
                        <div style="font-weight:600;color:#fff;">{{ $h->package?->name ?? 'Manual Top-up' }}</div>
                        <div style="font-size:0.72rem;">{{ $h->created_at->format('d M Y') }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="color:#3b82f6;font-weight:600;">+{{ $h->sms_credits_added }} SMS</div>
                        <div style="color:#25d366;font-weight:600;">+{{ $h->whatsapp_credits_added }} WA</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-weight:700;color:#fff;">GHS {{ number_format($h->amount, 2) }}</div>
                        <span style="font-size:0.68rem;background:{{ $h->status === 'completed' ? '#dcfce7' : '#fef2f2' }};color:{{ $h->status === 'completed' ? '#16a34a' : '#ef4444' }};padding:2px 6px;border-radius:4px;">
                            {{ ucfirst($h->status) }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function selectPackage(id, price, sms, wa, name) {
    document.querySelectorAll('.pkg-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    document.getElementById('selectedPackageId').value = id;
    document.getElementById('summaryName').textContent  = name;
    document.getElementById('summarySmS').textContent   = sms.toLocaleString();
    document.getElementById('summaryWa').textContent    = wa.toLocaleString();
    document.getElementById('summaryPrice').textContent = 'GHS ' + parseFloat(price).toLocaleString('en-GH', {minimumFractionDigits:2});
    document.getElementById('selectedSummary').style.display = 'block';

    const btn = document.getElementById('payBtn');
    btn.disabled = false;
    document.getElementById('payBtnText').textContent = 'Pay GHS ' + parseFloat(price).toLocaleString('en-GH', {minimumFractionDigits:2}) + ' Securely';
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
