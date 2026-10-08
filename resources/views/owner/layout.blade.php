<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Owner Panel') — School Platform</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --owner-primary: #e94560;
            --owner-dark:    #0f3460;
            --owner-sidebar: #1a1a2e;
            --owner-text:    #e2e8f0;
            --owner-muted:   rgba(226,232,240,0.5);
        }
        body { background: #f1f5f9; font-family: 'Segoe UI', sans-serif; margin: 0; }

        /* Sidebar */
        .owner-sidebar {
            width: 250px; min-height: 100vh; background: var(--owner-sidebar);
            position: fixed; top: 0; left: 0; z-index: 1000;
            display: flex; flex-direction: column;
        }
        .sidebar-brand {
            padding: 1.5rem 1.25rem; border-bottom: 1px solid rgba(255,255,255,0.07);
            display: flex; align-items: center; gap: 0.75rem;
        }
        .sidebar-brand .crown {
            width: 38px; height: 38px; background: var(--owner-primary);
            border-radius: 8px; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .sidebar-brand span { color: #fff; font-weight: 700; font-size: 0.95rem; line-height: 1.3; }
        .sidebar-brand small { color: var(--owner-muted); font-size: 0.72rem; display: block; }

        .sidebar-nav { padding: 1rem 0; flex: 1; overflow-y: auto; }
        .nav-section { padding: 0.5rem 1.25rem 0.25rem; color: var(--owner-muted); font-size: 0.68rem; text-transform: uppercase; letter-spacing: 1px; }
        .sidebar-nav a {
            display: flex; align-items: center; gap: 0.75rem;
            padding: 0.6rem 1.25rem; color: var(--owner-muted);
            text-decoration: none; font-size: 0.875rem; transition: all 0.15s;
            border-left: 3px solid transparent;
        }
        .sidebar-nav a:hover, .sidebar-nav a.active {
            color: #fff; background: rgba(255,255,255,0.06);
            border-left-color: var(--owner-primary);
        }
        .sidebar-nav a i { width: 18px; text-align: center; font-size: 0.8rem; }

        .sidebar-footer {
            padding: 1rem 1.25rem; border-top: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-footer .owner-name { color: #fff; font-size: 0.85rem; font-weight: 600; }
        .sidebar-footer .owner-role { color: var(--owner-muted); font-size: 0.75rem; }

        /* Main content */
        .owner-main { margin-left: 250px; min-height: 100vh; }
        .owner-topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0;
            padding: 0.875rem 1.75rem; display: flex; align-items: center;
            justify-content: space-between; position: sticky; top: 0; z-index: 100;
        }
        .topbar-title { font-weight: 700; color: #1e293b; font-size: 1.1rem; }
        .topbar-actions { display: flex; align-items: center; gap: 0.75rem; }
        .owner-content { padding: 1.75rem; }

        /* Cards */
        .stat-card { background: #fff; border-radius: 12px; padding: 1.5rem; border: none; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .stat-card .stat-icon { width: 48px; height: 48px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
        .stat-card .stat-value { font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .stat-card .stat-label { color: #64748b; font-size: 0.8rem; margin-top: 0.25rem; }

        /* Badges */
        .badge-active    { background: #dcfce7; color: #16a34a; }
        .badge-suspended { background: #fef2f2; color: #dc2626; }
        .badge-trial     { background: #fef9c3; color: #ca8a04; }
        .badge-expired   { background: #f1f5f9; color: #64748b; }

        /* Tables */
        .owner-table { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); overflow: hidden; }
        .owner-table table { margin: 0; }
        .owner-table thead th { background: #f8fafc; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 0.9rem 1rem; }
        .owner-table tbody td { padding: 0.85rem 1rem; vertical-align: middle; font-size: 0.875rem; border-bottom: 1px solid #f1f5f9; }
        .owner-table tbody tr:last-child td { border-bottom: none; }
        .owner-table tbody tr:hover { background: #fafbfc; }

        /* Forms */
        .owner-card { background: #fff; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .owner-card .card-header { background: none; border-bottom: 1px solid #f1f5f9; padding: 1.25rem 1.5rem; font-weight: 700; color: #1e293b; }
        .owner-card .card-body { padding: 1.5rem; }

        .btn-owner-primary { background: var(--owner-primary); color: #fff; border: none; }
        .btn-owner-primary:hover { background: #c62a47; color: #fff; }

        @media(max-width:768px) {
            .owner-sidebar { width: 100%; min-height: auto; position: relative; }
            .owner-main { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="owner-sidebar">
    <div class="sidebar-brand">
        <div class="crown"><i class="fas fa-crown text-white" style="font-size:1rem;"></i></div>
        <span>Owner Panel<small>School Platform</small></span>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">Overview</div>
        <a href="{{ route('owner.dashboard') }}" class="{{ request()->routeIs('owner.dashboard') ? 'active' : '' }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>

        <div class="nav-section">Management</div>
        <a href="{{ route('owner.schools.index') }}" class="{{ request()->routeIs('owner.schools.*') ? 'active' : '' }}">
            <i class="fas fa-school"></i> Schools
        </a>
        <a href="{{ route('owner.plans.index') }}" class="{{ request()->routeIs('owner.plans.*') ? 'active' : '' }}">
            <i class="fas fa-layer-group"></i> Plans
        </a>
        <a href="{{ route('owner.features.index') }}" class="{{ request()->routeIs('owner.features.*') ? 'active' : '' }}">
            <i class="fas fa-toggle-on"></i> Features
        </a>

        <div class="nav-section">Finance</div>
        <a href="{{ route('owner.payments.index') }}" class="{{ request()->routeIs('owner.payments.*') ? 'active' : '' }}">
            <i class="fas fa-money-bill-wave"></i> Payments
        </a>

        <div class="nav-section">System</div>
        <a href="{{ route('owner.backups.index') }}" class="{{ request()->routeIs('owner.backups.*') ? 'active' : '' }}">
            <i class="fas fa-database"></i> Backups
        </a>
        <a href="{{ route('owner.audit-logs.index') }}" class="{{ request()->routeIs('owner.audit-logs.*') ? 'active' : '' }}">
            <i class="fas fa-history"></i> Audit Log
        </a>

        <div class="nav-section">Account</div>
        <a href="{{ route('owner.profile.edit') }}" class="{{ request()->routeIs('owner.profile.*') ? 'active' : '' }}">
            <i class="fas fa-user-cog"></i> My Profile
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div style="width:32px;height:32px;background:var(--owner-primary);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:0.8rem;color:#fff;font-weight:700;flex-shrink:0;">
                {{ strtoupper(substr(auth('owner')->user()->name, 0, 1)) }}
            </div>
            <div>
                <div class="owner-name">{{ auth('owner')->user()->name }}</div>
                <div class="owner-role">System Owner</div>
            </div>
        </div>
        <form method="POST" action="{{ route('owner.logout') }}">
            @csrf
            <button type="submit" style="background:none;border:none;color:var(--owner-muted);font-size:0.8rem;padding:0;cursor:pointer;">
                <i class="fas fa-sign-out-alt me-1"></i> Sign Out
            </button>
        </form>
    </div>
</div>

<div class="owner-main">
    <div class="owner-topbar">
        <div class="topbar-title">@yield('page-title', 'Dashboard')</div>
        <div class="topbar-actions">
            @yield('topbar-actions')
        </div>
    </div>

    <div class="owner-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:10px; border:none; background:#dcfce7; color:#166534;">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:10px; border:none; background:#fef2f2; color:#991b1b;">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
