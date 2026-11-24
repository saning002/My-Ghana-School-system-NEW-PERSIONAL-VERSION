<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended — {{ $tenant->name }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Segoe UI',system-ui,sans-serif;background:#0f172a;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:1.5rem;}
        .card{background:#fff;border-radius:24px;box-shadow:0 24px 80px rgba(0,0,0,.4);max-width:500px;width:100%;overflow:hidden;}
        .header{background:linear-gradient(135deg,#334155,#1e293b);color:#fff;padding:2.5rem 2rem 2rem;text-align:center;}
        .header .icon{width:72px;height:72px;background:rgba(255,255,255,.1);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;font-size:2rem;}
        .header h1{font-size:1.4rem;font-weight:800;margin-bottom:.4rem;}
        .header .school{font-size:.85rem;opacity:.7;font-weight:500;}
        .body{padding:2rem;}
        .body p{color:#475569;font-size:.88rem;line-height:1.75;margin-bottom:1rem;}
        .contact-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:1rem 1.25rem;margin:1rem 0;font-size:.85rem;color:#334155;line-height:1.7;}
        .contact-box a{color:#3b82f6;text-decoration:none;}
        .btn-login{display:block;background:linear-gradient(135deg,#16a34a,#059669);color:#fff;border:none;padding:1rem 2rem;border-radius:14px;font-weight:700;font-size:.95rem;text-decoration:none;text-align:center;margin-top:1.5rem;transition:filter .15s;}
        .btn-login:hover{filter:brightness(.9);color:#fff;}
        .btn-login i{margin-right:.5rem;}
        .footer{text-align:center;padding:1rem;border-top:1px solid #f1f5f9;font-size:.75rem;color:#94a3b8;}
    </style>
</head>
<body>
<div class="card">
    <div class="header">
        <div class="icon"><i class="fas fa-ban"></i></div>
        <h1>Account Suspended</h1>
        <div class="school">{{ $tenant->name }}</div>
    </div>
    <div class="body">
        <p>Access to <strong>{{ $tenant->name }}</strong>'s system has been temporarily suspended. This is usually due to an outstanding payment or a policy issue.</p>

        <div class="contact-box">
            <strong>To restore access, contact support:</strong><br>
            <i class="fas fa-envelope" style="color:#94a3b8;margin-right:.4rem;"></i>
            <a href="mailto:{{ $tenant->admin_email }}">{{ $tenant->admin_email }}</a>
        </div>

        <p style="font-size:.82rem;color:#64748b;">If you are the school administrator, log in to view the reason and resolve the issue.</p>

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
