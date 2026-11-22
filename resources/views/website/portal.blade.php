@extends('website.layout')
@section('page_title', ($site->site_name ?? 'School') . ' — Portals')
@section('meta_description','Access the '.($site->site_name ?? 'School').' admin, teacher and student portals.')

@section('extra_css')
<style>
/* ── Variables ─────────────────────────────────────────────────── */
:root {
  --portal-navy: #0a1120;
  --portal-navy2: #111827;
  --portal-gold: #f59e0b;
  --portal-gold2: #d97706;
  --portal-blue: #2563eb;
}

/* ── Hero ──────────────────────────────────────────────────────── */
.ph-wrap {
  min-height: 100vh;
  background: radial-gradient(ellipse 120% 80% at 60% -10%, rgba(37,99,235,.18) 0%, transparent 60%),
              radial-gradient(ellipse 80% 60% at -10% 80%, rgba(245,158,11,.12) 0%, transparent 55%),
              linear-gradient(160deg, #0a1120 0%, #0f172a 50%, #0c1a3a 100%);
  display: flex; align-items: center; padding-top: 76px; position: relative; overflow: hidden;
}
.ph-mesh {
  position: absolute; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(255,255,255,.025) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.025) 1px, transparent 1px);
  background-size: 60px 60px;
}
.ph-orb-1 { position:absolute; width:700px; height:700px; border-radius:50%; background:radial-gradient(circle, rgba(245,158,11,.07) 0%, transparent 65%); top:-200px; right:-150px; pointer-events:none; }
.ph-orb-2 { position:absolute; width:500px; height:500px; border-radius:50%; background:radial-gradient(circle, rgba(37,99,235,.1) 0%, transparent 65%); bottom:-150px; left:-100px; pointer-events:none; }

.ph-inner {
  position: relative; z-index: 2;
  max-width: 1200px; margin: 0 auto; padding: 80px 28px;
  display: grid; grid-template-columns: 1fr 480px; gap: 80px; align-items: center;
}

/* ── Left branding ─────────────────────────────────────────────── */
.pb-eyebrow {
  display: inline-flex; align-items: center; gap: 8px;
  background: rgba(245,158,11,.1); border: 1px solid rgba(245,158,11,.25);
  color: var(--portal-gold); padding: 6px 16px; border-radius: 100px;
  font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase;
  margin-bottom: 24px;
}
.pb-eyebrow span { width:6px; height:6px; border-radius:50%; background:var(--portal-gold); animation: pulse-dot 1.8s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.8)} }

.pb-title {
  font-size: clamp(2.4rem, 5vw, 3.8rem); font-weight: 900; color: #fff;
  line-height: 1.08; letter-spacing: -.03em; margin-bottom: 20px;
}
.pb-title em { font-style:normal; color: var(--portal-gold); }

.pb-sub { font-size: 1.05rem; color: rgba(255,255,255,.55); line-height: 1.8; max-width: 440px; margin-bottom: 40px; }

/* Feature pills */
.pb-feats { display:flex; flex-direction:column; gap:10px; margin-bottom:36px; }
.pb-feat {
  display:flex; align-items:center; gap:12px;
  background: rgba(255,255,255,.04); border: 1px solid rgba(255,255,255,.07);
  border-radius: 12px; padding: 12px 16px;
  transition: background .2s, border-color .2s;
}
.pb-feat:hover { background: rgba(255,255,255,.07); border-color: rgba(245,158,11,.2); }
.pb-feat-icon {
  width: 34px; height: 34px; border-radius: 9px;
  background: linear-gradient(135deg, var(--portal-gold), var(--portal-gold2));
  display: flex; align-items: center; justify-content: center;
  color: #0a1120; font-size: .85rem; flex-shrink: 0;
}
.pb-feat strong { display:block; color:#fff; font-size:.86rem; font-weight:700; }
.pb-feat span { font-size:.74rem; color:rgba(255,255,255,.38); }

/* Stats row */
.pb-stats { display:flex; gap:32px; padding-top:28px; border-top:1px solid rgba(255,255,255,.07); }
.pb-stat strong { display:block; font-size:1.7rem; font-weight:900; color:var(--portal-gold); line-height:1; }
.pb-stat span { font-size:.65rem; text-transform:uppercase; letter-spacing:.1em; color:rgba(255,255,255,.3); margin-top:4px; display:block; }

/* ── Portal card ───────────────────────────────────────────────── */
.pc-card {
  background: rgba(255,255,255,.97);
  border-radius: 28px;
  overflow: hidden;
  box-shadow: 0 40px 100px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.08);
}
.pc-head {
  background: linear-gradient(150deg, #0a1f44 0%, #1a3a7a 100%);
  padding: 28px 30px 24px; text-align: center;
}
.pc-shield {
  width: 64px; height: 64px; border-radius: 20px;
  background: linear-gradient(135deg, var(--portal-gold), var(--portal-gold2));
  margin: 0 auto 14px; display: flex; align-items: center; justify-content: center;
  font-size: 1.7rem; color: #0a1f44;
  box-shadow: 0 8px 28px rgba(245,158,11,.35);
}
.pc-head h2 { font-size: 1.2rem; font-weight: 800; color: #fff; margin-bottom: 4px; }
.pc-head p  { font-size: .8rem; color: rgba(255,255,255,.45); }

.pc-body { padding: 24px 26px; }

/* Access buttons */
.pa-list { display:flex; flex-direction:column; gap:9px; }

.pa-btn {
  display: flex; align-items: center; gap: 13px; padding: 14px 16px;
  border-radius: 14px; text-decoration: none; cursor: pointer;
  transition: transform .18s, box-shadow .18s, filter .18s;
  position: relative; overflow: hidden; border: none;
  width: 100%; text-align: left;
}
.pa-btn:hover { transform: translateY(-2px); }
.pa-btn::after {
  content: '\f054'; font-family:'Font Awesome 6 Free'; font-weight:900;
  position: absolute; right: 16px; top: 50%; transform: translateY(-50%);
  font-size: .68rem; opacity: .4; transition: right .18s, opacity .18s;
}
.pa-btn:hover::after { right: 12px; opacity: .8; }

.pa-admin   { background: linear-gradient(135deg, #0a1f44, #1a3a7a); color:#fff; box-shadow: 0 4px 20px rgba(10,31,68,.4); }
.pa-teacher { background: linear-gradient(135deg, #5b21b6, #7c3aed); color:#fff; box-shadow: 0 4px 20px rgba(91,33,182,.35); }
.pa-student { background: linear-gradient(135deg, #0369a1, #0284c7); color:#fff; box-shadow: 0 4px 20px rgba(3,105,161,.35); }

.pa-icon {
  width: 38px; height: 38px; border-radius: 11px;
  background: rgba(255,255,255,.15);
  display: flex; align-items: center; justify-content: center;
  font-size: 1rem; flex-shrink: 0;
}
.pa-strong { display:block; font-weight:700; font-size:.88rem; margin-bottom:2px; }
.pa-sub    { font-size:.72rem; opacity:.65; }

/* ── Admin role picker dropdown ────────────────────────────────── */
.pa-admin-wrap { position: relative; }
.pa-picker {
  display: none; position: absolute; left:0; right:0; top: calc(100% + 6px); z-index: 50;
  background: #fff; border-radius: 16px; overflow: hidden;
  box-shadow: 0 20px 60px rgba(0,0,0,.2), 0 0 0 1px #e2e8f0;
}
.pa-picker-role {
  display: flex; align-items: center; gap: 11px; padding: 12px 16px;
  text-decoration: none; background: #fff; transition: background .15s;
  border-bottom: 1px solid #f1f5f9;
}
.pa-picker-role:last-child { border-bottom: none; }
.pa-picker-role:hover { background: #f8fafc; }
.pa-picker-dot {
  width: 30px; height: 30px; border-radius: 8px; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center; font-size:.75rem; color:#fff;
}
.pa-picker-role-name { font-size:.82rem; font-weight:700; color:#1e293b; display:block; }
.pa-picker-role-sub  { font-size:.7rem; color:#94a3b8; display:block; }
.pa-picker-arr { margin-left:auto; font-size:.62rem; color:#cbd5e1; }

.pa-chev { margin-left:auto; font-size:.72rem; opacity:.5; transition:transform .2s; }
.admin-open .pa-chev { transform: rotate(180deg); }

/* Divider */
.pa-divider {
  display:flex; align-items:center; gap:10px; margin:14px 0;
}
.pa-divider::before,.pa-divider::after { content:''; flex:1; height:1px; background:#e2e8f0; }
.pa-divider span { font-size:.64rem; color:#94a3b8; font-weight:700; letter-spacing:.1em; text-transform:uppercase; white-space:nowrap; }

/* Info note */
.pa-note {
  background: #f8fafc; border-radius: 11px; padding: 12px 14px;
  font-size: .78rem; color: #64748b; line-height: 1.6;
  display: flex; gap: 9px; align-items: flex-start;
}
.pa-note i { color: var(--portal-gold2); flex-shrink: 0; margin-top: 2px; }
.pa-note-gold { background: rgba(245,158,11,.06); border: 1px solid rgba(245,158,11,.18); }

/* ── Animated particles ────────────────────────────────────────── */
.ph-particle { position: absolute; border-radius: 50%; pointer-events: none; z-index: 0; opacity: .15; animation: float-p linear infinite; }
@keyframes float-p { 0%{transform:translateY(0) rotate(0deg)} 100%{transform:translateY(-100vh) rotate(720deg)} }

/* ── Responsive ────────────────────────────────────────────────── */
@media(max-width:1024px) {
  .ph-inner { grid-template-columns:1fr; gap:50px; max-width:520px; }
  .pb-title { font-size:2.4rem; }
  .pb-sub,.pb-feats { max-width:100%; }
  .pb-stats { justify-content:flex-start; }
}
@media(max-width:600px) {
  .ph-inner { padding:60px 18px; }
  .pb-title { font-size:2rem; }
  .pc-body { padding:18px 16px; }
  .pc-head { padding:22px 18px 18px; }
  .pa-btn { padding:12px 14px; gap:10px; }
  .pa-icon { width:32px; height:32px; font-size:.9rem; }
  .pb-stats { gap:20px; }
}
</style>
@endsection

@section('content')
@php $tenantSlug = session('tenant_slug'); @endphp

<section class="ph-wrap">
  {{-- Background elements --}}
  <div class="ph-mesh"></div>
  <div class="ph-orb-1"></div>
  <div class="ph-orb-2"></div>

  {{-- Animated particles --}}
  @for($i = 0; $i < 12; $i++)
  <div class="ph-particle" style="
    width:{{ rand(4,10) }}px; height:{{ rand(4,10) }}px;
    background:{{ ['#f59e0b','#2563eb','#ffffff','#7c3aed'][$i % 4] }};
    left:{{ rand(5,95) }}%; bottom:-20px;
    animation-duration:{{ rand(12,22) }}s;
    animation-delay:-{{ rand(0,15) }}s;
  "></div>
  @endfor

  <div class="ph-inner">

    {{-- ── LEFT: Branding ── --}}
    <div class="pb-brand reveal">
      <div class="pb-eyebrow"><span></span> Official School Portals</div>
      <h1 class="pb-title">{!! $site->portal_intro_title ?? 'Access Your <em>School</em> Portals' !!}</h1>
      <p class="pb-sub">{{ $site->portal_intro_subtitle ?? 'Everything you need in one place. Choose your role below and log in securely.' }}</p>

      <div class="pb-feats">
        @foreach([
          ['fa-users-gear',    'Student &amp; Parent Management',    'Track enrollment, profiles and guardian records'],
          ['fa-graduation-cap','Academic Records &amp; Results',      'Grades, report cards, rankings and transcripts'],
          ['fa-chart-line',    'Real-Time Analytics &amp; Reports',   'Dashboards and performance insights'],
          ['fa-shield-halved', 'Role-Based Secure Access',            'Permission-driven access for every role'],
        ] as [$icon,$title,$sub])
        <div class="pb-feat">
          <div class="pb-feat-icon"><i class="fas {{ $icon }}"></i></div>
          <div><strong>{!! $title !!}</strong><span>{{ $sub }}</span></div>
        </div>
        @endforeach
      </div>

      <div class="pb-stats">
        <div class="pb-stat"><strong>4</strong><span>Portals</span></div>
        <div class="pb-stat"><strong>SSL</strong><span>Secured</span></div>
        <div class="pb-stat"><strong>24/7</strong><span>Access</span></div>
        <div class="pb-stat"><strong>Live</strong><span>Updates</span></div>
      </div>
    </div>

    {{-- ── RIGHT: Access card ── --}}
    <div class="pc-card reveal reveal-delay-2">
      <div class="pc-head">
        <div class="pc-shield"><i class="fas fa-school"></i></div>
        <h2>{{ $site->site_name ?? 'School' }}</h2>
        <p>Select your role to sign in securely</p>
      </div>

      <div class="pc-body">
        <div class="pa-list">

          {{-- Admin / Staff — expandable --}}
          <div class="pa-admin-wrap" id="adminWrap">
            <button type="button" class="pa-btn pa-admin" onclick="togglePicker()" id="adminBtn">
              <div class="pa-icon"><i class="fas fa-crown"></i></div>
              <div>
                <span class="pa-strong">Admin &amp; Staff Portal</span>
                <span class="pa-sub">Administrators, heads, accountants, secretaries</span>
              </div>
              <i class="fas fa-chevron-down pa-chev" id="adminChev"></i>
            </button>

            <div class="pa-picker" id="adminPicker">
              @php
              $adminLoginUrl = $tenantSlug ? route('school.admin.login', $tenantSlug) : route('admin.login');
              $adminRoles = [
                ['Super Admin / School Owner', 'Full system access',     'fa-shield-halved',  '#0a1f44', null],
                ['Branch Admin',               'Branch management',      'fa-building',       '#1e40af', null],
                ['Headmaster',                 'Academic leadership',    'fa-user-tie',        '#166534', 'headmaster'],
                ['Headteacher',                'School management',      'fa-chalkboard',      '#0f766e', 'headteacher'],
                ['Accountant',                 'Fees &amp; finance',     'fa-calculator',      '#92400e', 'accountant'],
                ['Secretary',                  'Administration',         'fa-clipboard-user',  '#6b21a8', 'secretary'],
              ];
              @endphp
              @foreach($adminRoles as [$label,$sub,$icon,$color,$staffRole])
              @php
                if ($staffRole) {
                    // Staff portal roles — route to their specific login
                    try {
                        $loginUrl = route('staff-portal.login.role', $staffRole) . ($tenantSlug ? '?tenant=' . $tenantSlug : '');
                    } catch (\Throwable $e) {
                        $loginUrl = $adminLoginUrl;
                    }
                } else {
                    $loginUrl = $adminLoginUrl;
                }
              @endphp
              <a href="{{ $loginUrl }}" class="pa-picker-role">
                <div class="pa-picker-dot" style="background:{{ $color }}">
                  <i class="fas {{ $icon }}"></i>
                </div>
                <div>
                  <span class="pa-picker-role-name">{!! $label !!}</span>
                  <span class="pa-picker-role-sub">{!! $sub !!}</span>
                </div>
                <i class="fas fa-arrow-right pa-picker-arr"></i>
              </a>
              @endforeach
            </div>
          </div>

          {{-- Teacher --}}
          <a href="{{ $tenantSlug ? route('school.lecturer.login', $tenantSlug) : route('teachers-portal.login') }}" class="pa-btn pa-teacher">
            <div class="pa-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div>
              <span class="pa-strong">{{ $site->portal_teacher_title ?? 'Teacher Portal' }}</span>
              <span class="pa-sub">{{ $site->portal_teacher_desc ?? 'Mark attendance and enter exam scores' }}</span>
            </div>
          </a>

          {{-- Student --}}
          <a href="{{ route('student-portal.login') }}" class="pa-btn pa-student">
            <div class="pa-icon"><i class="fas fa-user-graduate"></i></div>
            <div>
              <span class="pa-strong">{{ $site->portal_student_title ?? 'Student Portal' }}</span>
              <span class="pa-sub">{{ $site->portal_student_desc ?? 'Check results, attendance and fees' }}</span>
            </div>
          </a>

        </div>

        <div class="pa-divider"><span>Help &amp; Info</span></div>

        <div class="pa-note">
          <i class="fas fa-info-circle"></i>
          <span>{{ $site->portal_help_text ?? 'Not sure which portal to use? Contact your school administrator for your login details.' }}</span>
        </div>

        @if(isset($site) && $site->contact_phone)
        <div class="pa-note pa-note-gold" style="margin-top:8px;">
          <i class="fas fa-phone"></i>
          <span style="color:#374151;">Help line: <strong><a href="tel:{{ $site->contact_phone }}" style="color:#0a1f44;">{{ $site->contact_phone }}</a></strong></span>
        </div>
        @endif
      </div>
    </div>

  </div>
</section>
@endsection

@section('extra_js')
<script>
function togglePicker() {
    var wrap  = document.getElementById('adminWrap');
    var picker= document.getElementById('adminPicker');
    var open  = picker.style.display === 'block';
    picker.style.display = open ? 'none' : 'block';
    wrap.classList.toggle('admin-open', !open);
}
document.addEventListener('click', function(e) {
    var wrap  = document.getElementById('adminWrap');
    var picker= document.getElementById('adminPicker');
    if (wrap && picker && !wrap.contains(e.target)) {
        picker.style.display = 'none';
        wrap.classList.remove('admin-open');
    }
});
</script>
@endsection
