<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ ucfirst($role ?? 'Staff') }} Portal — {{ \App\Models\Setting::get('school_name', config('app.name')) }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        @keyframes float { 0%,100%{transform:translateY(0) scale(1);} 50%{transform:translateY(-18px) scale(1.03);} }
        @keyframes shimmer { 0%{transform:translateX(-120%) skewX(-15deg);} 100%{transform:translateX(280%) skewX(-15deg);} }
        .orb { position:absolute; border-radius:50%; filter:blur(72px); pointer-events:none; animation:float 7s ease-in-out infinite; }
        .hero-bg::after { content:''; position:absolute; inset:0; background:linear-gradient(90deg,transparent,rgba(255,255,255,.05),transparent); animation:shimmer 4s ease-in-out infinite; pointer-events:none; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 relative overflow-hidden" style="background:linear-gradient(135deg,#0d0a2e 0%,#1a1550 20%,#2d2480 45%,#4c1d95 65%,#7c3aed 85%,#a78bfa 100%)">

    {{-- Background orbs --}}
    <div class="orb w-96 h-96 top-0 -right-20" style="background:radial-gradient(circle,rgba(245,166,35,.08),transparent 70%); animation-delay:0s;"></div>
    <div class="orb w-80 h-80 -bottom-16 -left-16" style="background:radial-gradient(circle,rgba(79,70,229,.12),transparent 70%); animation-delay:-3s;"></div>

    <div class="relative z-10 w-full max-w-md">

        {{-- School branding --}}
        <div class="text-center mb-8">
            @php $logoUrl = \App\Models\Setting::get('site_logo_url'); @endphp
            @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" class="h-14 w-14 object-contain rounded-2xl mx-auto mb-3 bg-white/10 p-1">
            @else
            <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 border border-white/20 mb-3">
                <i class="fas fa-shield-halved text-2xl text-violet-300"></i>
            </div>
            @endif
            <p class="text-sm font-extrabold text-violet-300 uppercase tracking-widest">
                {{ \App\Models\Setting::get('school_name', config('app.name')) }}
            </p>
            <p class="text-xs text-white/50 mt-0.5">Staff Portal Access</p>
        </div>

        {{-- Login card --}}
        <div class="rounded-3xl bg-white shadow-2xl overflow-hidden">

            {{-- Card header --}}
            @php
            $roleConfig = [
                'accountant'  => ['label'=>'Accountant Portal',  'icon'=>'fa-coins',          'from'=>'#d97706','to'=>'#b45309'],
                'headmaster'  => ['label'=>'Headmaster Portal',  'icon'=>'fa-crown',           'from'=>'#4f46e5','to'=>'#6d28d9'],
                'headteacher' => ['label'=>'Headteacher Portal', 'icon'=>'fa-chalkboard-user', 'from'=>'#0891b2','to'=>'#0e7490'],
                'deputy'      => ['label'=>'Deputy Head Portal', 'icon'=>'fa-user-tie',        'from'=>'#059669','to'=>'#047857'],
                'secretary'   => ['label'=>'Secretary Portal',   'icon'=>'fa-file-alt',        'from'=>'#db2777','to'=>'#be185d'],
                'staff'       => ['label'=>'Staff Portal',       'icon'=>'fa-user-shield',     'from'=>'#5647d6','to'=>'#4338ca'],
            ];
            $rc = $roleConfig[$role ?? 'staff'] ?? $roleConfig['staff'];
            @endphp
            <div class="px-8 py-7 text-white text-center"
                 style="background:linear-gradient(135deg,{{ $rc['from'] }},{{ $rc['to'] }})">
                <div class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 mb-3">
                    <i class="fas {{ $rc['icon'] }} text-2xl text-white"></i>
                </div>
                <h2 class="text-xl font-extrabold">{{ $rc['label'] }}</h2>
                <p class="text-white/70 text-xs mt-1">{{ \App\Models\Setting::get('school_name', config('app.name')) }}</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-7">
                @if($errors->any())
                <div class="rounded-xl bg-red-50 border border-red-200 px-4 py-3 mb-5 text-sm text-red-800 font-semibold">
                    <i class="fas fa-circle-exclamation mr-1.5"></i>
                    {{ $errors->first() }}
                </div>
                @endif

                <form method="POST" action="{{ route('staff-portal.login.submit') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="role" value="{{ $role ?? '' }}">

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Email Address</label>
                        <div class="relative">
                            <i class="fas fa-envelope absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                                placeholder="your@email.com"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:outline-none focus:ring-2 focus:border-transparent transition-all"
                                style="--tw-ring-color:{{ $rc['from'] }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">Password</label>
                        <div class="relative">
                            <i class="fas fa-lock absolute left-3.5 top-3.5 text-slate-400 text-sm"></i>
                            <input type="password" name="password" required
                                placeholder="••••••••"
                                class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-sm font-semibold focus:outline-none focus:ring-2 focus:border-transparent transition-all">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full py-3.5 rounded-xl text-white text-sm font-extrabold shadow-lg transition-all active:scale-95 hover:opacity-90"
                        style="background:linear-gradient(135deg,{{ $rc['from'] }},{{ $rc['to'] }})">
                        <i class="fas fa-sign-in-alt mr-2"></i> Sign In
                    </button>
                </form>

                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    @foreach(\App\Models\StaffPortalUser::$roleLabels as $r => $lbl)
                    @if($r !== ($role ?? 'staff'))
                    <a href="{{ route('staff-portal.login.role', $r) }}"
                       class="text-xs font-semibold text-slate-400 hover:text-slate-700 transition-colors">
                        {{ $lbl }} →
                    </a>
                    @endif
                    @endforeach
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-white/30 mt-6">
            Staff only. Unauthorised access is prohibited.
        </p>
    </div>
</body>
</html>
