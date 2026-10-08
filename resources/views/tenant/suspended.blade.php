<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Suspended — {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1e293b; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card { background: #fff; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 520px; width: 100%; padding: 3rem 2.5rem; text-align: center; }
        .icon-wrap { width: 80px; height: 80px; background: #fef3f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .school-name { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem; }
        h2 { font-weight: 700; color: #1e293b; }
        p  { color: #64748b; line-height: 1.7; }
        .badge-suspended { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 0.35rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem; }
        .contact-box { background: #f8fafc; border-radius: 10px; padding: 1rem 1.5rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <p class="school-name">{{ $tenant->name }}</p>
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
        </div>
        <span class="badge-suspended">Account Suspended</span>
        <h2>Service Suspended</h2>
        <p>Access to <strong>{{ $tenant->name }}</strong>'s system has been temporarily suspended. This may be due to an outstanding payment or a policy issue.</p>
        <div class="contact-box">
            <p class="mb-1" style="font-size:0.85rem; color:#64748b;">To restore access, please contact:</p>
            <strong style="color:#1e293b;">School System Support</strong><br>
            <a href="mailto:{{ $tenant->admin_email }}" style="color:#3b82f6;">{{ $tenant->admin_email }}</a>
        </div>
    </div>
</body>
</html>
