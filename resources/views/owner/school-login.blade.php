<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $tenant->name }} — Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #1a1a2e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', sans-serif;
            padding: 1rem;
        }
        .login-wrapper { width: 100%; max-width: 440px; }

        .login-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }

        .school-logo {
            width: 72px; height: 72px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem; color: #fff;
        }
        .school-name { color: #fff; font-weight: 700; font-size: 1.2rem; }
        .school-sub  { color: rgba(255,255,255,0.45); font-size: 0.8rem; margin-bottom: 1.5rem; }

        /* Role selector tabs */
        .role-tabs {
            display: flex;
            background: rgba(255,255,255,0.07);
            border-radius: 12px;
            padding: 4px;
            margin-bottom: 1.5rem;
            gap: 4px;
        }
        .role-tab {
            flex: 1;
            padding: 0.55rem 0.5rem;
            border-radius: 9px;
            border: none;
            background: transparent;
            color: rgba(255,255,255,0.5);
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.35rem;
        }
        .role-tab.active {
            background: #fff;
            color: #1e293b;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        .role-tab:hover:not(.active) { color: rgba(255,255,255,0.8); }

        .form-label { color: rgba(255,255,255,0.7); font-size: 0.85rem; font-weight: 500; }
        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fff; border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.12);
            border-color: var(--role-color, #3b82f6);
            color: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.25); }

        .btn-login {
            border: none; color: #fff; padding: 0.8rem;
            border-radius: 10px; font-weight: 600; width: 100%;
            font-size: 0.95rem; transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; color: #fff; }

        .trial-bar {
            background: rgba(245,158,11,0.15);
            border: 1px solid rgba(245,158,11,0.3);
            color: #fbbf24; border-radius: 8px;
            padding: 0.5rem 0.75rem; font-size: 0.8rem;
            margin-bottom: 1.25rem; text-align: center;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: rgba(255,255,255,0.35);
            font-size: 0.8rem;
            text-decoration: none;
        }
        .back-link a:hover { color: rgba(255,255,255,0.6); }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">

        {{-- School logo + name --}}
        <div class="text-center">
            <div class="school-logo" id="schoolLogo" style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="school-name">{{ $tenant->name }}</div>
            <div class="school-sub" id="roleSubtitle">Select your account type below</div>
        </div>

        @if($tenant->isOnTrial())
        <div class="trial-bar">
            <i class="fas fa-clock me-1"></i>
            Trial: {{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's' }} remaining
        </div>
        @endif

        @if($errors->any())
        <div class="alert mb-3" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;border-radius:10px;font-size:0.85rem;">
            <i class="fas fa-exclamation-circle me-1"></i>{{ $errors->first() }}
        </div>
        @endif

        {{-- Role Selector --}}
        <div class="role-tabs">
            <button type="button" class="role-tab active" id="tab-superadmin" onclick="setRole('superadmin')">
                <i class="fas fa-crown"></i> Super Admin
            </button>
            <button type="button" class="role-tab" id="tab-admin" onclick="setRole('admin')">
                <i class="fas fa-user-shield"></i> Admin
            </button>
            <button type="button" class="role-tab" id="tab-lecturer" onclick="setRole('lecturer')">
                <i class="fas fa-chalkboard-user"></i> Lecturer
            </button>
        </div>

        <form method="POST" action="{{ route('school.login.submit', $tenant->slug) }}" id="loginForm">
            @csrf
            <input type="hidden" name="login_role" id="loginRoleField" value="{{ old('login_role', $role ?? 'superadmin') }}">

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="your@email.com"
                    autofocus required>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••" required>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember"
                        style="color:rgba(255,255,255,0.45);font-size:0.82rem;">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn-login" id="loginBtn"
                style="background:linear-gradient(135deg,#7c3aed,#5b21b6);">
                <i class="fas fa-crown me-2"></i> Sign In as Super Admin
            </button>
        </form>

        <div class="back-link">
            <a href="{{ route('website.portal') }}"><i class="fas fa-arrow-left me-1"></i> Back to Portal</a>
        </div>

        <div class="text-center mt-3" style="color:rgba(255,255,255,0.2);font-size:0.72rem;">
            Powered by School Management Platform
        </div>
    </div>
</div>

<script>
const roles = {
    superadmin: {
        label:    'Super Admin Login',
        subtitle: 'Full system access — manage everything',
        icon:     'fa-crown',
        gradient: 'linear-gradient(135deg,#7c3aed,#5b21b6)',
        btnText:  'Sign In as Super Admin',
        btnIcon:  'fa-crown',
    },
    admin: {
        label:    'Admin Login',
        subtitle: 'Standard administrator access',
        icon:     'fa-user-shield',
        gradient: 'linear-gradient(135deg,#3b82f6,#1d4ed8)',
        btnText:  'Sign In as Admin',
        btnIcon:  'fa-user-shield',
    },
    lecturer: {
        label:    'Lecturer / Teacher Login',
        subtitle: 'Attendance, exams and reports',
        icon:     'fa-chalkboard-user',
        gradient: 'linear-gradient(135deg,#10b981,#059669)',
        btnText:  'Sign In as Lecturer',
        btnIcon:  'fa-chalkboard-user',
    },
};

function setRole(role) {
    const cfg = roles[role];

    // Update tabs
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    document.getElementById('tab-' + role).classList.add('active');

    // Update logo
    document.getElementById('schoolLogo').style.background = cfg.gradient;
    document.getElementById('schoolLogo').querySelector('i').className = 'fas ' + cfg.icon;

    // Update subtitle
    document.getElementById('roleSubtitle').textContent = cfg.subtitle;

    // Update button
    const btn = document.getElementById('loginBtn');
    btn.style.background = cfg.gradient;
    btn.innerHTML = `<i class="fas ${cfg.btnIcon} me-2"></i> ${cfg.btnText}`;

    // Set hidden field
    document.getElementById('loginRoleField').value = role;
}

// Init with pre-selected role
setRole('{{ old('login_role', $role ?? 'superadmin') }}');
</script>
</body>
</html>
