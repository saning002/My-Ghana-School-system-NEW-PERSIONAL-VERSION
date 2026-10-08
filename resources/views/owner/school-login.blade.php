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
        }
        .login-wrapper {
            width: 100%;
            max-width: 420px;
            padding: 1rem;
        }
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
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.25rem;
            font-size: 2rem; color: #fff;
        }
        .school-name { color: #fff; font-weight: 700; font-size: 1.2rem; margin-bottom: 0.25rem; }
        .school-sub  { color: rgba(255,255,255,0.45); font-size: 0.8rem; margin-bottom: 2rem; }
        .form-label  { color: rgba(255,255,255,0.7); font-size: 0.85rem; font-weight: 500; }
        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fff; border-radius: 10px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.12);
            border-color: #3b82f6; color: #fff;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.25);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.25); }
        .btn-login {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border: none; color: #fff; padding: 0.8rem;
            border-radius: 10px; font-weight: 600; width: 100%;
            font-size: 0.95rem; transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; color: #fff; }
        .invalid-feedback { color: #f87171; font-size: 0.8rem; }
        .trial-bar {
            background: rgba(245,158,11,0.15);
            border: 1px solid rgba(245,158,11,0.3);
            color: #fbbf24; border-radius: 8px;
            padding: 0.5rem 0.75rem; font-size: 0.8rem;
            margin-bottom: 1.25rem; text-align: center;
        }
    </style>
</head>
<body>
<div class="login-wrapper">
    <div class="login-card">
        <div class="text-center">
            <div class="school-logo">
                <i class="fas fa-graduation-cap"></i>
            </div>
            <div class="school-name">{{ $tenant->name }}</div>
            <div class="school-sub">School Management System</div>
        </div>

        @if($tenant->isOnTrial())
        <div class="trial-bar">
            <i class="fas fa-clock me-1"></i>
            Trial: {{ $tenant->trialDaysRemaining() }} day{{ $tenant->trialDaysRemaining() === 1 ? '' : 's' }} remaining
        </div>
        @endif

        @if($errors->any())
        <div class="alert mb-3" style="background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);color:#f87171;border-radius:10px;font-size:0.85rem;">
            <i class="fas fa-exclamation-circle me-1"></i>
            {{ $errors->first() }}
        </div>
        @endif

        <form method="POST" action="{{ route('school.login.submit', $tenant->slug) }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="admin@school.com"
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
                        style="color:rgba(255,255,255,0.45);font-size:0.82rem;">
                        Remember me
                    </label>
                </div>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt me-2"></i> Sign In
            </button>
        </form>

        <div class="text-center mt-4" style="color:rgba(255,255,255,0.25);font-size:0.75rem;">
            Powered by School Management Platform
        </div>
    </div>
</div>
</body>
</html>
