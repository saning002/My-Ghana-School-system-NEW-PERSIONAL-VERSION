<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Overdue — {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family:'Segoe UI',sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:2rem; }
        .card { background:#fff;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.3);max-width:520px;width:100%;overflow:hidden; }
        .header { background:linear-gradient(135deg,#b45309,#dc2626);color:#fff;padding:2.5rem 2rem;text-align:center; }
        .header i { font-size:2.5rem;margin-bottom:1rem; }
        .header h1 { font-size:1.5rem;font-weight:800;margin:0; }
        .header p { opacity:.8;font-size:.9rem;margin-top:.5rem; }
        .body { padding:2rem; }
        .body p { color:#475569;font-size:.9rem;line-height:1.7; }
        .btn-pay { background:linear-gradient(135deg,#16a34a,#059669);color:#fff;border:none;padding:.85rem 2rem;border-radius:12px;font-weight:700;font-size:.95rem;text-decoration:none;display:inline-block;width:100%;text-align:center; }
        .btn-pay:hover { filter:brightness(.92);color:#fff; }
        .info-box { background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:1rem 1.25rem;margin:1rem 0;font-size:.85rem;color:#92400e; }
        .footer { text-align:center;padding:1rem;border-top:1px solid #f1f5f9;font-size:.78rem;color:#94a3b8; }
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <i class="fas fa-file-invoice-dollar"></i>
        <h1>Subscription Payment Overdue</h1>
        <p>{{ $tenant->name }}</p>
    </div>
    <div class="body">
        <p>Your term invoice is past its due date and remains unpaid. Access to the school management system has been suspended until payment is made.</p>

        <div class="info-box">
            <i class="fas fa-info-circle me-1"></i>
            Your subscription is billed per student per term. An invoice was generated based on your active student count and is now overdue.
        </div>

        <p style="font-size:.82rem;">To restore access immediately:</p>
        <ol style="font-size:.85rem;color:#475569;padding-left:1.2rem;">
            <li class="mb-1">Log in with your admin account</li>
            <li class="mb-1">Go to <strong>Subscription</strong> in the sidebar</li>
            <li class="mb-1">Click <strong>Pay Now</strong> on the overdue invoice</li>
            <li class="mb-1">Pay via Card, MoMo or Bank Transfer</li>
        </ol>

        <a href="{{ url('/login') }}" class="btn-pay mt-3">
            <i class="fas fa-sign-in-alt me-1"></i> Go to Login
        </a>
    </div>
    <div class="footer">
        Powered by SaningTech Solutions · {{ $tenant->name }}
    </div>
</div>
</body>
</html>
