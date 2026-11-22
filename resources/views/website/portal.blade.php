@extends('website.layout')
@section('page_title','School Portals')
@section('meta_description','Access the '.($site->site_name ?? 'School').' admin, teacher and student portals.')

@section('extra_css')
.portal-hero{min-height:100vh;background:linear-gradient(150deg,var(--nav-bg) 0%,var(--navy) 50%,var(--navy-mid) 100%);display:flex;align-items:center;position:relative;overflow:hidden;padding-top:72px;}
.portal-noise{position:absolute;inset:0;opacity:.025;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");}
.portal-glow-a{position:absolute;width:600px;height:600px;background:radial-gradient(circle,rgba(233,164,34,.09),transparent 70%);top:-120px;right:-100px;pointer-events:none;}
.portal-glow-b{position:absolute;width:500px;height:500px;background:radial-gradient(circle,rgba(30,77,183,.1),transparent 70%);bottom:-100px;left:-80px;pointer-events:none;}
.portal-inner{position:relative;z-index:2;max-width:1180px;margin:0 auto;padding:80px 28px;display:grid;grid-template-columns:1fr 460px;gap:80px;align-items:center;}
/* Left */
.portal-brand{color:var(--white);}
.portal-eyebrow{display:inline-flex;align-items:center;gap:7px;background:rgba(233,164,34,.12);border:1px solid rgba(233,164,34,.22);color:var(--gold);padding:5px 15px;border-radius:100px;font-size:.68rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;margin-bottom:22px;}
.portal-brand h1{font-size:clamp(2.2rem,4.5vw,3.4rem);font-weight:800;color:var(--white);line-height:1.1;margin-bottom:18px;letter-spacing:-.02em;}
.portal-brand h1 em{font-style:normal;color:var(--gold);}
.portal-brand p{font-size:1.02rem;color:rgba(255,255,255,.62);line-height:1.82;max-width:420px;margin-bottom:36px;}
.portal-feats{display:flex;flex-direction:column;gap:11px;}
.pf{display:flex;align-items:center;gap:13px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:13px 16px;transition:var(--trans);}
.pf:hover{background:rgba(255,255,255,.07);}
.pf-icon{width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,var(--gold),var(--gold-dark));display:flex;align-items:center;justify-content:center;color:var(--navy);flex-shrink:0;font-size:.9rem;}
.pf strong{display:block;color:var(--white);font-size:.88rem;font-weight:700;}
.pf span{font-size:.77rem;color:rgba(255,255,255,.42);}
.portal-stats{display:flex;gap:28px;margin-top:40px;padding-top:28px;border-top:1px solid rgba(255,255,255,.07);}
.ps-item strong{display:block;font-size:1.6rem;font-weight:800;color:var(--gold);line-height:1;}
.ps-item span{font-size:.7rem;color:rgba(255,255,255,.38);text-transform:uppercase;letter-spacing:.08em;margin-top:3px;display:block;}
/* Card */
.portal-card{background:rgba(255,255,255,.97);border-radius:24px;overflow:hidden;box-shadow:0 40px 80px rgba(0,0,0,.4);}
.portal-card-head{background:linear-gradient(150deg,var(--navy),var(--navy-mid));padding:30px 32px 26px;text-align:center;}
.portal-shield{width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,var(--gold),var(--gold-dark));margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--navy);box-shadow:0 8px 24px rgba(0,0,0,.25);}
.portal-card-head h2{font-size:1.25rem;font-weight:800;color:var(--white);margin-bottom:4px;}
.portal-card-head p{font-size:.82rem;color:rgba(255,255,255,.5);}
.portal-card-body{padding:28px 30px;}
.access-list{display:flex;flex-direction:column;gap:11px;}
.access-btn{display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:14px;text-decoration:none;transition:var(--trans);position:relative;overflow:hidden;}
.access-btn::after{content:'\f054';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:18px;top:50%;transform:translateY(-50%);font-size:.72rem;opacity:.45;transition:var(--trans);}
.access-btn:hover{transform:translateY(-2px);}
.access-btn:hover::after{right:14px;opacity:.85;}
.ab-admin  {background:linear-gradient(135deg,var(--navy),var(--navy-mid));color:var(--white);box-shadow:0 4px 18px rgba(0,0,0,.28);}
.ab-teacher{background:linear-gradient(135deg,#6d28d9,#4c1d95);color:var(--white);box-shadow:0 4px 18px rgba(109,40,217,.28);}
.ab-student{background:linear-gradient(135deg,#0369a1,#0c4a6e);color:var(--white);box-shadow:0 4px 18px rgba(3,105,161,.28);}
.ab-icon{width:40px;height:40px;border-radius:11px;background:rgba(255,255,255,.14);display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0;}
.ab-strong{display:block;font-weight:700;font-size:.9rem;margin-bottom:2px;}
.ab-sub{font-size:.74rem;opacity:.7;}
.portal-divider{display:flex;align-items:center;gap:10px;margin:16px 0;}
.portal-divider::before,.portal-divider::after{content:'';flex:1;height:1px;background:var(--gray-200);}
.portal-divider span{font-size:.68rem;color:var(--gray-400);font-weight:700;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap;}
.portal-note{background:var(--off-white);border-radius:11px;padding:13px 15px;font-size:.8rem;color:var(--gray-500);line-height:1.62;display:flex;gap:9px;align-items:flex-start;}
.portal-note i{color:var(--gold-dark);flex-shrink:0;margin-top:2px;}
.portal-note-gold{background:rgba(233,164,34,.07);border:1px solid rgba(233,164,34,.18);}
.portal-note-gold i{color:var(--gold-dark);}
@media(max-width:1024px){.portal-inner{grid-template-columns:1fr;gap:44px;max-width:500px;}.portal-brand{text-align:center;}.portal-brand p{margin:0 auto 32px;}.portal-feats{max-width:400px;margin:0 auto;}.portal-stats{justify-content:center;}}
@media(max-width:600px){
  .portal-inner{padding:60px 18px;max-width:100%;}
  .portal-brand h1{font-size:1.8rem;}
  .portal-feats{max-width:100%;}
  .portal-stats{gap:20px;}
  .portal-card{border-radius:18px;}
}
@media(max-width:500px){.portal-card-body{padding:20px 16px;}.portal-card-head{padding:22px 18px;}
  .access-btn{padding:13px 14px;gap:10px;}
  .ab-icon{width:34px;height:34px;font-size:.95rem;}
}
@endsection

@section('content')
<section class="portal-hero">
  <div class="portal-noise"></div>
  <div class="portal-glow-a"></div>
  <div class="portal-glow-b"></div>

  <div class="portal-inner">
    <div class="portal-brand reveal">
      <div class="portal-eyebrow"><i class="fas fa-shield-halved"></i> Official School Portals</div>
      <h1>{!! $site->portal_intro_title ?? 'Access Your Portals' !!}</h1>
      <p>{{ $site->portal_intro_subtitle ?? 'Choose your portal below to log in' }}</p>
      <div class="portal-feats">
        <div class="pf"><div class="pf-icon"><i class="fas fa-users-gear"></i></div><div><strong>Student &amp; Parent Management</strong><span>Track enrollment, profiles and guardian records</span></div></div>
        <div class="pf"><div class="pf-icon"><i class="fas fa-graduation-cap"></i></div><div><strong>Academic Records &amp; Results</strong><span>Grades, report cards, rankings and transcripts</span></div></div>
        <div class="pf"><div class="pf-icon"><i class="fas fa-chart-bar"></i></div><div><strong>Real-Time Analytics</strong><span>Dashboards and performance insights</span></div></div>
        <div class="pf"><div class="pf-icon"><i class="fas fa-lock"></i></div><div><strong>Role-Based Secure Access</strong><span>Permission-driven access for every role</span></div></div>
      </div>
      <div class="portal-stats">
        <div class="ps-item"><strong>3</strong><span>Portals</span></div>
        <div class="ps-item"><strong>SSL</strong><span>Secured</span></div>
        <div class="ps-item"><strong>24/7</strong><span>Accessible</span></div>
      </div>
    </div>

    <div class="portal-card reveal reveal-delay-2">
      <div class="portal-card-head">
        <div class="portal-shield"><i class="fas fa-school"></i></div>
        <h2>{{ $site->site_name ?? 'School' }} Portals</h2>
        <p>Select your role to sign in</p>
      </div>
      <div class="portal-card-body">
        <div class="access-list">
          @php $tenantSlug = session('tenant_slug'); @endphp

          {{-- Admin / Staff Portal — expandable role picker --}}
          <div>
            <button type="button" onclick="toggleAdminPicker()" id="adminPickerBtn"
                class="access-btn ab-admin w-full text-left" style="border:none;cursor:pointer;width:100%;justify-content:space-between;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="ab-icon"><i class="fas fa-crown"></i></div>
                    <div><span class="ab-strong">Admin / Staff Portal</span><span class="ab-sub">School administrators &amp; staff</span></div>
                </div>
                <i class="fas fa-chevron-down" id="adminPickerChev" style="font-size:.75rem;opacity:.6;transition:transform .2s;"></i>
            </button>
            <div id="adminPickerMenu" style="display:none;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;margin-top:6px;">
                @php
                $adminRoles = [
                    ['label'=>'Super Admin / School Owner', 'sub'=>'Full system access',  'icon'=>'fa-shield-halved',  'color'=>'#0a1f44'],
                    ['label'=>'Branch Admin',               'sub'=>'Branch management',   'icon'=>'fa-building',       'color'=>'#1e40af'],
                    ['label'=>'Headmaster / Head',          'sub'=>'Academic leadership', 'icon'=>'fa-user-tie',       'color'=>'#166534'],
                    ['label'=>'Headteacher',                'sub'=>'School management',   'icon'=>'fa-user-check',     'color'=>'#0f766e'],
                    ['label'=>'Accountant',                 'sub'=>'Fees &amp; finance',  'icon'=>'fa-calculator',     'color'=>'#92400e'],
                    ['label'=>'Secretary',                  'sub'=>'Administration',      'icon'=>'fa-clipboard-user', 'color'=>'#6b21a8'],
                ];
                $adminLoginUrl = $tenantSlug ? route('school.admin.login', $tenantSlug) : route('admin.login');
                @endphp
                @foreach($adminRoles as $role)
                <a href="{{ $adminLoginUrl }}"
                   style="display:flex;align-items:center;gap:11px;padding:11px 15px;text-decoration:none;background:#fff;{{ !$loop->last ? 'border-bottom:1px solid #f1f5f9;' : '' }}transition:background .15s;"
                   onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='#fff'">
                    <div style="width:32px;height:32px;border-radius:9px;background:{{ $role['color'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas {{ $role['icon'] }}" style="font-size:.8rem;color:#fff;"></i>
                    </div>
                    <div>
                        <div style="font-size:.82rem;font-weight:700;color:#1e293b;">{!! $role['label'] !!}</div>
                        <div style="font-size:.7rem;color:#94a3b8;">{!! $role['sub'] !!}</div>
                    </div>
                    <i class="fas fa-arrow-right" style="margin-left:auto;font-size:.65rem;color:#cbd5e1;"></i>
                </a>
                @endforeach
            </div>
          </div>

          <a href="{{ $tenantSlug ? route('school.lecturer.login', $tenantSlug) : route('teachers-portal.login') }}" class="access-btn ab-teacher">
            <div class="ab-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div><span class="ab-strong">{{ $site->portal_teacher_title ?? 'Teacher Portal' }}</span><span class="ab-sub">{{ $site->portal_teacher_desc ?? 'Mark attendance and enter scores' }}</span></div>
          </a>
          <a href="{{ route('student-portal.login') }}" class="access-btn ab-student">
            <div class="ab-icon"><i class="fas fa-user-graduate"></i></div>
            <div><span class="ab-strong">{{ $site->portal_student_title ?? 'Student Portal' }}</span><span class="ab-sub">{{ $site->portal_student_desc ?? 'Check results, attendance and fees' }}</span></div>
          </a>
        </div>

        <div class="portal-divider"><span>Access Information</span></div>

        <div class="portal-note">
          <i class="fas fa-info-circle"></i>
          <span>{{ $site->portal_help_text ?? 'Need help? Contact your school administrator.' }}</span>
        </div>

        @if(isset($site) && $site->contact_phone)
        <div class="portal-note portal-note-gold" style="margin-top:10px;">
          <i class="fas fa-phone"></i>
          <span style="color:var(--gray-700);">Need help? Call us: <strong><a href="tel:{{ $site->contact_phone }}" style="color:var(--navy);">{{ $site->contact_phone }}</a></strong></span>
        </div>
        @endif
      </div>
    </div>
  </div>
@endsection

@section('extra_js')
<script>
function toggleAdminPicker() {
    var menu = document.getElementById('adminPickerMenu');
    var chev = document.getElementById('adminPickerChev');
    var open = menu.style.display === 'block';
    menu.style.display = open ? 'none' : 'block';
    if (chev) chev.style.transform = open ? '' : 'rotate(180deg)';
}
document.addEventListener('click', function(e) {
    var btn = document.getElementById('adminPickerBtn');
    var menu = document.getElementById('adminPickerMenu');
    if (btn && menu && !btn.contains(e.target) && !menu.contains(e.target)) {
        menu.style.display = 'none';
        var chev = document.getElementById('adminPickerChev');
        if (chev) chev.style.transform = '';
    }
});
</script>
@endsection
