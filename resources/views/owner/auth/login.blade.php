<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Login — School System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            padding: 2.5rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.4);
        }
        .login-card .logo {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #e94560, #c62a47);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .login-card h4 { color: #fff; font-weight: 700; }
        .login-card p  { color: rgba(255,255,255,0.5); font-size: 0.9rem; }
        .form-control {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.15);
            color: #fff;
            border-radius: 8px;
            padding: 0.75rem 1rem;
        }
        .form-control:focus {
            background: rgba(255,255,255,0.12);
            border-color: #e94560;
            color: #fff;
            box-shadow: 0 0 0 3px rgba(233,69,96,0.2);
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3); }
        .form-label { color: rgba(255,255,255,0.7); font-size: 0.85rem; font-weight: 500; }
        .btn-owner {
            background: linear-gradient(135deg, #e94560, #c62a47);
            border: none;
            color: #fff;
            padding: 0.75rem;
            border-radius: 8px;
            font-weight: 600;
            width: 100%;
            transition: opacity 0.2s;
        }
        .btn-owner:hover { opacity: 0.9; color: #fff; }
        .badge-owner {
            background: rgba(233,69,96,0.15);
            color: #e94560;
            border: 1px solid rgba(233,69,96,0.3);
            font-size: 0.75rem;
            padding: 0.3rem 0.75rem;
            border-radius: 20px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="logo">
                <i class="fas fa-crown text-white fs-4"></i>
            </div>
            <h4>Owner Panel</h4>
            <p>School Management Platform</p>
            <span class="badge-owner">Restricted Access</span>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-sm py-2 px-3 mb-3" style="border-radius:8px; font-size:0.875rem;">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('owner.login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input
                    type="email"
                    name="email"
                    class="form-control @error('email') is-invalid @enderror"
                    value="{{ old('email') }}"
                    placeholder="owner@example.com"
                    autofocus
                    required
                >
                @error('email')
                    <div class="invalid-feedback" style="color:#f87171;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <input
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    placeholder="••••••••"
                    required
                >
                @error('password')
                    <div class="invalid-feedback" style="color:#f87171;">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" style="color:rgba(255,255,255,0.5); font-size:0.85rem;" for="remember">
                        Remember me
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-owner">
                <i class="fas fa-sign-in-alt me-2"></i> Sign In to Owner Panel
            </button>
        </form>
    </div>
</body>
</html>
