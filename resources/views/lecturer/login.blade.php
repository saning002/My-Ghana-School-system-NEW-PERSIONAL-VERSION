<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Portal — {{ $schoolName ?? 'School' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
        body { margin: 0; height: 100vh; width: 100vw; max-height: 100vh; max-width: 100vw; padding: 0; overflow: hidden; background: #f3f4f6; display: flex; align-items: center; justify-content: center; }

        .bg-anim { position: fixed; inset: 0; z-index: 0; overflow: hidden; background: linear-gradient(135deg, #f8fafc 0%, #f3f4f6 100%); pointer-events: none; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.26; animation: drift 8s ease-in-out infinite; pointer-events: none; }
        .orb-1 { width: 500px; height: 500px; background: #0B1121; top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 420px; height: 420px; background: #D4A017; bottom: -120px; right: -80px; animation-delay: 3s; }
        .orb-3 { width: 320px; height: 320px; background: #2563eb; top: 40%; left: 55%; transform: translate(-50%,-50%); animation-delay: 1.5s; }
        @keyframes drift { 0%,100%{transform:translate(0,0)} 33%{transform:translate(20px,-20px)} 66%{transform:translate(-15px,15px)} }

        .portal-card { position: relative; z-index: 1000; pointer-events: auto; isolation: isolate; transform: translateZ(0); width: 90%; max-width: 900px; max-height: 90vh; overflow-y: auto; border-radius: 24px; display: flex; box-shadow: 0 40px 90px rgba(15,23,42,0.16); border: 1px solid rgba(255,255,255,0.7); background: #fffcf5; }
        .left-panel { width: 45%; position: relative; z-index: 2; pointer-events: auto; background: #fffdf8; border-right: 1px solid #f3e8ff; padding: 40px 32px; display: flex; flex-direction: column; justify-content: center; }
        .right-panel { width: 55%; pointer-events: none; background: #0B1121; padding: 40px 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden; }
        .right-panel::before { content: ''; position: absolute; inset: 0; pointer-events: none; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
        .portal-input { width: 100%; background: #f8fafc; border: 1.5px solid #d8e0eb; border-radius: 12px; color: #111827; padding: 14px 16px 14px 44px; font-size: 14px; font-family: 'Poppins', sans-serif; transition: all 0.16s; outline: none; }
        .portal-input:focus { border-color: #2563eb; background: #ffffff; box-shadow: 0 0 0 3px rgba(37,99,235,0.14); }
        .portal-input::placeholder { color: #64748b; }
        .portal-btn { width: 100%; padding: 14px; border-radius: 12px; background: #D4A017; color: #111827; font-weight: 700; font-size: 14px; font-family: 'Poppins', sans-serif; border: none; cursor: pointer; transition: all 0.16s; letter-spacing: 0.3px; }
        .portal-btn:hover { box-shadow: 0 10px 26px rgba(212,160,23,0.24); transform: translateY(-1px); }
        .closed-banner { background: #fee2e2; border: 1.5px solid #fca5a5; border-radius: 14px; padding: 20px; text-align: center; color: #991b1b; }
        .portal-footer { margin-top: 20px; text-align: center; }
        .portal-link { color: #6366f1; font-size: 12px; font-weight: 600; text-decoration: none; transition: all 0.16s; }
        .portal-link:hover { opacity: 0.8; }
        @keyframes float { 0%,100%{transform:translateY(0) rotate(0deg)} 50%{transform:translateY(-15px) rotate(5deg)} }
        .float-shape { animation: float 4s ease-in-out infinite; }
        @media (max-width: 900px) { .portal-card { width: 95%; max-height: 95vh; } .left-panel { padding: 35px 25px; } .right-panel { padding: 35px 25px; } .portal-input { padding: 12px 14px 12px 40px; font-size: 13px; } .portal-btn { padding: 12px; font-size: 13px; } }
        @media (max-width: 768px) { .portal-card { flex-direction: column; } .left-panel { width: 100%; border-right: none; padding: 25px 20px; } .right-panel { display: none; } .portal-input { padding: 11px 13px 11px 36px; font-size: 12px; } .portal-btn { padding: 11px; font-size: 12px; } body { padding: 0; } }
        @media (max-width: 480px) { .portal-card { width: 100%; border-radius: 0; max-height: 100vh; } .left-panel { width: 100%; padding: 20px 16px; justify-content: flex-start; overflow-y: auto; } .portal-input { padding: 10px 12px 10px 34px; font-size: 11px; } .portal-btn { padding: 10px; font-size: 11px; } }
    </style>
</head>
<body>

{{-- Animated background --}}
<div class="bg-anim">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="portal-card">

    {{-- ── LEFT: Login Form ── --}}
    <div class="left-panel">

        {{-- Logo + School --}}
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:28px">
              <img src="{{ $siteLogoUrl ?? asset('images/logo.png') }}" alt="{{ $schoolName ?? 'Logo' }}"
                 style="width:54px;height:54px;object-fit:contain;margin:0 auto;display:block" />
                 <p style="color:#111827;font-size:11px;font-weight:700;line-height:1.1">{{ $schoolName ?? 'School' }}</p>
                 <p style="color:#5b21b6;font-size:10px;font-weight:500">{{ $schoolSubtitle ?? '' }}</p>
        </div>

        @if(isset($teachersPortalOpen) && !$teachersPortalOpen)
            {{-- ── CLOSED PORTAL NOTICE ── --}}
            <div class="closed-banner" style="margin-bottom:20px">
                <div style="width:48px;height:48px;border-radius:50%;background:#ef4444;color:#fff;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:20px">
                    <i class="fas fa-lock"></i>
                </div>
                <h3 style="font-size:16px;font-weight:800;margin:0 0 6px 0;color:#991b1b">Teachers Portal is Closed</h3>
                <p style="font-size:13px;line-height:1.5;margin:0;color:#7f1d1d">
                    {{ $teachersPortalMessage ?: 'The teachers portal is currently closed for score entry and maintenance.' }}
                </p>
            </div>
        @else

            {{-- ── LOGIN FORM ── --}}
            <div style="margin-bottom:20px">
                <h1 style="color:#111827;font-size:24px;font-weight:800;margin-bottom:4px;line-height:1.2">Welcome Back</h1>
                <p style="color:#7c3aed;font-size:12px">Sign in to your teacher account</p>
            </div>

            @if($errors->has('email') || $errors->has('password'))
            <div style="background:rgba(239,68,68,0.1);border:1.5px solid rgba(239,68,68,0.3);border-radius:12px;padding:14px 16px;margin-bottom:20px;display:flex;align-items:center;gap:10px">
                <i class="fas fa-exclamation-circle" style="color:#f87171;flex-shrink:0"></i>
                <p style="color:#dc2626;font-size:13px;margin:0">{{ $errors->first('email') ?: 'Invalid email or password' }}</p>
            </div>
            @endif

            <form action="{{ route('lecturer.login.submit') }}" method="POST">
                @csrf

                {{-- Email Input --}}
                <div style="margin-bottom:16px;position:relative">
                    <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7c3aed;font-size:14px">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}"
                        placeholder="your@email.com" class="portal-input"
                        style="padding:14px 16px 14px 44px" required />
                </div>

                {{-- Password Input --}}
                <div style="margin-bottom:16px;position:relative">
                    <div style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7c3aed;font-size:14px">
                        <i class="fas fa-lock"></i>
                    </div>
                    <input type="password" name="password"
                        placeholder="••••••••" class="portal-input"
                        style="padding:14px 16px 14px 44px" required />
                </div>

                {{-- Remember Me --}}
                <div style="margin-bottom:20px">
                    <label style="display:flex;align-items:center;gap:8px;color:#111827;font-size:13px">
                        <input type="checkbox" name="remember" style="width:16px;height:16px;cursor:pointer;accent-color:#6366f1" />
                        Remember me
                    </label>
                </div>

                {{-- Login Button --}}
                <button type="submit" class="portal-btn">
                    Sign In to Teacher Portal
                </button>
            </form>
        @endif

        {{-- Footer Links --}}
        <div class="portal-footer">
            <p style="color:#7c3aed;font-size:12px;margin:0">Need help? Contact your administrator</p>
            <div style="margin-top:8px">
                <a href="{{ route('login') }}" class="portal-link">← Back to main portals</a>
            </div>
        </div>

    </div>

    {{-- ── RIGHT: Branding ── --}}
    <div class="right-panel">
        <div class="float-shape" style="text-align:center;position:relative;z-index:1">
            <div style="width:80px;height:80px;border-radius:50%;background:rgba(255,255,255,0.2);margin:0 auto 24px;display:flex;align-items:center;justify-content:center;border:2px solid rgba(255,255,255,0.4)">
                <i class="fas fa-chalkboard-user" style="color:#fff;font-size:40px"></i>
            </div>
            <h1 style="color:#fff;font-size:32px;font-weight:800;margin:0 0 12px 0">Teacher Portal</h1>
            <p style="color:rgba(255,255,255,0.85);font-size:14px;line-height:1.6;max-width:280px">
                Manage your courses, track student attendance, enter exam scores, and view detailed performance reports.
            </p>
        </div>
    </div>

</div>

</body>
</html>
