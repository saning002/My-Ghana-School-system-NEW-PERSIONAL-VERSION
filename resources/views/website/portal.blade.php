@extends('website.layout')
@section('page_title', ($site->site_name ?? 'School') . ' — Portals')
@section('meta_description','Access the '.($site->site_name ?? 'School').' portals.')

@section('extra_css')
<style>
/* Uses --navy, --gold, --gold-dark, --navy-mid, --teal from layout.blade.php */

/* ── Hero wrapper ──────────────────────────────────────────── */
.ph {
  min-height: 100vh;
  background: linear-gradient(150deg, var(--navy) 0%, color-mix(in srgb,var(--navy) 70%,#000) 100%);
  display: flex; align-items: center; padding-top: 76px;
  position: relative; overflow: hidden;
}

/* Animated grid mesh */
.ph-grid {
  position: absolute; inset: 0; z-index: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(255,255,255,.04) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,.04) 1px, transparent 1px);
  background-size: 56px 56px;
  animation: grid-drift 20s linear infinite;
}
@keyframes grid-drift {
  0%   { background-position: 0 0; }
  100% { background-position: 56px 56px; }
}

/* Glowing orbs */
.ph-orb {
  position: absolute; border-radius: 50%; pointer-events: none; z-index: 0;
  animation: orb-pulse 6s ease-in-out infinite;
}
.ph-orb-1 {
  width: 600px; height: 600px;
  background: radial-gradient(circle, color-mix(in srgb,var(--gold) 12%,transparent), transparent 65%);
  top: -180px; right: -120px; animation-delay: 0s;
}
.ph-orb-2 {
  width: 450px; height: 450px;
  background: radial-gradient(circle, color-mix(in srgb,var(--teal,#0891b2) 10%,transparent), transparent 65%);
  bottom: -120px; left: -80px; animation-delay: 3s;
}
@keyframes orb-pulse {
  0%,100% { transform: scale(1); opacity: .8; }
  50%      { transform: scale(1.1); opacity: 1; }
}

/* Floating particles */
.ph-particle {
  position: absolute; border-radius: 50%; pointer-events: none; z-index: 0;
  animation: float-up linear infinite;
}
@keyframes float-up {
  0%   { transform: translateY(0) rotate(0deg); opacity: 0; }
  10%  { opacity: .2; }
  90%  { opacity: .15; }
  100% { transform: translateY(-110vh) rotate(540deg); opacity: 0; }
}

/* ── Layout ────────────────────────────────────────────────── */
.ph-inner {
  position: relative; z-index: 2;
  max-width: 1160px; margin: 0 auto; padding: 80px 28px;
  display: grid; grid-template-columns: 1fr 460px; gap: 72px; align-items: center;
}

/* ── Left panel ────────────────────────────────────────────── */
.pl { animation: slide-in-left .9s cubic-bezier(.22,1,.36,1) both; }
@keyframes slide-in-left {
  from { opacity: 0; transform: translateX(-40px); }
  to   { opacity: 1; transform: translateX(0); }
}

.pl-badge {
  display: inline-flex; align-items: center; gap: 8px;
  background: color-mix(in srgb,var(--gold) 12%,transparent);
  border: 1px solid color-mix(in srgb,var(--gold) 30%,transparent);
  color: var(--gold); padding: 6px 16px; border-radius: 100px;
  font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase;
  margin-bottom: 24px;
}
.pl-badge-dot {
  width: 7px; height: 7px; border-radius: 50%; background: var(--gold);
  animation: blink 1.6s ease-in-out infinite;
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.25} }

.pl-title {
  font-size: clamp(2.2rem,4.5vw,3.6rem); font-weight: 900; color: #fff;
  line-height: 1.08; letter-spacing: -.03em; margin-bottom: 18px;
}
.pl-title em { font-style: normal; color: var(--gold); }

.pl-sub {
  font-size: 1rem; color: rgba(255,255,255,.52); line-height: 1.82;
  max-width: 420px; margin-bottom: 38px;
}

/* Feature rows */
.pl-feats { display: flex; flex-direction: column; gap: 9px; margin-bottom: 36px; }
.pl-feat {
  display: flex; align-items: center; gap: 13px;
  background: rgba(255,255,255,.045); border: 1px solid rgba(255,255,255,.07);
  border-radius: 13px; padding: 12px 16px;
  transition: background .2s, border-color .2s, transform .2s;
  animation: feat-in .7s cubic-bezier(.22,1,.36,1) both;
}
.pl-feat:nth-child(1) { animation-delay: .15s; }
.pl-feat:nth-child(2) { animation-delay: .25s; }
.pl-feat:nth-child(3) { animation-delay: .35s; }
.pl-feat:nth-child(4) { animation-delay: .45s; }
@keyframes feat-in {
  from { opacity: 0; transform: translateX(-20px); }
  to   { opacity: 1; transform: translateX(0); }
}
.pl-feat:hover { background: rgba(255,255,255,.08); border-color: color-mix(in srgb,var(--gold) 22%,transparent); transform: translateX(4px); }
.pl-feat-icon {
  width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  display: flex; align-items: center; justify-content: center;
  color: var(--navy); font-size: .88rem;
}
.pl-feat strong { display: block; color: #fff; font-size: .86rem; font-weight: 700; }
.pl-feat span   { font-size: .73rem; color: rgba(255,255,255,.38); }

/* Stats */
.pl-stats {
  display: flex; gap: 28px; padding-top: 26px;
  border-top: 1px solid rgba(255,255,255,.07);
}
.pl-stat strong { display: block; font-size: 1.65rem; font-weight: 900; color: var(--gold); line-height: 1; }
.pl-stat span   { font-size: .64rem; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.28); margin-top: 3px; display: block; }

/* ── Right card ────────────────────────────────────────────── */
.pc {
  background: rgba(255,255,255,.97); border-radius: 26px; overflow: hidden;
  box-shadow: 0 40px 100px rgba(0,0,0,.45), 0 0 0 1px rgba(255,255,255,.1);
  animation: slide-in-right .9s cubic-bezier(.22,1,.36,1) both; animation-delay: .1s;
}
@keyframes slide-in-right {
  from { opacity: 0; transform: translateX(40px) scale(.97); }
  to   { opacity: 1; transform: translateX(0) scale(1); }
}

.pc-head {
  background: linear-gradient(150deg, var(--navy) 0%, var(--navy-mid) 100%);
  padding: 26px 28px 22px; text-align: center;
}
.pc-shield {
  width: 62px; height: 62px; border-radius: 18px; margin: 0 auto 13px;
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  display: flex; align-items: center; justify-content: center;
  font-size: 1.65rem; color: var(--navy);
  box-shadow: 0 8px 26px color-mix(in srgb,var(--gold) 38%,transparent);
  animation: shield-pop .8s cubic-bezier(.34,1.56,.64,1) .3s both;
}
@keyframes shield-pop {
  from { transform: scale(0) rotate(-15deg); opacity: 0; }
  to   { transform: scale(1) rotate(0deg);  opacity: 1; }
}
.pc-head h2 { font-size: 1.15rem; font-weight: 800; color: #fff; margin-bottom: 3px; }
.pc-head p  { font-size: .78rem; color: rgba(255,255,255,.42); }

.pc-body { padding: 22px 24px; }

/* Portal buttons */
.pa-list { display: flex; flex-direction: column; gap: 8px; }

.pa-btn {
  display: flex; align-items: center; gap: 12px; padding: 13px 15px;
  border-radius: 13px; text-decoration: none; cursor: pointer; border: none;
  width: 100%; text-align: left; color: #fff; font-family: inherit;
  transition: transform .18s, box-shadow .18s, filter .18s;
  position: relative; overflow: hidden;
}
.pa-btn:hover { transform: translateY(-2px) scale(1.01); filter: brightness(1.08); }
.pa-btn-nav   { background: linear-gradient(135deg, var(--navy), var(--navy-mid)); box-shadow: 0 3px 16px color-mix(in srgb,var(--navy) 45%,transparent); }
.pa-btn-teach { background: linear-gradient(135deg, #5b21b6, #7c3aed); box-shadow: 0 3px 16px rgba(91,33,182,.35); }
.pa-btn-stu   { background: linear-gradient(135deg, var(--teal,#0369a1), color-mix(in srgb,var(--teal,#0369a1) 75%,#000)); box-shadow: 0 3px 16px color-mix(in srgb,var(--teal,#0369a1) 38%,transparent); }

.pa-icon {
  width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
  background: rgba(255,255,255,.16);
  display: flex; align-items: center; justify-content: center; font-size: .95rem;
}
.pa-strong { display: block; font-weight: 700; font-size: .86rem; margin-bottom: 1px; }
.pa-sub    { font-size: .7rem; opacity: .6; }
.pa-arr    { margin-left: auto; font-size: .6rem; opacity: .45; flex-shrink: 0; transition: margin-right .18s, opacity .18s; }
.pa-btn:hover .pa-arr { margin-right: -3px; opacity: .85; }

/* ── Admin / Staff dropdown ────────────────────────────────── */
.pa-wrap { position: relative; }
.pa-chev { margin-left: auto; font-size: .68rem; opacity: .5; transition: transform .22s; flex-shrink: 0; }
.pa-wrap.open .pa-chev { transform: rotate(180deg); }

.pa-drop {
  display: none; margin-top: 6px;
  background: #fff; border-radius: 14px; overflow: hidden;
  border: 1px solid #e8edf2;
  box-shadow: 0 16px 48px rgba(0,0,0,.14);
}
.pa-wrap.open .pa-drop { display: block; animation: drop-in .22s ease both; }
@keyframes drop-in {
  from { opacity: 0; transform: translateY(-8px); }
  to   { opacity: 1; transform: translateY(0); }
}

.pa-role {
  display: flex; align-items: center; gap: 11px; padding: 11px 15px;
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
  display: flex; gap: 9px; align-items: flex-start; margin-top: 10px;
}
.pa-note i { color: var(--gold-dark); flex-shrink: 0; margin-top: 2px; }

/* ── Responsive ────────────────────────────────────────────── */
@media(max-width:1024px) {
  .ph-inner { grid-template-columns: 1fr; gap: 44px; max-width: 500px; }
  .pl-title { font-size: 2.4rem; }
  .pl-stats { flex-wrap: wrap; }
}
@media(max-width:600px) {
  .ph-inner { padding: 60px 18px; }
  .pl-title { font-size: 2rem; }
  .pc-body  { padding: 16px 14px; }
  .pc-head  { padding: 20px 16px 16px; }
  .pa-btn   { padding: 12px 13px; gap: 10px; }
  .pa-icon  { width: 32px; height: 32px; font-size: .88rem; }
}
</style>
@endsection

@section('content')
@php $tenantSlug = session('tenant_slug'); @endphp

<section class="ph">
  <div class="ph-grid"></div>
  <div class="ph-orb ph-orb-1"></div>
  <div class="ph-orb ph-orb-2"></div>

  {{-- Floating particles --}}
  @php $pColors = ['var(--gold)','rgba(255,255,255,.6)','var(--teal,#0891b2)','color-mix(in srgb,var(--gold) 70%,white)']; @endphp
  @for($i=0;$i<14;$i++)
  <div class="ph-particle" style="
    width:{{ rand(4,9) }}px; height:{{ rand(4,9) }}px;
    background:{{ $pColors[$i%4] }};
    left:{{ rand(3,97) }}%; bottom:-10px;
    animation-duration:{{ rand(14,24) }}s;
    animation-delay:-{{ rand(0,20) }}s;
  "></div>
  @endfor

  <div class="ph-inner">

    {{-- ── LEFT ─────────────────────────────────────────── --}}
    <div class="pl">
      <div class="pl-badge"><span class="pl-badge-dot"></span> Official School Portals</div>
      <h1 class="pl-title">{!! $site->portal_intro_title ?? 'Access Your <em>School</em> Portals' !!}</h1>
      <p class="pl-sub">{{ $site->portal_intro_subtitle ?? 'Everything you need in one place. Choose your role and sign in securely.' }}</p>

      <div class="pl-feats">
        @foreach([
          ['fa-users-gear',    'Student &amp; Parent Management',   'Profiles, enrollment and guardian records'],
          ['fa-graduation-cap','Academic Records &amp; Results',     'Report cards, grades and transcripts'],
          ['fa-chart-line',    'Real-Time Analytics',               'Live dashboards and performance insights'],
          ['fa-shield-halved', 'Role-Based Secure Access',          'Every user sees only what they need'],
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

    {{-- ── RIGHT CARD ───────────────────────────────────── --}}
    <div class="pc">
      <div class="pc-head">
        <div class="pc-shield"><i class="fas fa-school"></i></div>
        <h2>{{ $site->site_name ?? 'School' }}</h2>
        <p>Select your role to sign in securely</p>
      </div>

      <div class="pc-body">
        <div class="pa-list">

          {{-- ── Admin & Staff — dropdown ── --}}
          @php
            $adminUrl = $tenantSlug ? route('school.admin.login', $tenantSlug) : route('admin.login');
            $adminRoles = [
              ['Super Admin',   'Full system access',     'fa-shield-halved',  '#0a1f44', null],
              ['Branch Admin',  'Branch management',      'fa-building',       '#1e40af', null],
              ['Headmaster',    'Academic leadership',    'fa-user-tie',        '#166534', 'headmaster'],
              ['Headteacher',   'School management',      'fa-chalkboard',      '#0f766e', 'headteacher'],
              ['Accountant',    'Fees & finance',         'fa-calculator',      '#92400e', 'accountant'],
              ['Secretary',     'Administration',         'fa-clipboard-user',  '#6b21a8', 'secretary'],
              ['Deputy Head',   'Deputy management',      'fa-user-check',      '#0369a1', 'deputy'],
            ];
          @endphp
          <div class="pa-wrap" id="adminWrap">
            <button type="button" class="pa-btn pa-btn-nav" id="adminBtn" onclick="toggleDrop()">
              <div class="pa-icon"><i class="fas fa-crown"></i></div>
              <div>
                <span class="pa-strong">Admin &amp; Staff Portal</span>
                <span class="pa-sub">Administrators, heads, accountants &amp; secretaries</span>
              </div>
              <i class="fas fa-chevron-down pa-chev"></i>
            </button>

            <div class="pa-drop" id="adminDrop">
              @foreach($adminRoles as [$label,$sub,$icon,$color,$role])
              @php
                if ($role) {
                  try { $url = route('staff-portal.login.role', $role) . ($tenantSlug ? '?tenant='.$tenantSlug : ''); }
                  catch (\Throwable $e) { $url = $adminUrl; }
                } else {
                  $url = $adminUrl;
                }
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

          {{-- ── Teacher ── --}}
          <a href="{{ $tenantSlug ? route('school.lecturer.login',$tenantSlug) : route('teachers-portal.login') }}" class="pa-btn pa-btn-teach">
            <div class="pa-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div>
              <span class="pa-strong">{{ $site->portal_teacher_title ?? 'Teacher Portal' }}</span>
              <span class="pa-sub">{{ $site->portal_teacher_desc ?? 'Attendance, scores and class work' }}</span>
            </div>
            <i class="fas fa-arrow-right pa-arr"></i>
          </a>

          {{-- ── Student ── --}}
          <a href="{{ route('student-portal.login') }}" class="pa-btn pa-btn-stu">
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
          <span>{{ $site->portal_help_text ?? 'Not sure which portal to use? Contact your school administrator for your login details.' }}</span>
        </div>

        @if(isset($site) && $site->contact_phone)
        <div class="pa-note" style="margin-top:7px;background:color-mix(in srgb,var(--gold) 7%,transparent);border:1px solid color-mix(in srgb,var(--gold) 18%,transparent);">
          <i class="fas fa-phone" style="color:var(--gold-dark)"></i>
          <span style="color:#374151;">Help line: <strong><a href="tel:{{ $site->contact_phone }}" style="color:var(--navy)">{{ $site->contact_phone }}</a></strong></span>
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
    drop.style.display = open ? 'none' : 'block';
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
