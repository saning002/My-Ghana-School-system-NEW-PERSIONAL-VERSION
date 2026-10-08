<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Branch — {{ $schoolName ?? 'School' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            position: relative;
            overflow: hidden;
        }
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
        }
        .orb-1 { width: 24rem; height: 24rem; background: #6366f1; top: 0; right: 0; transform: translate(25%, -50%); }
        .orb-2 { width: 20rem; height: 20rem; background: #2563eb; bottom: 0; left: 0; transform: translate(-25%, 25%); }

        .login-shell {
            position: relative;
            z-index: 1000;
            width: 100%;
            max-width: 48rem;
            pointer-events: auto;
        }

        .brand {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .logo-wrap {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 5rem;
            height: 5rem;
            border-radius: 1rem;
            margin-bottom: 1rem;
            overflow: hidden;
            background: rgba(0,0,0,0.3);
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .logo-wrap img { width: 5rem; height: 5rem; object-fit: contain; }
        .brand h1 { color: #fff; font-size: 1.5rem; font-weight: 800; margin: 0 0 0.25rem; line-height: 1.2; }
        .brand p { color: rgba(255,255,255,0.5); font-size: 0.875rem; font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; margin: 0; }

        .glass {
            background: rgba(255,255,255,0.06);
            -webkit-backdrop-filter: blur(20px);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.45);
        }
        .glass h2 { color: #fff; font-size: 1.125rem; font-weight: 700; margin: 0 0 0.25rem; }
        .glass .sub { color: rgba(255,255,255,0.4); font-size: 0.875rem; margin: 0 0 1.75rem; }

        .branch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1rem;
        }

        .branch-card {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            border-radius: 1rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.04);
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            color: #fff;
        }
        .branch-card:hover {
            background: rgba(255,255,255,0.12);
            border-color: #6366f1;
            transform: translateY(-4px);
            box-shadow: 0 12px 30px -8px rgba(99,102,241,0.35);
        }
        .branch-card .avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            box-shadow: 0 4px 12px rgba(99,102,241,0.3);
        }
        .branch-card .name {
            font-size: 0.875rem;
            font-weight: 600;
            text-align: center;
            margin-bottom: 0.25rem;
        }
        .branch-card .location {
            font-size: 0.75rem;
            color: rgba(255,255,255,0.4);
            text-align: center;
        }

        .super-admin-card {
            border-color: rgba(212,160,23,0.3);
            background: rgba(212,160,23,0.06);
        }
        .super-admin-card:hover {
            border-color: #D4A017;
            box-shadow: 0 12px 30px -8px rgba(212,160,23,0.35);
        }
        .super-admin-card .avatar {
            background: linear-gradient(135deg, #D4A017, #b8860b);
            box-shadow: 0 4px 12px rgba(212,160,23,0.3);
        }

        .err-box {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            background: rgba(239,68,68,0.1);
            border: 1px solid rgba(239,68,68,0.2);
            border-radius: 1rem;
            color: #fca5a5;
            font-size: 0.875rem;
        }

        .footer-note {
            text-align: center;
            color: rgba(255,255,255,0.2);
            font-size: 0.75rem;
            margin-top: 1.5rem;
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
            <h2>Select your branch</h2>
            <p class="sub">Choose a branch to sign in as administrator</p>

            @if($errors->any())
                <div class="err-box">
                    <i class="fas fa-exclamation-circle" style="flex-shrink:0"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="branch-grid">
                @foreach($branches as $branch)
                    <a href="{{ secure_url("/login/branch/" . $branch->id) }}" class="branch-card">
                        <div class="avatar">{{ strtoupper(substr($branch->getCodeOrDerived(), 0, 2)) }}</div>
                        <div class="name">{{ $branch->name }}</div>
                        @if($branch->location)
                            <div class="location"><i class="fas fa-map-marker-alt" style="margin-right:4px"></i>{{ $branch->location }}</div>
                        @endif
                    </a>
                @endforeach

                <a href="{{ secure_url('/login/super-admin') }}" class="branch-card super-admin-card">
    <div class="avatar"><i class="fas fa-crown" style="font-size:1.25rem"></i></div>
    <div class="name">Super Admin</div>
    <div class="location">Manage all branches</div>
</a>
        </div>

        <p class="footer-note">
            {{ $schoolName ?? 'School' }} {{ $schoolSubtitle ? ' — '.$schoolSubtitle : '' }} &copy; {{ date('Y') }} — Admin Portal
        </p>
    </div>
</body>
</html>
