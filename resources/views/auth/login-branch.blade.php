<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — {{ $schoolName ?? 'School' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            width: 100vw;
            max-height: 100vh;
            max-width: 100vw;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            @if(isset($loginBackground) && $loginBackground)
                background: linear-gradient(135deg, rgba(26, 26, 46, 0.85) 0%, rgba(22, 33, 62, 0.85) 50%, rgba(15, 52, 96, 0.85) 100%), url('{{ $loginBackground }}') center/cover no-repeat;
            @else
                background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            @endif
            background-attachment: fixed;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; }
        .orb-1 { width: 24rem; height: 24rem; background: #6366f1; top: 0; right: 0; transform: translate(25%, -50%); }
        .orb-2 { width: 20rem; height: 20rem; background: #2563eb; bottom: 0; left: 0; transform: translate(-25%, 25%); }
        .login-shell { position: relative; z-index: 1000; width: 100%; max-width: 28rem; max-height: 100vh; overflow-y: auto; isolation: isolate; pointer-events: auto; padding: 1rem; }
        .brand { text-align: center; margin-bottom: 1.5rem; }
        .logo-wrap { display: inline-flex; align-items: center; justify-content: center; width: 4.5rem; height: 4.5rem; border-radius: 1rem; margin-bottom: 1rem; overflow: hidden; background: rgba(0,0,0,0.3); box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5); }
        .logo-wrap img { width: 100%; height: 100%; object-fit: contain; }
        .brand h1 { color: #fff; font-size: 1.5rem; font-weight: 800; margin: 0 0 0.15rem; line-height: 1.2; }
        .brand p { color: rgba(255,255,255,0.5); font-size: 0.75rem; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; margin: 0; }
        .glass { background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.45); position: relative; z-index: 1; }
        .glass h2 { color: #fff; font-size: 1.1rem; font-weight: 700; margin: 0 0 0.2rem; }
        .glass .sub { color: rgba(255,255,255,0.4); font-size: 0.8rem; margin: 0 0 1.5rem; }
        .err-box { display: flex; align-items: center; gap: 0.75rem; padding: 0.9rem; margin-bottom: 1.2rem; background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.2); border-radius: 1rem; color: #fca5a5; font-size: 0.8rem; }
        .field { margin-bottom: 1rem; }
        .field label { display: block; color: rgba(255,255,255,0.6); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.4rem; }
        .input-wrap { position: relative; }
        .input-wrap .fa-icon { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.3); font-size: 0.8rem; pointer-events: none; }
        .input-field { width: 100%; padding: 0.75rem 0.9rem 0.75rem 2.5rem; border-radius: 0.75rem; font-size: 0.9rem; line-height: 1.4; border: 1px solid rgba(255,255,255,0.12); background: rgba(255,255,255,0.08); color: #fff; }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .btn-primary { width: 100%; margin-top: 0.3rem; padding: 0.75rem 0.9rem; border: none; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 600; color: #fff; cursor: pointer; background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .footer-note { text-align: center; color: rgba(255,255,255,0.2); font-size: 0.7rem; margin-top: 1.2rem; }
        .bg-notice { padding: 0.7rem 0.9rem; background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); border-radius: 0.5rem; color: #86efac; font-size: 0.7rem; margin-bottom: 0.8rem; }
        
        @media (max-width: 480px) {
            .login-shell { max-width: 100%; padding: 0.75rem; }
            .brand { margin-bottom: 1rem; }
            .logo-wrap { width: 4rem; height: 4rem; margin-bottom: 0.8rem; }
            .brand h1 { font-size: 1.3rem; margin-bottom: 0.1rem; }
            .brand p { font-size: 0.7rem; }
            .glass { padding: 1.25rem; }
            .glass h2 { font-size: 1rem; }
            .glass .sub { font-size: 0.75rem; margin-bottom: 1.2rem; }
            .field { margin-bottom: 0.8rem; }
            .field label { font-size: 0.65rem; margin-bottom: 0.3rem; }
            .btn-primary { font-size: 0.75rem; padding: 0.65rem 0.75rem; }
        }
    </style>
</head>
<body>

    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>

    <div class="login-shell">

        <div class="brand">
            <div class="logo-wrap">
                <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'Logo' }}">
            </div>
            <h1>{{ $schoolName ?? 'School' }}</h1>
            <p>{{ $schoolSubtitle ?? '' }}</p>
        </div>

        <div class="glass">
            @if(isset($branch) && $branch)
                <h2>{{ $branch->name }} — Branch Admin</h2>
                <p class="sub">Sign in to administer students for {{ $branch->name }}</p>
                @if(isset($loginBackground) && $loginBackground)
                    <div class="bg-notice">
                        <i class="fas fa-check-circle mr-1"></i>This is a customized login page for {{ $branch->name }}
                    </div>
                @endif
            @else
                <h2>Super Admin</h2>
                <p class="sub">Sign in with your super admin credentials</p>
            @endif

            <p class="sub" style="margin-top:-0.5rem; color:rgba(255,255,255,0.35); font-size:0.8rem;">
                <a href="{{ route('login') }}" style="color:#f8fafc; text-decoration:underline;">Back to branch selection</a>
            </p>

            @if($errors->any())
                <div class="err-box">
                    <i class="fas fa-exclamation-circle" style="flex-shrink:0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ isset($branch) && $branch ? secure_url("/login/branch/" . $branch->id) : secure_url("/login/super-admin") }}">
                @csrf

                <div class="field">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope fa-icon" aria-hidden="true"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                               autocomplete="username" class="input-field"
                               placeholder="admin@college.edu">
                    </div>
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock fa-icon" aria-hidden="true"></i>
                        <input id="password" type="password" name="password" required
                               autocomplete="current-password" class="input-field"
                               placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" class="btn-primary">
                    <i class="fas fa-arrow-right-to-bracket" style="margin-right:0.35rem"></i>
                    Sign In
                </button>
            </form>
        </div>

        <p class="footer-note">
            {{ $schoolName ?? 'School' }} {{ $schoolSubtitle ? ' — '.$schoolSubtitle : '' }} &copy; {{ date('Y') }} — Admin Portal
        </p>
    </div>

</body>
</html>