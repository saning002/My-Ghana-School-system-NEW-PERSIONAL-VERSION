<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — {{ $schoolName ?? 'School' }}</title>
    {{-- No Tailwind CDN: corporate/ad-block often blocks cdn.tailwindcss.com on desktop while mobile works --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow-x: hidden;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.2;
            pointer-events: none;
        }
        .orb-1 {
            width: 24rem;
            height: 24rem;
            background: #f59e0b;
            top: 0;
            right: 0;
            transform: translate(25%, -50%);
        }
        .orb-2 {
            width: 20rem;
            height: 20rem;
            background: #38bdf8;
            bottom: 0;
            left: 0;
            transform: translate(-25%, 25%);
        }
        .login-shell { position: relative; z-index: 1000; width: 100%; max-width: 24rem; isolation: isolate; pointer-events: auto; }
        .brand { text-align: center; margin-bottom: 1.5rem; }
        .logo-wrap { display: inline-flex; align-items: center; justify-content: center; width: 5rem; height: 5rem; border-radius: 1rem; margin-bottom: 0.875rem; overflow: hidden; background: rgba(255,255,255,0.14); box-shadow: 0 25px 50px -12px rgba(15,23,42,0.35); }
        .logo-wrap img { width: 5rem; height: 5rem; object-fit: contain; }
        .brand h1 { color: #fff; font-size: 1.5rem; font-weight: 800; margin: 0 0 0.125rem; line-height: 1.2; }
        .brand p { color: rgba(255,255,255,0.72); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; margin: 0; }
        .glass { background: rgba(255,255,255,0.09); border: 1px solid rgba(255,255,255,0.16); border-radius: 1.5rem; padding: 1.5rem; box-shadow: 0 30px 60px -18px rgba(15,23,42,0.45); position: relative; z-index: 1; transform: translateZ(0); -webkit-backface-visibility: hidden; backface-visibility: hidden; }
        @supports ((-webkit-backdrop-filter: blur(12px)) or (backdrop-filter: blur(12px))) { .glass { background: rgba(255,255,255,0.07); -webkit-backdrop-filter: blur(20px); backdrop-filter: blur(20px); } }
        .glass h2 { color: #fff; font-size: 1.1rem; font-weight: 700; margin: 0 0 0.125rem; }
        .glass .sub { color: rgba(255,255,255,0.68); font-size: 0.8rem; margin: 0 0 1.5rem; }
        .err-box { display: flex; align-items: center; gap: 0.75rem; padding: 1rem; margin-bottom: 1.5rem; background: rgba(239,68,68,0.12); border: 1px solid rgba(239,68,68,0.2); border-radius: 1rem; color: #fecaca; font-size: 0.875rem; }
        .field { margin-bottom: 0.875rem; }
        .field label { display: block; color: rgba(255,255,255,0.72); font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem; }
        .input-wrap { position: relative; }
        .input-wrap .fa-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4); font-size: 0.875rem; pointer-events: none; }
        .input-field { width: 100%; padding: 0.75rem 0.875rem 0.75rem 2.5rem; border-radius: 0.75rem; font-size: 0.9rem; line-height: 1.3; border: 1px solid rgba(255,255,255,0.16); background: rgba(255,255,255,0.1); color: #fff; transition: background 0.16s, border-color 0.16s, box-shadow 0.16s; }
        .input-field::placeholder { color: rgba(255,255,255,0.4); }
        .input-field:focus { outline: none; background: rgba(255,255,255,0.15); border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245,158,11,0.2); }
        .btn-primary { width: 100%; margin-top: 0.375rem; padding: 0.75rem 0.875rem; border: none; border-radius: 0.75rem; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.02em; color: #fff; cursor: pointer; background: #f59e0b; transition: transform 0.16s, box-shadow 0.16s, background 0.16s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 10px 28px rgba(37,99,235,0.35); }
        .footer-note { text-align: center; color: rgba(255,255,255,0.28); font-size: 0.7rem; margin-top: 1rem; }
    </style>
</head>
<body>

    <div class="orb orb-1" aria-hidden="true"></div>
    <div class="orb orb-2" aria-hidden="true"></div>

    <div class="login-shell">

        <div class="brand">
                <div class="logo-wrap">
                <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'School' }}">
            </div>
            <h1>{{ $schoolName ?? 'School' }}</h1>
            <p>{{ $schoolSubtitle ?? '' }}</p>
        </div>

        <div class="glass">
            <h2>Welcome back</h2>
            <p class="sub">Sign in to your college portal</p>

            @if($errors->any())
                <div class="err-box">
                    <i class="fas fa-exclamation-circle" style="flex-shrink:0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
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
