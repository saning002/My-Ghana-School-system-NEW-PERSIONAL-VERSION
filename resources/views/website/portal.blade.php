@extends('website.layout')
@section('page_title', ($site->site_name ?? 'School') . ' — Portals')
@section('meta_description','Access the '.($site->site_name ?? 'School').' portals.')

@section('extra_css')
<style>
/* ═══════════════════════════════════════════════════════════
   PORTAL PAGE — full-screen, fits any device
   ═══════════════════════════════════════════════════════════ */
html, body { overflow-x: hidden; }

.pp-wrap {
  min-height: 100dvh;
  padding-top: 76px; /* navbar height */
  display: flex;
  align-items: stretch;
  position: relative;
  overflow: hidden;
}

/* ── Animated background ───────────────────────────────── */
.pp-bg {
  position: fixed; inset: 0; z-index: 0; pointer-events: none;
}
.pp-bg-grad {
  position: absolute; inset: 0;
  background: linear-gradient(145deg,
    {{ $theme['secondary'] ?? '#0a1f44' }} 0%,
    {{ $theme['navBg']     ?? '#0d2857' }} 55%,
    {{ $theme['secondary'] ?? '#0a1f44' }} 100%);
}
.pp-grid {
  position: absolute; inset: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.035) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.035) 1px, transparent 1px);
  background-size: 52px 52px;
  animation: grid-scroll 30s linear infinite;
}
@keyframes grid-scroll {
  from { background-position: 0 0; }
  to   { background-position: 52px 52px; }
}
.pp-orb {
  position: absolute; border-radius: 50%; pointer-events: none;
  animation: orb-float 9s ease-in-out infinite;
  filter: blur(70px);
}
.pp-orb-1 {
  width: 480px; height: 480px;
  background: radial-gradient(circle, rgba(233,164,34,.14) 0%, transparent 60%);
  top: -120px; right: -80px;
}
.pp-orb-2 {
  width: 360px; height: 360px;
  background: radial-gradient(circle, rgba(8,145,178,.12) 0%, transparent 60%);
  bottom: -80px; left: -60px; animation-delay: 4s;
}
@keyframes orb-float {
  0%,100% { transform: scale(1); opacity: .7; }
  50%      { transform: scale(1.18); opacity: 1; }
}

/* Particle dots */
.pp-dot {
  position: absolute; border-radius: 50%; pointer-events: none; opacity: .45;
  animation: dot-rise linear infinite;
}
@keyframes dot-rise {
  0%   { transform: translateY(0) scale(1);   opacity: .45; }
  100% { transform: translateY(-110vh) scale(.6); opacity: 0; }
}

/* ── Inner layout ──────────────────────────────────────── */
.pp-inner {
  position: relative; z-index: 2;
  width: 100%; max-width: 1200px; margin: 0 auto;
  padding: 60px 28px 60px;
  display: grid;
  grid-template-columns: 1fr 480px;
  gap: 64px;
  align-items: center;
}

/* ── LEFT — welcome copy ───────────────────────────────── */
.pp-left {
  animation: slide-in-left .85s cubic-bezier(.22,1,.36,1) both;
}
@keyframes slide-in-left {
  from { opacity: 0; transform: translateX(-32px); }
  to   { opacity: 1; transform: translateX(0); }
}

.pp-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(233,164,34,.12); border: 1px solid rgba(233,164,34,.28);
  color: #e9a422; padding: 6px 16px; border-radius: 100px;
  font-size: .68rem; font-weight: 800; letter-spacing: .14em;
  text-transform: uppercase; margin-bottom: 22px;
}
.pp-pulse {
  width: 7px; height: 7px; border-radius: 50%; background: #e9a422;
  animation: pulse 1.6s ease-in-out infinite;
}
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:.15} }

.pp-greeting {
  font-size: 1rem; font-weight: 600;
  color: rgba(255,255,255,.55); margin-bottom: 10px;
}
.pp-title {
  font-size: clamp(2rem, 4.2vw, 3.4rem);
  font-weight: 900; color: #fff;
  line-height: 1.1; letter-spacing: -.03em;
  margin-bottom: 18px;
}
.pp-title em { font-style: normal; color: #e9a422; }

.pp-desc {
  font-size: 1rem; color: rgba(255,255,255,.5);
  line-height: 1.85; max-width: 420px; margin-bottom: 36px;
}

/* Feature rows */
.pp-features { display: flex; flex-direction: column; gap: 10px; margin-bottom: 36px; }
.pp-feat {
  display: flex; align-items: center; gap: 14px;
  background: rgba(255,255,255,.05); border: 1px solid rgba(255,255,255,.08);
  border-radius: 14px; padding: 12px 16px;
  transition: background .2s, transform .2s;
}
.pp-feat:hover { background: rgba(255,255,255,.09); transform: translateX(4px); }
.pp-feat-icon {
  width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
  background: linear-gradient(135deg,#e9a422,#b8860e);
  display: flex; align-items: center; justify-content: center;
  color: #0a1f44; font-size: .88rem;
}
.pp-feat-title { display: block; color: #fff; font-size: .84rem; font-weight: 700; }
.pp-feat-sub   { display: block; color: rgba(255,255,255,.35); font-size: .7rem; margin-top:1px; }

/* Stats */
.pp-stats {
  display: flex; gap: 28px; padding-top: 22px;
  border-top: 1px solid rgba(255,255,255,.08);
}
.pp-stat strong { display:block; font-size:1.5rem; font-weight:900; color:#e9a422; line-height:1; }
.pp-stat span   { display:block; font-size:.62rem; color:rgba(255,255,255,.3); text-transform:uppercase; letter-spacing:.1em; margin-top:3px; }

/* ── RIGHT — access card ───────────────────────────────── */
.pp-card {
  background: #fff;
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 48px 100px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.08);
  animation: slide-in-right .85s cubic-bezier(.22,1,.36,1) .08s both;
}
@keyframes slide-in-right {
  from { opacity: 0; transform: translateX(32px) scale(.97); }
  to   { opacity: 1; transform: translateX(0) scale(1); }
}

/* Card header */
.pp-card-head {
  padding: 28px 26px 22px; text-align: center;
  background: {{ $theme['secondary'] ?? '#0a1f44' }};
  position: relative; overflow: hidden;
}
.pp-card-head::after {
  content:''; position:absolute; inset:0;
  background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
  pointer-events:none;
}
.pp-card-logo {
  width: 64px; height: 64px; border-radius: 18px; margin: 0 auto 14px;
  background: linear-gradient(135deg,#e9a422,#b8860e);
  display: flex; align-items: center; justify-content: center;
  font-size: 1.7rem; color: {{ $theme['secondary'] ?? '#0a1f44' }};
  box-shadow: 0 10px 28px rgba(233,164,34,.4);
  position: relative; z-index: 1;
  animation: logo-pop .9s cubic-bezier(.34,1.56,.64,1) .3s both;
}
@keyframes logo-pop {
  from { transform: scale(0) rotate(-20deg); opacity:0; }
  to   { transform: scale(1) rotate(0);      opacity:1; }
}
.pp-card-label { font-size:.7rem; color:rgba(255,255,255,.4); margin-bottom:3px; position:relative;z-index:1; }
.pp-card-name  { font-size:1.08rem; font-weight:800; color:#fff; margin-bottom:2px; position:relative;z-index:1; }
.pp-card-sub   { font-size:.75rem; color:rgba(255,255,255,.38); position:relative;z-index:1; }

/* Card body */
.pp-card-body { padding: 22px 20px 20px; }

/* Portal buttons */
.pp-portals { display: flex; flex-direction: column; gap: 9px; }

.pp-btn {
  display: flex; align-items: center; gap: 13px; padding: 13px 14px;
  border-radius: 14px; border: none; width: 100%; cursor: pointer;
  font-family: inherit; color: #fff; text-decoration: none;
  transition: transform .18s, filter .18s, box-shadow .18s;
}
.pp-btn:hover { transform: translateY(-2px); filter: brightness(1.1); color: #fff; }
.pp-btn:active { transform: translateY(0); filter: brightness(.95); }

.pp-btn-admin {
  background: linear-gradient(135deg, {{ $theme['secondary'] ?? '#0a1f44' }}, {{ $theme['navBg'] ?? '#0d2857' }});
  box-shadow: 0 4px 16px rgba(10,31,68,.35);
}
.pp-btn-teacher {
  background: linear-gradient(135deg, #5b21b6, #7c3aed);
  box-shadow: 0 4px 16px rgba(91,33,182,.3);
}
.pp-btn-student {
  background: linear-gradient(135deg, #0369a1, #0891b2);
  box-shadow: 0 4px 16px rgba(3,105,161,.3);
}

.pp-btn-icon {
  width: 38px; height: 38px; border-radius: 11px; flex-shrink: 0;
  background: rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center; font-size: .92rem;
}
.pp-btn-title { display:block; font-weight:700; font-size:.86rem; }
.pp-btn-desc  { display:block; font-size:.7rem; opacity:.6; margin-top:1px; }
.pp-btn-arr   { margin-left:auto; font-size:.62rem; opacity:.4; flex-shrink:0; transition:margin .18s,opacity .18s; }
.pp-btn:hover .pp-btn-arr { margin-right:-3px; opacity:.85; }

/* Admin dropdown */
.pp-drop-wrap { position:relative; }
.pp-drop-chev {
  margin-left: auto; font-size: .68rem; opacity: .5; flex-shrink: 0;
  transition: transform .22s;
}
.pp-drop-wrap.open .pp-drop-chev { transform: rotate(180deg); }

.pp-dropdown {
  display: none; margin-top: 6px;
  background: #fff; border-radius: 16px; overflow: hidden;
  border: 1px solid #e8edf4;
  box-shadow: 0 20px 50px rgba(0,0,0,.12);
}
.pp-drop-wrap.open .pp-dropdown {
  display: block;
  animation: drop-open .2s ease both;
}
@keyframes drop-open {
  from { opacity:0; transform:translateY(-8px); }
  to   { opacity:1; transform:translateY(0); }
}

.pp-role {
  display: flex; align-items: center; gap: 12px; padding: 11px 14px;
  text-decoration: none; background: #fff;
  border-bottom: 1px solid #f0f4f8;
  transition: background .15s, transform .15s;
}
.pp-role:last-child { border-bottom: none; }
.pp-role:hover { background: #f7fafd; transform: translateX(3px); }
.pp-role-dot {
  width: 34px; height: 34px; border-radius: 10px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: .75rem; color: #fff;
}
.pp-role-name { display:block; font-size:.82rem; font-weight:700; color:#1e293b; }
.pp-role-sub  { display:block; font-size:.68rem; color:#94a3b8; margin-top:1px; }
.pp-role-arr  { margin-left:auto; font-size:.58rem; color:#cbd5e1; }

/* Divider */
.pp-divider {
  display:flex; align-items:center; gap:8px; margin:12px 0;
}
.pp-divider::before,.pp-divider::after {
  content:''; flex:1; height:1px; background:#e8edf4;
}
.pp-divider span {
  font-size:.6rem; color:#94a3b8; font-weight:700;
  letter-spacing:.12em; text-transform:uppercase;
}

/* Info note */
.pp-note {
  display: flex; gap: 9px; align-items: flex-start;
  background: #f8fafc; border-radius: 12px;
  padding: 11px 13px; font-size: .75rem;
  color: #64748b; line-height: 1.6; margin-top: 12px;
}
.pp-note i { color: #e9a422; flex-shrink:0; margin-top:2px; }

/* ── Responsive ────────────────────────────────────────── */
@media (max-width: 1100px) {
  .pp-inner { grid-template-columns: 1fr 440px; gap: 48px; }
}
@media (max-width: 900px) {
  .pp-inner {
    grid-template-columns: 1fr;
    gap: 36px;
    padding: 40px 20px 48px;
    max-width: 500px;
  }
  .pp-left { order: 2; }
  .pp-card { order: 1; }
  .pp-features, .pp-stats { display: none; }
  .pp-desc { max-width: 100%; }
}
@media (max-width: 540px) {
  .pp-wrap { padding-top: 68px; }
  .pp-inner { padding: 28px 14px 40px; gap: 24px; }
  .pp-title { font-size: 1.75rem; }
  .pp-desc  { font-size: .88rem; margin-bottom: 0; }
  .pp-card  { border-radius: 20px; }
  .pp-card-head { padding: 20px 18px 16px; }
  .pp-card-body { padding: 16px 14px; }
  .pp-btn   { padding: 11px 12px; gap: 11px; }
  .pp-btn-icon { width: 34px; height: 34px; font-size: .82rem; }
  .pp-btn-title { font-size: .82rem; }
  .pp-btn-desc  { font-size: .67rem; }
}
</style>
@endsection

@section('content')
@php
  $tenantSlug = session('tenant_slug');
  $adminUrl   = $tenantSlug ? route('school.admin.login', $tenantSlug) : '#';
  $teachUrl   = $tenantSlug ? route('school.lecturer.login', $tenantSlug) : route('teachers-portal.login');
  $stuUrl     = route('student-portal.login') . ($tenantSlug ? '?tenant=' . $tenantSlug : '');

  $roles = [
    ['Super Admin',  'Full system access',   'fa-shield-halved',  '#0a1f44', null],
    ['Headmaster',   'Academic leadership',  'fa-user-tie',        '#166534', 'headmaster'],
    ['Headteacher',  'School management',    'fa-chalkboard',      '#0f766e', 'headteacher'],
    ['Deputy Head',  'Deputy management',    'fa-user-check',      '#0369a1', 'deputy'],
    ['Accountant',   'Fees &amp; finance',   'fa-calculator',      '#92400e', 'accountant'],
    ['Secretary',    'Administration',       'fa-clipboard-user',  '#6b21a8', 'secretary'],
  ];
@endphp

{{-- Fixed background (outside main flex) --}}
<div class="pp-bg">
  <div class="pp-bg-grad"></div>
  <div class="pp-grid"></div>
  <div class="pp-orb pp-orb-1"></div>
  <div class="pp-orb pp-orb-2"></div>
  {{-- Subtle floating dots (server-side seeded so they don't shift on every render) --}}
  @for($i = 0; $i < 18; $i++)
  @php
    $colors = ['rgba(233,164,34,.6)','rgba(255,255,255,.4)','rgba(8,145,178,.55)'];
    $sizes  = [4, 5, 6, 7, 8];
    $left   = (($i * 53) % 97) + 1;
    $bot    = (($i * 37) % 45) + 2;
    $dur    = (($i * 3)  % 10) + 9;
    $delay  = (($i * 7)  % 18);
    $color  = $colors[$i % 3];
    $size   = $sizes[$i % 5];
  @endphp
  <div class="pp-dot" style="
    width:{{ $size }}px; height:{{ $size }}px;
    background:{{ $color }};
    left:{{ $left }}%; bottom:{{ $bot }}%;
    animation-duration:{{ $dur }}s;
    animation-delay:-{{ $delay }}s;
  "></div>
  @endfor
</div>

<div class="pp-wrap">
  <div class="pp-inner">

    {{-- ══ LEFT: Welcome copy ══════════════════════════════════════ --}}
    <div class="pp-left">
      <div class="pp-eyebrow">
        <span class="pp-pulse"></span>
        Official School Portals
      </div>

      <p class="pp-greeting">
        <i class="fas fa-hand-sparkles" style="margin-right:6px;color:#e9a422;"></i>
        Welcome to <strong style="color:#e9a422;">{{ $site->site_name ?? 'our school' }}</strong>
      </p>

      <h1 class="pp-title">
        {!! $site->portal_intro_title ?? 'Your Gateway to <em>School Life</em>' !!}
      </h1>

      <p class="pp-desc">
        {{ $site->portal_intro_subtitle ?? 'Everything your school community needs — results, attendance, fees and more — all in one secure place. Choose your portal below to get started.' }}
      </p>

      <div class="pp-features">
        @foreach([
          ['fa-users-gear',    'Student &amp; Parent Management', 'Profiles, enrollment and guardian records'],
          ['fa-graduation-cap','Academic Records &amp; Results',  'Report cards, grades and transcripts'],
          ['fa-chart-line',    'Real-Time Analytics',             'Live dashboards and performance insights'],
          ['fa-shield-halved', 'Role-Based Secure Access',        'Every user sees only what they need'],
        ] as [$ic, $t, $s])
        <div class="pp-feat">
          <div class="pp-feat-icon"><i class="fas {{ $ic }}"></i></div>
          <div>
            <span class="pp-feat-title">{!! $t !!}</span>
            <span class="pp-feat-sub">{{ $s }}</span>
          </div>
        </div>
        @endforeach
      </div>

      <div class="pp-stats">
        <div class="pp-stat"><strong>5+</strong><span>Portals</span></div>
        <div class="pp-stat"><strong>SSL</strong><span>Secured</span></div>
        <div class="pp-stat"><strong>24/7</strong><span>Access</span></div>
      </div>
    </div>

    {{-- ══ RIGHT: Access card ═══════════════════════════════════════ --}}
    <div class="pp-card">

      {{-- Header --}}
      <div class="pp-card-head">
        @if(!empty($siteLogoUrl))
        <div class="pp-card-logo" style="background:transparent;padding:0;">
          <img src="{{ $siteLogoUrl }}" alt="{{ $site->site_name ?? 'School' }}"
               style="width:64px;height:64px;object-fit:contain;border-radius:18px;">
        </div>
        @else
        <div class="pp-card-logo"><i class="fas fa-school"></i></div>
        @endif
        <p class="pp-card-label">Welcome back</p>
        <h2 class="pp-card-name">{{ $site->site_name ?? 'School' }}</h2>
        <p class="pp-card-sub">Select your role and sign in securely</p>
      </div>

      {{-- Body --}}
      <div class="pp-card-body">
        <div class="pp-portals">

          {{-- Admin & Staff dropdown --}}
          <div class="pp-drop-wrap" id="ppAdminWrap">
            <button type="button" class="pp-btn pp-btn-admin" onclick="ppToggleDrop()">
              <div class="pp-btn-icon"><i class="fas fa-crown"></i></div>
              <div>
                <span class="pp-btn-title">Admin &amp; Staff Portal</span>
                <span class="pp-btn-desc">Administrators, heads, accountants &amp; secretaries</span>
              </div>
              <i class="fas fa-chevron-down pp-drop-chev"></i>
            </button>
            <div class="pp-dropdown" id="ppAdminDrop">
              @foreach($roles as [$label, $sub, $icon, $color, $role])
              @php
                if ($role) {
                  $url = $tenantSlug
                    ? route('school.staff-portal', [$tenantSlug, $role])
                    : '#';
                  try {
                    $url = route('staff-portal.login.role', $role)
                         . ($tenantSlug ? '?tenant=' . $tenantSlug : '');
                  } catch (\Throwable $e) { $url = $adminUrl; }
                } else {
                  $url = $adminUrl;
                }
              @endphp
              <a href="{{ $url }}" class="pp-role">
                <div class="pp-role-dot" style="background:{{ $color }};">
                  <i class="fas {{ $icon }}"></i>
                </div>
                <div>
                  <span class="pp-role-name">{{ $label }}</span>
                  <span class="pp-role-sub">{!! $sub !!}</span>
                </div>
                <i class="fas fa-arrow-right pp-role-arr"></i>
              </a>
              @endforeach
            </div>
          </div>

          {{-- Teacher --}}
          <a href="{{ $teachUrl }}" class="pp-btn pp-btn-teacher">
            <div class="pp-btn-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div>
              <span class="pp-btn-title">{{ $site->portal_teacher_title ?? 'Teacher Portal' }}</span>
              <span class="pp-btn-desc">{{ $site->portal_teacher_desc ?? 'Attendance, scores and class work' }}</span>
            </div>
            <i class="fas fa-arrow-right pp-btn-arr"></i>
          </a>

          {{-- Student --}}
          <a href="{{ $stuUrl }}" class="pp-btn pp-btn-student">
            <div class="pp-btn-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
              <span class="pp-btn-title">{{ $site->portal_student_title ?? 'Student Portal' }}</span>
              <span class="pp-btn-desc">{{ $site->portal_student_desc ?? 'Results, attendance and fees' }}</span>
            </div>
            <i class="fas fa-arrow-right pp-btn-arr"></i>
          </a>

        </div>

        <div class="pp-divider"><span>Info</span></div>

        <div class="pp-note">
          <i class="fas fa-info-circle"></i>
          <span>{{ $site->portal_help_text ?? 'Not sure which portal? Contact your school administrator for your login details.' }}</span>
        </div>

        @if(isset($site) && $site->contact_phone)
        <div class="pp-note" style="background:rgba(233,164,34,.06);border:1px solid rgba(233,164,34,.18);">
          <i class="fas fa-phone" style="color:#b8860e;"></i>
          <span>Help line: <strong><a href="tel:{{ $site->contact_phone }}" style="color:#0a1f44;">{{ $site->contact_phone }}</a></strong></span>
        </div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection

@section('extra_js')
<script>
function ppToggleDrop() {
  var wrap = document.getElementById('ppAdminWrap');
  var drop = document.getElementById('ppAdminDrop');
  var isOpen = wrap.classList.contains('open');
  wrap.classList.toggle('open', !isOpen);
  drop.style.display = isOpen ? 'none' : 'block';
}
document.addEventListener('click', function(e) {
  var wrap = document.getElementById('ppAdminWrap');
  if (wrap && !wrap.contains(e.target)) {
    wrap.classList.remove('open');
    var drop = document.getElementById('ppAdminDrop');
    if (drop) drop.style.display = 'none';
  }
});
</script>
@endsection
