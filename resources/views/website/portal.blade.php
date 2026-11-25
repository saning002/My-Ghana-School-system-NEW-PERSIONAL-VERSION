@extends('website.layout')
@section('page_title', ($site->site_name ?? 'School') . ' — Portals')
@section('meta_description','Access the '.($site->site_name ?? 'School').' portals.')

@section('extra_css')
<style>
/* ═══════════════════════════════════════════════════════
   PORTAL PAGE — fluid, fits every screen, all content
   visible on mobile (card first, welcome below)
   ═══════════════════════════════════════════════════════ */
html, body { overflow-x: hidden; }

.ph {
  min-height: 100dvh;
  display: flex; align-items: center;
  padding-top: 76px; padding-bottom: 48px;
  position: relative; overflow: hidden;
}

/* ── Animated background ──────────────────────────────── */
.ph-grid {
  position: absolute; inset: 0; z-index: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
  background-size: 56px 56px;
  animation: grid-drift 24s linear infinite;
}
@keyframes grid-drift {
  from { background-position: 0 0; }
  to   { background-position: 56px 56px; }
}

.ph-orb {
  position: absolute; border-radius: 50%; pointer-events: none; z-index: 1;
  animation: orb-pulse 7s ease-in-out infinite;
}
.ph-orb-gold {
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(233,164,34,.18) 0%, transparent 65%);
  top: -150px; right: -100px;
}
.ph-orb-blue {
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(8,145,178,.15) 0%, transparent 65%);
  bottom: -100px; left: -60px; animation-delay: 3.5s;
}
@keyframes orb-pulse {
  0%,100% { transform: scale(1);    opacity: .8; }
  50%      { transform: scale(1.15); opacity: 1; }
}

/* Floating particles */
.ph-particle {
  position: absolute; border-radius: 50%; pointer-events: none; z-index: 2;
  animation: float-up linear infinite;
}
@keyframes float-up {
  0%   { transform: translateY(0) rotate(0); opacity: .5; }
  50%  { opacity: .3; }
  100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
}

.ph-shape {
  position: absolute; pointer-events: none; z-index: 2;
  animation: float-shape linear infinite;
}
.ph-shape.square  { border-radius: 4px; }
.ph-shape.diamond { border-radius: 2px; }
.ph-shape.ring    { border-radius: 50%; background: transparent !important; border-style: solid; }
@keyframes float-shape {
  0%   { transform: translateY(0) rotate(0deg) scale(1); opacity: .4; }
  50%  { transform: translateY(-50vh) rotate(180deg) scale(1.1); opacity: .25; }
  100% { transform: translateY(-100vh) rotate(360deg) scale(.8); opacity: 0; }
}

.ph-wave {
  position: absolute; left: -100%; top: 0; width: 60%; height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255,255,255,.025), transparent);
  z-index: 1; pointer-events: none;
  animation: wave-sweep 8s ease-in-out infinite;
}
.ph-wave:nth-of-type(2) { animation-delay: 4s; width: 40%; }
@keyframes wave-sweep { 0%{left:-60%} 100%{left:110%} }

.ph-ring {
  position: absolute; border-radius: 50%; pointer-events: none; z-index: 1;
  border: 1px solid rgba(233,164,34,.12);
  animation: ring-spin linear infinite;
}
@keyframes ring-spin { from{transform:rotate(0)} to{transform:rotate(360deg)} }

/* ── Layout ───────────────────────────────────────────── */
.ph-inner {
  position: relative; z-index: 2;
  width: 100%; max-width: 1160px; margin: 0 auto;
  padding: 48px 28px;
  display: grid;
  grid-template-columns: 1fr 440px;
  gap: 64px;
  align-items: center;
}

/* ── Left welcome panel ───────────────────────────────── */
.pl { animation: slide-left .9s cubic-bezier(.22,1,.36,1) both; }
@keyframes slide-left {
  from { opacity: 0; transform: translateX(-36px); }
  to   { opacity: 1; transform: translateX(0); }
}

.pl-welcome {
  display: inline-flex; align-items: center; gap: 10px;
  background: rgba(233,164,34,.12); border: 1px solid rgba(233,164,34,.28);
  color: var(--gold, #e9a422); padding: 7px 18px; border-radius: 100px;
  font-size: clamp(.58rem, 1.6vw, .68rem); font-weight: 800;
  letter-spacing: .15em; text-transform: uppercase; margin-bottom: 18px;
}
.pl-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--gold,#e9a422); animation: blink 1.6s ease-in-out infinite; flex-shrink:0; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.2} }

.pl-greet {
  font-size: clamp(.92rem, 2.2vw, 1.1rem); font-weight: 700;
  color: rgba(255,255,255,.65); margin-bottom: 10px;
  animation: fade-in .7s ease .15s both;
}
@keyframes fade-in { from{opacity:0} to{opacity:1} }

.pl-title {
  font-size: clamp(1.7rem, 4.5vw, 3.4rem); font-weight: 900; color: #fff;
  line-height: 1.12; letter-spacing: -.02em; margin-bottom: 16px;
}
.pl-title em { font-style: normal; color: var(--gold, #e9a422); }

.pl-sub {
  font-size: clamp(.88rem, 2vw, 1rem); color: rgba(255,255,255,.52);
  line-height: 1.75; max-width: 430px; margin-bottom: 32px;
}

.pl-feats { display: flex; flex-direction: column; gap: 9px; margin-bottom: 32px; }
.pl-feat {
  display: flex; align-items: center; gap: 13px;
  background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.07);
  border-radius: 13px; padding: 11px 15px;
  transition: background .2s, border-color .2s, transform .2s;
}
.pl-feat:hover { background: rgba(255,255,255,.08); border-color: rgba(233,164,34,.22); transform: translateX(4px); }
.pl-feat-icon {
  width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
  background: linear-gradient(135deg, var(--gold,#e9a422), var(--gold-dark,#b8860e));
  display: flex; align-items: center; justify-content: center;
  color: var(--navy,#0a1f44); font-size: .88rem;
}
.pl-feat strong { display: block; color: #fff; font-size: .86rem; font-weight: 700; }
.pl-feat span   { font-size: .73rem; color: rgba(255,255,255,.38); }

.pl-stats {
  display: flex; gap: 28px; padding-top: 22px;
  border-top: 1px solid rgba(255,255,255,.07);
}
.pl-stat strong { display: block; font-size: 1.5rem; font-weight: 900; color: var(--gold,#e9a422); line-height: 1; }
.pl-stat span   { font-size: .6rem; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.28); margin-top: 3px; display: block; }

/* ── Right access card ────────────────────────────────── */
.pc {
  background: rgba(255,255,255,.97); border-radius: 26px; overflow: hidden;
  box-shadow: 0 40px 90px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.1);
  animation: slide-right .9s cubic-bezier(.22,1,.36,1) .08s both;
  max-width: 100%; width: 100%;
}
@keyframes slide-right {
  from { opacity: 0; transform: translateX(36px) scale(.97); }
  to   { opacity: 1; transform: translateX(0) scale(1); }
}

.pc-head {
  background: var(--navy, #0a1f44);
  padding: 24px 26px 20px; text-align: center;
}
.pc-shield {
  width: 60px; height: 60px; border-radius: 18px; margin: 0 auto 12px;
  background: linear-gradient(135deg, var(--gold,#e9a422), var(--gold-dark,#b8860e));
  display: flex; align-items: center; justify-content: center;
  font-size: 1.6rem; color: var(--navy,#0a1f44);
  box-shadow: 0 8px 24px rgba(233,164,34,.38);
  animation: shield-pop .8s cubic-bezier(.34,1.56,.64,1) .3s both;
}
@keyframes shield-pop {
  from { transform: scale(0) rotate(-15deg); opacity: 0; }
  to   { transform: scale(1) rotate(0);      opacity: 1; }
}
.pc-welcome { font-size: .72rem; color: rgba(255,255,255,.45); margin-bottom: 3px; }
.pc-head h2 { font-size: 1.1rem; font-weight: 800; color: #fff; margin-bottom: 2px; }
.pc-head p  { font-size: .76rem; color: rgba(255,255,255,.4); }

.pc-body { padding: 20px 22px; }

/* Buttons */
.pa-list { display: flex; flex-direction: column; gap: 8px; }

.pa-btn {
  display: flex; align-items: center; gap: 12px; padding: 13px 14px;
  border-radius: 13px; text-decoration: none; cursor: pointer; border: none;
  width: 100%; text-align: left; color: #fff; font-family: inherit;
  transition: transform .18s, filter .18s;
  position: relative;
}
.pa-btn:hover { transform: translateY(-2px); filter: brightness(1.08); }
.pa-btn-nav   { background: linear-gradient(135deg, var(--navy,#0a1f44), var(--navy-mid,#1a3a7a)); box-shadow: 0 3px 14px rgba(10,31,68,.4); }
.pa-btn-teach { background: linear-gradient(135deg, #5b21b6, #7c3aed); box-shadow: 0 3px 14px rgba(91,33,182,.35); }
.pa-btn-stu   { background: linear-gradient(135deg, var(--teal,#0369a1), #0891b2); box-shadow: 0 3px 14px rgba(3,105,161,.35); }

.pa-icon {
  width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
  background: rgba(255,255,255,.16);
  display: flex; align-items: center; justify-content: center; font-size: .95rem;
}
.pa-strong { display: block; font-weight: 700; font-size: .86rem; margin-bottom: 1px; }
.pa-sub    { display: block; font-size: .7rem; opacity: .6; }
.pa-arr    { margin-left: auto; font-size: .6rem; opacity: .4; flex-shrink: 0; }

/* Dropdown */
.pa-wrap { position: relative; }
.pa-chev { margin-left: auto; font-size: .68rem; opacity: .5; transition: transform .22s; flex-shrink: 0; }
.pa-wrap.open .pa-chev { transform: rotate(180deg); }

.pa-drop {
  display: none; margin-top: 5px;
  background: #fff; border-radius: 14px; overflow: hidden;
  border: 1px solid #e8edf2;
  box-shadow: 0 16px 44px rgba(0,0,0,.14);
}
.pa-wrap.open .pa-drop { display: block; }

.pa-role {
  display: flex; align-items: center; gap: 11px; padding: 11px 14px;
  text-decoration: none; background: #fff; border-bottom: 1px solid #f3f7fb;
  transition: background .15s, transform .15s;
}
.pa-role:last-child { border-bottom: none; }
.pa-role:hover { background: #f7fafd; transform: translateX(3px); }
.pa-role-dot {
  width: 32px; height: 32px; border-radius: 9px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; color: #fff;
}
.pa-role-name { font-size: .82rem; font-weight: 700; color: #1e293b; display: block; }
.pa-role-sub  { font-size: .68rem; color: #94a3b8; display: block; }
.pa-role-arr  { margin-left: auto; font-size: .58rem; color: #d1d5db; }

/* Divider */
.pa-div { display:flex; align-items:center; gap:9px; margin:12px 0; }
.pa-div::before,.pa-div::after { content:''; flex:1; height:1px; background:#e8edf2; }
.pa-div span { font-size:.62rem; color:#94a3b8; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }

/* Info note */
.pa-note {
  background: #f8fafc; border-radius: 11px; padding: 11px 13px;
  font-size: .76rem; color: #64748b; line-height: 1.6;
  display: flex; gap: 9px; align-items: flex-start;
}
.pa-note i { color: var(--gold-dark,#b8860e); flex-shrink: 0; margin-top: 2px; }

/* ═══════════════════════════════════════════════════════
   RESPONSIVE — all content visible, just re-arranged
   ═══════════════════════════════════════════════════════ */
@media (max-width: 1024px) {
  .ph-inner {
    grid-template-columns: 1fr;
    gap: 40px;
    max-width: 540px;
    padding: 40px 22px;
  }
  /* Card first — the most important action */
  .pc  { order: 1; }
  .pl  { order: 2; animation: slide-up .7s cubic-bezier(.22,1,.36,1) both; }
  @keyframes slide-up {
    from { opacity: 0; transform: translateY(24px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  /* Features become a 2-col grid — still visible, more compact */
  .pl-feats { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
  .pl-title { font-size: clamp(1.7rem, 5vw, 2.4rem); }
  .pl-sub   { max-width: 100%; }
}

@media (max-width: 640px) {
  .ph { padding-top: 70px; padding-bottom: 32px; }
  .ph-inner { padding: 32px 16px 36px; gap: 32px; }

  /* Welcome copy — compact but fully visible */
  .pl-welcome { font-size: .58rem; padding: 6px 13px; margin-bottom: 14px; }
  .pl-greet   { font-size: .9rem; margin-bottom: 8px; }
  .pl-title   { font-size: clamp(1.45rem, 6.5vw, 1.9rem); margin-bottom: 12px; }
  .pl-sub     { font-size: .86rem; margin-bottom: 22px; line-height: 1.7; }

  /* Features single column, tighter */
  .pl-feats { grid-template-columns: 1fr; gap: 7px; margin-bottom: 22px; }
  .pl-feat  { padding: 9px 12px; gap: 11px; }
  .pl-feat-icon { width: 32px; height: 32px; font-size: .8rem; }
  .pl-feat strong { font-size: .8rem; }
  .pl-feat span   { font-size: .68rem; }

  /* Stats row — full width, still visible */
  .pl-stats { gap: 18px; padding-top: 16px; }
  .pl-stat strong { font-size: 1.2rem; }
  .pl-stat span   { font-size: .55rem; }

  /* Card */
  .pc    { border-radius: 20px; }
  .pc-head { padding: 20px 16px 16px; }
  .pc-shield { width: 52px; height: 52px; font-size: 1.35rem; margin-bottom: 9px; }
  .pc-head h2 { font-size: 1rem; }
  .pc-body { padding: 16px 14px; }

  .pa-btn   { padding: 11px 12px; gap: 11px; border-radius: 12px; }
  .pa-icon  { width: 34px; height: 34px; font-size: .85rem; }
  .pa-strong{ font-size: .82rem; }
  .pa-sub   { font-size: .67rem; }

  .pa-role  { padding: 10px 12px; gap: 10px; }
  .pa-role-dot { width: 30px; height: 30px; font-size: .7rem; }
  .pa-role-name { font-size: .78rem; }
  .pa-role-sub  { font-size: .64rem; }

  .pa-note  { font-size: .72rem; padding: 10px 11px; }

  /* Fewer background particles on small screens = faster + cleaner */
  .ph-particle:nth-child(n+9), .ph-shape:nth-child(n+8), .ph-ring { display: none; }
}

@media (max-width: 380px) {
  .pl-title { font-size: 1.35rem; }
  .pl-greet { font-size: .82rem; }
  .pl-sub   { font-size: .82rem; }
  .pa-sub   { font-size: .63rem; }
  .pc-head h2 { font-size: .95rem; }
}
</style>
@endsection

@section('content')
@php $tenantSlug = session('tenant_slug'); @endphp

<section class="ph" style="background:{{ $theme['secondary'] ?? '#0a1f44' }}">
  <div class="ph-grid"></div>
  <div class="ph-orb ph-orb-gold"></div>
  <div class="ph-orb ph-orb-blue"></div>

  <div class="ph-wave"></div>
  <div class="ph-wave"></div>

  <div class="ph-ring" style="width:300px;height:300px;top:10%;right:5%;animation-duration:25s;"></div>
  <div class="ph-ring" style="width:180px;height:180px;top:15%;right:9%;animation-duration:18s;animation-direction:reverse;border-color:rgba(8,145,178,.14);"></div>
  <div class="ph-ring" style="width:500px;height:500px;bottom:-10%;left:-5%;animation-duration:40s;border-color:rgba(233,164,34,.07);"></div>

  {{-- Floating circles — deterministic positions (stable across renders) --}}
  @for($i=0;$i<16;$i++)
  @php
    $pc = ['rgba(233,164,34,.7)','rgba(255,255,255,.45)','rgba(8,145,178,.65)','rgba(233,164,34,.4)'][$i%4];
    $sz = 5 + ($i % 6);
  @endphp
  <div class="ph-particle" style="
    width:{{ $sz }}px; height:{{ $sz }}px;
    background:{{ $pc }};
    left:{{ ($i * 61) % 95 + 3 }}%; bottom:{{ ($i * 29) % 30 }}%;
    animation-duration:{{ 8 + ($i * 3) % 9 }}s;
    animation-delay:-{{ ($i * 7) % 16 }}s;
  "></div>
  @endfor

  {{-- Floating shapes — deterministic --}}
  @php
    $shapes = ['square','diamond','ring'];
    $shapeColors = ['rgba(233,164,34,.5)','rgba(255,255,255,.3)','rgba(8,145,178,.5)'];
  @endphp
  @for($i=0;$i<14;$i++)
  @php
    $shape = $shapes[$i%3];
    $col   = $shapeColors[$i%3];
    $sz    = 6 + ($i % 9);
  @endphp
  <div class="ph-shape {{ $shape }}" style="
    width:{{ $sz }}px; height:{{ $sz }}px;
    {{ $shape === 'ring' ? 'border:2px solid '.$col.';' : 'background:'.$col.';' }}
    left:{{ ($i * 67) % 90 + 5 }}%; bottom:{{ ($i * 31) % 40 }}%;
    animation-duration:{{ 10 + ($i * 5) % 11 }}s;
    animation-delay:-{{ ($i * 11) % 18 }}s;
    transform-origin: center;
  "></div>
  @endfor

  <div class="ph-inner">

    {{-- ══ LEFT WELCOME PANEL ══════════════════════════════════ --}}
    <div class="pl">
      <div class="pl-welcome"><span class="pl-dot"></span> Official School Portals</div>

      <p class="pl-greet">
        <i class="fas fa-hand-sparkles" style="margin-right:7px;"></i>
        Welcome to <strong style="color:var(--gold,#e9a422)">{{ $site->site_name ?? 'our school' }}</strong>
      </p>

      <h1 class="pl-title">{!! $site->portal_intro_title ?? 'Your Gateway to <em>School Life</em>' !!}</h1>
      <p class="pl-sub">{{ $site->portal_intro_subtitle ?? 'Everything your school community needs — results, attendance, fees, and more — all in one secure place. Choose your portal below to get started.' }}</p>

      <div class="pl-feats">
        @foreach([
          ['fa-users-gear',    'Student &amp; Parent Management',  'Profiles, enrollment and guardian records'],
          ['fa-graduation-cap','Academic Records &amp; Results',   'Report cards, grades and transcripts'],
          ['fa-chart-line',    'Real-Time Analytics',              'Live dashboards and performance insights'],
          ['fa-shield-halved', 'Role-Based Secure Access',         'Every user sees only what they need'],
        ] as [$ic,$t,$s])
        <div class="pl-feat">
          <div class="pl-feat-icon"><i class="fas {{ $ic }}"></i></div>
          <div><strong>{!! $t !!}</strong><span>{{ $s }}</span></div>
        </div>
        @endforeach
      </div>

      <div class="pl-stats">
        <div class="pl-stat"><strong>5+</strong><span>Portals</span></div>
        <div class="pl-stat"><strong>SSL</strong><span>Secured</span></div>
        <div class="pl-stat"><strong>24/7</strong><span>Access</span></div>
      </div>
    </div>

    {{-- ══ RIGHT ACCESS CARD ══════════════════════════════════ --}}
    <div class="pc">
      <div class="pc-head" style="background:{{ $theme['secondary'] ?? '#0a1f44' }}">
        <div class="pc-shield"><i class="fas fa-school"></i></div>
        <p class="pc-welcome">Welcome back</p>
        <h2>{{ $site->site_name ?? 'School' }}</h2>
        <p>Select your role and sign in securely</p>
      </div>

      <div class="pc-body">
        <div class="pa-list">

          {{-- Admin & Staff dropdown --}}
          @php
            $adminUrl = $tenantSlug ? route('school.admin.login', $tenantSlug) : url('/login');
            $roles = [
              ['Super Admin',  'Full system access',  'fa-shield-halved',  '#0a1f44', null],
              ['Headmaster',   'Academic leadership', 'fa-user-tie',       '#166534', 'headmaster'],
              ['Headteacher',  'School management',   'fa-chalkboard',     '#0f766e', 'headteacher'],
              ['Deputy Head',  'Deputy management',   'fa-user-check',     '#0369a1', 'deputy'],
              ['Accountant',   'Fees &amp; finance',  'fa-calculator',     '#92400e', 'accountant'],
              ['Secretary',    'Administration',      'fa-clipboard-user', '#6b21a8', 'secretary'],
            ];
          @endphp
          <div class="pa-wrap" id="adminWrap">
            <button type="button" class="pa-btn pa-btn-nav" onclick="toggleDrop()">
              <div class="pa-icon"><i class="fas fa-crown"></i></div>
              <div>
                <span class="pa-strong">Admin &amp; Staff Portal</span>
                <span class="pa-sub">Administrators, heads, accountants &amp; secretaries</span>
              </div>
              <i class="fas fa-chevron-down pa-chev"></i>
            </button>
            <div class="pa-drop" id="adminDrop">
              @foreach($roles as [$label,$sub,$icon,$color,$role])
              @php
                if ($role) {
                  try { $url = route('staff-portal.login.role', $role) . ($tenantSlug ? '?tenant=' . $tenantSlug : ''); }
                  catch (\Throwable $e) { $url = $adminUrl; }
                } else { $url = $adminUrl; }
              @endphp
              <a href="{{ $url }}" class="pa-role">
                <div class="pa-role-dot" style="background:{{ $color }}">
                  <i class="fas {{ $icon }}"></i>
                </div>
                <div>
                  <span class="pa-role-name">{{ $label }}</span>
                  <span class="pa-role-sub">{{ $sub }}</span>
                </div>
                <i class="fas fa-arrow-right pa-role-arr"></i>
              </a>
              @endforeach
            </div>
          </div>

          {{-- Teacher --}}
          <a href="{{ $tenantSlug ? route('school.lecturer.login', $tenantSlug) : route('teachers-portal.login') }}" class="pa-btn pa-btn-teach">
            <div class="pa-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div>
              <span class="pa-strong">{{ $site->portal_teacher_title ?? 'Teacher Portal' }}</span>
              <span class="pa-sub">{{ $site->portal_teacher_desc ?? 'Attendance, scores and class work' }}</span>
            </div>
            <i class="fas fa-arrow-right pa-arr"></i>
          </a>

          {{-- Student — always carry the tenant param so the login works --}}
          <a href="{{ route('student-portal.login') . ($tenantSlug ? '?tenant=' . $tenantSlug : '') }}" class="pa-btn pa-btn-stu">
            <div class="pa-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
              <span class="pa-strong">{{ $site->portal_student_title ?? 'Student Portal' }}</span>
              <span class="pa-sub">{{ $site->portal_student_desc ?? 'Results, attendance and fees' }}</span>
            </div>
            <i class="fas fa-arrow-right pa-arr"></i>
          </a>

        </div>

        <div class="pa-div"><span>Info</span></div>

        <div class="pa-note">
          <i class="fas fa-info-circle"></i>
          <span>{{ $site->portal_help_text ?? 'Not sure which portal? Contact your school administrator for your login details.' }}</span>
        </div>

        @if(isset($site) && $site->contact_phone)
        <div class="pa-note" style="margin-top:7px;background:rgba(233,164,34,.07);border:1px solid rgba(233,164,34,.2);">
          <i class="fas fa-phone"></i>
          <span style="color:#374151;">Help line: <strong><a href="tel:{{ $site->contact_phone }}" style="color:var(--navy,#0a1f44)">{{ $site->contact_phone }}</a></strong></span>
        </div>
        @endif
      </div>
    </div>

  </div>
</section>
@endsection

@section('extra_js')
<script>
function toggleDrop() {
    var wrap = document.getElementById('adminWrap');
    var drop = document.getElementById('adminDrop');
    var open = wrap.classList.contains('open');
    wrap.classList.toggle('open', !open);
    if (drop) drop.style.display = open ? 'none' : 'block';
}
document.addEventListener('click', function(e) {
    var wrap = document.getElementById('adminWrap');
    if (wrap && !wrap.contains(e.target)) {
        wrap.classList.remove('open');
        var drop = document.getElementById('adminDrop');
        if (drop) drop.style.display = 'none';
    }
});
</script>
@endsection
