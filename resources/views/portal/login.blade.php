<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal — {{ $schoolName ?? 'School' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; box-sizing: border-box; }
        body {
            margin: 0; height: 100vh; width: 100vw; max-height: 100vh; max-width: 100vw;
            padding: 0; overflow: hidden; background: #f3f4f6;
            display: flex; align-items: center; justify-content: center;
        }

        /* ── Animated background ─────────────────────────────────────── */
        .bg-anim { position: fixed; inset: 0; z-index: 0; overflow: hidden; background: linear-gradient(135deg, #f8fafc 0%, #f0f9ff 100%); pointer-events: none; }
        .orb { position: fixed; border-radius: 50%; filter: blur(80px); opacity: 0.26; animation: drift 8s ease-in-out infinite; pointer-events: none; }
        .orb-1 { width: 500px; height: 500px; background: #0a1f44; top: -100px; left: -100px; animation-delay: 0s; }
        .orb-2 { width: 420px; height: 420px; background: #0891b2; bottom: -120px; right: -80px; animation-delay: 3s; }
        .orb-3 { width: 320px; height: 320px; background: #e0f2fe; top: 40%; left: 55%; transform: translate(-50%,-50%); animation-delay: 1.5s; }
        @keyframes drift { 0%,100%{transform:translate(0,0)} 33%{transform:translate(20px,-20px)} 66%{transform:translate(-15px,15px)} }

        /* ── Card ────────────────────────────────────────────────────── */
        .portal-card {
            position: relative; z-index: 1000; pointer-events: auto; isolation: isolate;
            transform: translateZ(0); width: 90%; max-width: 900px; max-height: 90vh;
            overflow-y: auto; border-radius: 24px; display: flex;
            box-shadow: 0 40px 90px rgba(15,23,42,0.16);
            border: 1px solid rgba(255,255,255,0.7); background: #f8fdff;
        }

        /* ── Left panel (form) ───────────────────────────────────────── */
        .left-panel {
            width: 45%; position: relative; z-index: 2; pointer-events: auto;
            background: #ffffff; border-right: 1px solid #e0f2fe;
            padding: 40px 32px; display: flex; flex-direction: column; justify-content: center;
        }

        /* ── Right panel (branding) ──────────────────────────────────── */
        .right-panel {
            width: 55%; pointer-events: none; background: #0a1f44;
            padding: 40px 32px; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            position: relative; overflow: hidden;
        }
        .right-panel::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        /* ── Inputs ──────────────────────────────────────────────────── */
        .portal-input {
            width: 100%; background: #f8fafc; border: 1.5px solid #d8e0eb;
            border-radius: 12px; color: #111827; padding: 14px 16px 14px 44px;
            font-size: 14px; font-family: 'Poppins', sans-serif;
            transition: all 0.16s; outline: none;
        }
        .portal-input:focus { border-color: #0891b2; background: #ffffff; box-shadow: 0 0 0 3px rgba(8,145,178,0.14); }
        .portal-input::placeholder { color: #64748b; }

        /* ── Button ──────────────────────────────────────────────────── */
        .portal-btn {
            width: 100%; padding: 14px; border-radius: 12px;
            background: #0891b2; color: #ffffff;
            font-weight: 700; font-size: 14px; font-family: 'Poppins', sans-serif;
            border: none; cursor: pointer; transition: all 0.16s; letter-spacing: 0.3px;
        }
        .portal-btn:hover { background: #0e7490; box-shadow: 0 10px 26px rgba(8,145,178,0.3); transform: translateY(-1px); }

        /* ── Field label ─────────────────────────────────────────────── */
        .field-label {
            display: block; color: #0e7490; font-size: 10px; font-weight: 600;
            text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 6px;
        }

        /* ── Alerts ──────────────────────────────────────────────────── */
        .alert-error {
            background: rgba(239,68,68,0.08); border: 1.5px solid rgba(239,68,68,0.25);
            border-radius: 12px; padding: 13px 16px; margin-bottom: 18px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-success {
            background: rgba(16,185,129,0.08); border: 1.5px solid rgba(16,185,129,0.25);
            border-radius: 12px; padding: 13px 16px; margin-bottom: 18px;
            display: flex; align-items: flex-start; gap: 10px;
        }
        .alert-text { font-size: 13px; margin: 0; line-height: 1.5; }

        /* ── Closed banner ───────────────────────────────────────────── */
        .closed-banner {
            background: #fff8f0; border: 1.5px solid #fed7aa;
            border-radius: 16px; padding: 28px 24px; text-align: center;
        }

        /* ── Helper text ─────────────────────────────────────────────── */
        .helper-text { color: #64748b; font-size: 10px; margin-top: 4px; line-height: 1.5; }

        /* ── Right panel floating animation ──────────────────────────── */
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-14px)} }
        .float-wrap { animation: float 4.5s ease-in-out infinite; text-align: center; position: relative; z-index: 1; }

        /* ── Responsive ──────────────────────────────────────────────── */
        @media (max-width: 900px)  { .portal-card { width: 95%; max-height: 95vh; } .left-panel, .right-panel { padding: 35px 25px; } }
        @media (max-width: 768px)  { .portal-card { flex-direction: column; } .left-panel { width: 100%; border-right: none; padding: 28px 22px; } .right-panel { display: none; } body { padding: 0; } }
        @media (max-width: 480px)  { .portal-card { width: 100%; border-radius: 0; max-height: 100vh; } .left-panel { padding: 22px 18px; justify-content: flex-start; overflow-y: auto; } }
    </style>
</head>
<body>

<div class="bg-anim">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>
</div>

<div class="portal-card">

    {{-- ══ LEFT: Login form ════════════════════════════════════════════ --}}
    <div class="left-panel">

        {{-- School identity --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:28px;">
            @if(isset($siteLogoUrl) && $siteLogoUrl)
                <img src="{{ $siteLogoUrl }}" alt="{{ $schoolName ?? 'Logo' }}"
                     style="width:48px;height:48px;object-fit:contain;flex-shrink:0;">
            @else
                <div style="width:48px;height:48px;border-radius:14px;background:#0a1f44;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="fas fa-graduation-cap" style="color:#e9a422;font-size:22px;"></i>
                </div>
            @endif
            <div>
                <p style="color:#111827;font-size:12px;font-weight:700;line-height:1.2;margin:0;">{{ $schoolName ?? 'School' }}</p>
                @if(isset($schoolSubtitle) && $schoolSubtitle)
                <p style="color:#0e7490;font-size:10px;font-weight:500;margin:2px 0 0 0;">{{ $schoolSubtitle }}</p>
                @endif
            </div>
        </div>

        @if(!$portalOpen)
        {{-- ── PORTAL CLOSED ─────────────────────────────────────────── --}}
        <div class="closed-banner">
            <div style="width:56px;height:56px;border-radius:50%;background:rgba(234,88,12,0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="fas fa-lock" style="color:#ea580c;font-size:22px;"></i>
            </div>
            <h2 style="color:#111827;font-size:17px;font-weight:800;margin:0 0 8px;">Portal Currently Closed</h2>
            <p style="color:#64748b;font-size:12px;line-height:1.65;margin:0 0 14px;">
                @if($portalMessage){{ $portalMessage }}@else The student portal is not currently available. Please check back later.@endif
            </p>
            <div style="padding-top:14px;border-top:1px solid #fed7aa;">
                <p style="color:#92400e;font-size:11px;margin:0;"><i class="fas fa-info-circle" style="margin-right:5px;"></i>Contact your administrator for access</p>
            </div>
        </div>

        @else
        {{-- ── LOGIN FORM ─────────────────────────────────────────────── --}}
        <div style="margin-bottom:20px;">
            <h1 style="color:#111827;font-size:24px;font-weight:800;margin:0 0 4px;line-height:1.2;">Welcome Back</h1>
            <p style="color:#0e7490;font-size:12px;margin:0;">Sign in to your student portal</p>
        </div>

        @if($errors->has('credentials'))
        <div class="alert-error">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;flex-shrink:0;margin-top:1px;"></i>
            <p class="alert-text" style="color:#b91c1c;">{{ $errors->first('credentials') }}</p>
        </div>
        @endif

        @if(session('error'))
        <div class="alert-error">
            <i class="fas fa-exclamation-circle" style="color:#ef4444;flex-shrink:0;margin-top:1px;"></i>
            <p class="alert-text" style="color:#b91c1c;">{{ session('error') }}</p>
        </div>
        @endif

        @if(session('success'))
        <div class="alert-success">
            <i class="fas fa-check-circle" style="color:#10b981;flex-shrink:0;margin-top:1px;"></i>
            <p class="alert-text" style="color:#065f46;">{{ session('success') }}</p>
        </div>
        @endif

        <form method="POST" action="{{ route('portal.login.submit') }}" style="display:flex;flex-direction:column;gap:14px;">
            @csrf

            {{-- Registration Number --}}
            <div>
                <label class="field-label">Registration Number</label>
                <div style="position:relative;">
                    <i class="fas fa-id-badge" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#0891b2;font-size:13px;pointer-events:none;"></i>
                    <input type="text" name="student_id" value="{{ old('student_id') }}"
                           placeholder="e.g. KMC/KA/PC/001"
                           class="portal-input" required autofocus>
                </div>
            </div>

            {{-- Full Name --}}
            <div>
                <label class="field-label">Full Name</label>
                <div style="position:relative;">
                    <i class="fas fa-user" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#0891b2;font-size:13px;pointer-events:none;"></i>
                    <input type="text" name="full_name" value="{{ old('full_name') }}"
                           placeholder="Enter your full name as registered"
                           class="portal-input" required>
                </div>
                <p class="helper-text"><i class="fas fa-info-circle" style="margin-right:3px;color:#0891b2;"></i>Must match your registered name exactly.</p>
            </div>

            {{-- Password --}}
            <div>
                <label class="field-label">Portal Password</label>
                <div style="position:relative;">
                    <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#0891b2;font-size:13px;pointer-events:none;"></i>
                    <input type="password" name="password"
                           placeholder="••••••••"
                           class="portal-input" required>
                </div>
                <p class="helper-text"><i class="fas fa-info-circle" style="margin-right:3px;color:#0891b2;"></i>Set by your administrator or changed by you.</p>
            </div>

            <button type="submit" class="portal-btn" style="margin-top:4px;">
                <i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>
                Access Student Portal
            </button>
        </form>
        @endif

        <div style="margin-top:20px;text-align:center;">
            <p style="color:#64748b;font-size:11px;margin:0 0 6px;">Need help? Contact your administrator</p>
            <a href="{{ route('login') }}" style="color:#0891b2;font-size:12px;font-weight:600;text-decoration:none;transition:opacity .15s;" onmouseover="this.style.opacity='.7'" onmouseout="this.style.opacity='1'">
                ← Back to main portals
            </a>
        </div>

        <p style="color:#cbd5e1;font-size:10px;text-align:center;margin-top:18px;">
            &copy; {{ date('Y') }} {{ $schoolName ?? 'School' }}{{ isset($schoolSubtitle) && $schoolSubtitle ? ' — '.$schoolSubtitle : '' }}
        </p>
    </div>

    {{-- ══ RIGHT: Branding panel ════════════════════════════════════════ --}}
    <div class="right-panel">
        <div class="float-wrap">
            {{-- Icon circle --}}
            <div style="width:88px;height:88px;border-radius:50%;background:rgba(255,255,255,0.12);border:2px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                <i class="fas fa-user-graduate" style="color:#ffffff;font-size:42px;"></i>
            </div>

            <h1 style="color:#ffffff;font-size:30px;font-weight:800;margin:0 0 12px;line-height:1.2;">Student Portal</h1>
            <p style="color:rgba(255,255,255,.75);font-size:13px;line-height:1.7;max-width:280px;margin:0 auto 24px;">
                Access your academic records, report cards, attendance history, fee status and notifications — all in one place.
            </p>

            {{-- Feature pills --}}
            <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:8px;max-width:300px;margin:0 auto;">
                @foreach(['📋 Report Cards', '📊 Attendance', '💰 Fee Status', '🎓 My Profile', '📈 Results', '🔔 Notifications'] as $feat)
                <span style="padding:6px 13px;border-radius:100px;font-size:11px;font-weight:600;color:rgba(255,255,255,.9);background:rgba(255,255,255,.1);border:1px solid rgba(255,255,255,.18);backdrop-filter:blur(8px);">
                    {{ $feat }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

</div>

</body>
</html>
