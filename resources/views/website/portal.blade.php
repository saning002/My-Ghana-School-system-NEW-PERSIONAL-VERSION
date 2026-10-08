@extends('website.layout')
@section('page_title','School Portals')
@section('meta_description','Access the '.($site->site_name ?? 'School').' admin, teacher and student portals.')

@section('extra_css')
.portal-hero{min-height:100vh;background:linear-gradient(150deg,#040c1f 0%,#0a1f44 50%,#111845 100%);display:flex;align-items:center;position:relative;overflow:hidden;padding-top:72px;}
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
.portal-card-head{background:linear-gradient(150deg,var(--navy),#162d60);padding:30px 32px 26px;text-align:center;}
.portal-shield{width:60px;height:60px;border-radius:18px;background:linear-gradient(135deg,var(--gold),var(--gold-dark));margin:0 auto 14px;display:flex;align-items:center;justify-content:center;font-size:1.6rem;color:var(--navy);box-shadow:0 8px 24px rgba(233,164,34,.4);}
.portal-card-head h2{font-size:1.25rem;font-weight:800;color:var(--white);margin-bottom:4px;}
.portal-card-head p{font-size:.82rem;color:rgba(255,255,255,.5);}
.portal-card-body{padding:28px 30px;}
.access-list{display:flex;flex-direction:column;gap:11px;}
.access-btn{display:flex;align-items:center;gap:14px;padding:16px 18px;border-radius:14px;text-decoration:none;transition:var(--trans);position:relative;overflow:hidden;}
.access-btn::after{content:'\f054';font-family:'Font Awesome 6 Free';font-weight:900;position:absolute;right:18px;top:50%;transform:translateY(-50%);font-size:.72rem;opacity:.45;transition:var(--trans);}
.access-btn:hover{transform:translateY(-2px);}
.access-btn:hover::after{right:14px;opacity:.85;}
.ab-admin  {background:linear-gradient(135deg,var(--navy),#162d60);color:var(--white);box-shadow:0 4px 18px rgba(10,31,68,.28);}
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
      <h1>Welcome to the<br><em>{{ $site->site_name ?? 'School' }}</em><br>Portal Hub</h1>
      <p>Your centralised gateway to school management. Select your portal below to securely access records, results, attendance, fees, and more.</p>
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
          <a href="{{ route('admin.login') }}" class="access-btn ab-admin">
            <div class="ab-icon"><i class="fas fa-crown"></i></div>
            <div><span class="ab-strong">Admin Portal</span><span class="ab-sub">School administrators &amp; branch managers</span></div>
          </a>
          <a href="{{ route('teachers-portal.login') }}" class="access-btn ab-teacher">
            <div class="ab-icon"><i class="fas fa-chalkboard-user"></i></div>
            <div><span class="ab-strong">Teacher Portal</span><span class="ab-sub">Attendance, exam scores &amp; reports</span></div>
          </a>
          <a href="{{ route('student-portal.login') }}" class="access-btn ab-student">
            <div class="ab-icon"><i class="fas fa-user-graduate"></i></div>
            <div><span class="ab-strong">Student Portal</span><span class="ab-sub">Results, report cards &amp; notifications</span></div>
          </a>
        </div>

        <div class="portal-divider"><span>Access Information</span></div>

        <div class="portal-note">
          <i class="fas fa-info-circle"></i>
          <span>Each portal has its own login page. Use the credentials provided by your school administrator. If you have trouble logging in, contact the school office.</span>
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
</section>
@endsection
