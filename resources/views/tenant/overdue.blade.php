<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Overdue — {{ $tenant->name }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
        .card{background:#fff;border-radius:24px;box-shadow:0 24px 80px rgba(0,0,0,.4);max-width:500px;width:100%;overflow:hidden;}
        .header{background:linear-gradient(135deg,#b45309 0%,#dc2626 100%);color:#fff;padding:2.5rem 2rem 2rem;text-align:center;}
        .header .icon{width:72px;height:72px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2rem;}
        .header h1{font-size:1.4rem;font-weight:800;margin-bottom:.4rem;}
        .header .school{font-size:.85rem;opacity:.75;font-weight:500;}
        .body{padding:2rem;}
        .body p{color:#475569;font-size:.88rem;line-height:1.75;margin-bottom:1rem;}
        .info-box{background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:1rem 1.25rem;margin:1rem 0;font-size:.83rem;color:#92400e;line-height:1.6;}
        .steps{background:#f8fafc;border-radius:12px;padding:1rem 1.25rem;margin:1rem 0;}
        .steps p{font-size:.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:.05em;margin-bottom:.6rem;}
        .steps ol{padding-left:1.2rem;color:#334155;font-size:.85rem;}
        .steps ol li{margin-bottom:.4rem;line-height:1.5;}
        .btn-login{display:block;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;border:none;padding:1rem 2rem;border-radius:14px;font-weight:700;font-size:.95rem;text-decoration:none;text-align:center;margin-top:1.5rem;transition:filter .15s;}
        .btn-login:hover{filter:brightness(.9);color:#fff;}
        .btn-login i{margin-right:.5rem;}
        .footer{text-align:center;padding:1rem;border-top:1px solid #f1f5f9;font-size:.75rem;color:#94a3b8;}
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="icon"><i class="fas fa-file-invoice-dollar"></i></div>
        <h1>Subscription Payment Overdue</h1>
        <div class="school">{{ $tenant->name }}</div>
    </div>
    <div class="body">
        <p>Your term invoice is past its due date and has not been paid. Access to the school management system is on hold until the invoice is settled.</p>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>&nbsp;
            Invoices are due <strong>14 days</strong> after they are generated. Payment restores access immediately.
        </div>

        <div class="steps">
            <p>To restore access:</p>
            <ol>
                <li>Click <strong>Go to Login</strong> below</li>
                <li>Sign in with your admin account</li>
                <li>Go to <strong>Subscription</strong> in the sidebar</li>
                <li>Click <strong>Pay Now</strong> on the overdue invoice</li>
            </ol>
        </div>

        <a href="{{ route('school.admin.login', $tenant->slug) }}" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Go to Login
        </a>
    </div>
    <div class="footer">
        Powered by SaningTech Solutions &middot; {{ $tenant->name }}
    </div>
</div>
</body>
</html>
