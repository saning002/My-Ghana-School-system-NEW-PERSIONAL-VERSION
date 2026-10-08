<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Expired — {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #1e293b; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card { background: #fff; border: none; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); max-width: 520px; width: 100%; padding: 3rem 2.5rem; text-align: center; }
        .icon-wrap { width: 80px; height: 80px; background: #fffbeb; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .school-name { color: #94a3b8; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.25rem; }
        h2 { font-weight: 700; color: #1e293b; }
        p  { color: #64748b; line-height: 1.7; }
        .badge-expired { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; padding: 0.35rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; margin-bottom: 1.5rem; }
        .expired-date { font-size: 0.85rem; color: #94a3b8; margin-top: 0.5rem; }
        .contact-box { background: #f8fafc; border-radius: 10px; padding: 1rem 1.5rem; margin-top: 1.5rem; }
    </style>
</head>
<body>
    <div class="card">
        <p class="school-name">{{ $tenant->name }}</p>
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <span class="badge-expired">Subscription Expired</span>
        <h2>Your Subscription Has Expired</h2>
        @if($tenant->subscription_ends_at)
            <p class="expired-date">Expired on: <strong>{{ $tenant->subscription_ends_at->format('d M Y') }}</strong></p>
        @endif
        <p>Access to <strong>{{ $tenant->name }}</strong>'s system has been paused because the subscription period has ended. Please contact your school administrator to renew.</p>
        <div class="contact-box">
            <p class="mb-1" style="font-size:0.85rem; color:#64748b;">Contact your administrator to renew:</p>
            <strong style="color:#1e293b;">School System Support</strong><br>
            <a href="mailto:{{ $tenant->admin_email }}" style="color:#3b82f6;">{{ $tenant->admin_email }}</a>
        </div>
    </div>
</body>
</html>
