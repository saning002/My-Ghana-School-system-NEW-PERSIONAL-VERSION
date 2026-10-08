<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feature Not Available — {{ $tenant->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f1f5f9; min-height: 100vh; display: flex; align-items: center; justify-content: center; font-family: 'Segoe UI', sans-serif; }
        .card { background: #fff; border: none; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.08); max-width: 500px; width: 100%; padding: 3rem 2.5rem; text-align: center; }
        .icon-wrap { width: 80px; height: 80px; background: #f0f9ff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        h2 { font-weight: 700; color: #1e293b; }
        p  { color: #64748b; line-height: 1.7; }
        .feature-key { display: inline-block; background: #f1f5f9; color: #475569; font-family: monospace; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.85rem; margin-bottom: 1rem; }
        .btn-back { background: #3b82f6; color: #fff; border: none; padding: 0.65rem 1.75rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block; margin-top: 1rem; }
        .btn-back:hover { background: #2563eb; color: #fff; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-wrap">
            <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
        </div>
        <span class="feature-key">{{ $featureKey }}</span>
        <h2>Feature Not Available</h2>
        <p>This feature is not included in <strong>{{ $tenant->name }}</strong>'s current plan. Please contact your school administrator to upgrade or enable this feature.</p>
        <p style="font-size:0.85rem;">
            Admin: <a href="mailto:{{ $tenant->admin_email }}">{{ $tenant->admin_email }}</a>
        </p>
        <a href="javascript:history.back()" class="btn-back">Go Back</a>
    </div>
</body>
</html>
