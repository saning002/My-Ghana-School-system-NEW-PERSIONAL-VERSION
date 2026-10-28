<!DOCTYPE html>
<html lang="en">
<head>
@php
    // = Slug-aware URL helper =
    // If the current URL contains /school/{slug}/website, use slug-based
    // routes so the URL stays as /school/{slug}/website/* during navigation.
    // Otherwise fall back to the session-based /website/* routes.
    $urlSlug = null;
    $path = request()->path(); // e.g. "school/saning-academy/website/about"
    if (preg_match('#^school/([^/]+)/website#', $path, $m)) {
        $urlSlug = $m[1];
    }
    // Helper closure: generate URL using slug if available
    $wu = function(string $page, string $extra = '') use ($urlSlug): string {
        if ($urlSlug) {
            $base = url('/school/' . $urlSlug . '/website');
            return $page === '' ? $base : $base . '/' . $page . $extra;
        }
        $map = [
            ''          => wu('home'),
            'home'      => wu('home'),
            'about'     => wu('about'),
            'academics' => wu('academics'),
            'admissions'=> wu('admissions'),
            'gallery'   => wu('gallery'),
            'events'    => wu('events'),
            'blog'      => wu('blog'),
            'staff'     => wu('staff'),
            'contact'   => wu('contact'),
            'portal'    => wu('portal'),
            'apply'     => wu('apply'),
        ];
        return $map[$page] ?? wu('home');
    };
@endphp
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@hasSection('page_title'){{ $site->site_name ?? 'School' }} – @yield('page_title')@else{{ $site->site_name ?? 'School' }}@endif</title>
  <meta name="description" content="@yield('meta_description', $site->tagline ?? 'Welcome to our school.')" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  @php
    $theme = ['primary'=>'#e9a422','secondary'=>'#0a1f44','accent'=>'#0891b2','text'=>'#3a3830','bg'=>'#ffffff','navBg'=>'#0a1f44'];
    try {
        $theme = ($site ?? \App\Models\Website\SiteSetting::instance())->websiteTheme;
    } catch (\Throwable $__e) {}
  @endphp
  <style>
    :root {
      --gold:       {{ $theme['primary'] }};
      --gold-dark:  {{ \App\Http\Controllers\Admin\ThemeController::darken($theme['primary'], 15) }};
      --gold-pale:  {{ \App\Http\Controllers\Admin\ThemeController::lighten($theme['primary'], 85) }};
      --navy:       {{ $theme['secondary'] }};
      --navy-mid:   {{ \App\Http\Controllers\Admin\ThemeController::lighten($theme['secondary'], 15) }};
      --navy-light: {{ \App\Http\Controllers\Admin\ThemeController::lighten($theme['secondary'], 30) }};
      --teal:       {{ $theme['accent'] }};
      --gray-700:   {{ $theme['text'] }};
      --white:      {{ $theme['bg'] }};
      --nav-bg:     {{ $theme['navBg'?'Plus Jakarta Sans', sans-serif; color: var(--gray-700); background: var(--white); line-height: 1.7; overflow-x: hidden; }
    h1,h2,h3,h4,h5,h6 { font-family: 'Plus Jakarta Sans'?''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Ccircle cx='40' cy='40' r='1.5'?'extra_css'?'') }}" class="nav-brand" aria-label="{{ $site->site_name ?? 'School' }} home">
      @if(isset($site) && $site->logo_url)
        <img src="{{ $site->logo_url }}" alt="{{ $site->site_name }}" class="nav-logo">
      @else
        <svg class="nav-logo" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M40 4L10 22v22c0 20 30 32 30 32s30-12 30-32V22L40 4z" fill="#0a1f44" stroke="#e9a422" stroke-width="2.5"/>
          <path d="M28 40l12-8 12 8v14l-12 8-12-8V40z" fill="#e9a422"/>
          <rect x="36" y="36" width="8" height="18" rx="1" fill="#0a1f44"/>
          <circle cx="40" cy="26" r="4" fill="#e9a422"/>
        </svg>
      @endif
      <div class="nav-brand-text">
        <span class="nav-brand-name">{{ $site->site_name ?? 'School' }}</span>
        <span class="nav-brand-sub">{{ $site->nav_brand_sub ?? 'Education &amp; Excellence' }}</span>
      </div>
    </a>

    <div class="nav-links">
      <a href="{{ $wu('home') }}"       @class(['active' => request()->routeIs('website.home','school.website.home')])>Home</a>
      <a href="{{ $wu('about') }}"      @class(['active' => request()->routeIs('website.about','school.website.about')])>About</a>
      <a href="{{ $wu('academics') }}"  @class(['active' => request()->routeIs('website.academics','school.website.academics')])>Academics</a>
      <a href="{{ $wu('admissions') }}" @class(['active' => request()->routeIs('website.admissions','school.website.admissions')])>Admissions</a>
      <a href="{{ $wu('gallery') }}"    @class(['active' => request()->routeIs('website.gallery','school.website.gallery')])>Gallery</a>
      <a href="{{ $wu('events') }}"     @class(['active' => request()->routeIs('website.events','school.website.events')])>Events</a>
      <a href="{{ $wu('contact') }}"    @class(['active' => request()->routeIs('website.contact','school.website.contact')])>Contact</a>
    </div>
    <div class="nav-actions">
      <a href="{{ $wu('portal') }}" class="nav-cta-ghost"><i class="fas fa-shield-halved"></i> {{ $site->nav_portal_text ?? 'Portals' }}</a>
      <a href="{{ $wu('apply') }}"  class="nav-cta-primary">{{ $site->nav_enroll_text ?? 'Enroll Now' }}</a>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

{{-- Mobile nav --}}
<div class="mobile-nav" id="mobileNav" role="dialog" aria-label="Mobile navigation">
  <a href="{{ $wu('home') }}"><i class="fas fa-home"></i> Home</a>
  <a href="{{ $wu('about') }}"><i class="fas fa-school"></i> About Us</a>
  <a href="{{ $wu('academics') }}"><i class="fas fa-graduation-cap"></i> Academics</a>
  <a href="{{ $wu('admissions') }}"><i class="fas fa-file-alt"></i> Admissions</a>
  <a href="{{ $wu('gallery') }}"><i class="fas fa-images"></i> Gallery</a>
  <a href="{{ $wu('events') }}"><i class="fas fa-calendar-alt"></i> Events</a>
  <a href="{{ $wu('contact') }}"><i class="fas fa-envelope"></i> Contact</a>
  <div class="mobile-nav-divider"></div>
  <div class="mobile-nav-actions">
    <a href="{{ $wu('portal') }}" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.85);"><i class="fas fa-shield-halved" style="color:var(--gold);"></i> View Portals</a>
    <a href="{{ $wu('apply') }}"  style="background:var(--gold);color:var(--navy);"><i class="fas fa-star"></i> Enroll Now</a>
  </div>
</div>

{{-- Flash messages --}}
@if(session('success') || session('error'))
<div class="flash-wrap">
  @if(session('success'))
  <div class="flash success">
    <i class="fas fa-check-circle"></i>
    <span>{{ session('success') }}</span>
    <button class="flash-close" onclick="this.closest('.flash').remove()"><i class="fas fa-times"></i></button>
  </div>
  @endif
  @if(session('error'))
  <div class="flash error">
    <i class="fas fa-times-circle"></i>
    <span>{{ session('error') }}</span>
    <button class="flash-close" onclick="this.closest('.flash').remove()"><i class="fas fa-times"></i></button>
  </div>
  @endif
</div>
@endif

<main id="main">@yield('content'?'School' }}</span>
        </div>
        <p class="footer-desc">{{ $site->footer_tagline ?? $site->tagline ?? 'A nurturing environment where every child is celebrated, respected, and inspired to reach their full potential.' }}</p>
        <div class="footer-socials">
          @if(isset($site) && $site->facebook)<a href="{{ $site->facebook }}" class="footer-social" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>@endif
          @if(isset($site) && $site->instagram)<a href="{{ $site->instagram }}" class="footer-social" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>@endif
          @if(isset($site) && $site->twitter)<a href="{{ $site->twitter }}" class="footer-social" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>@endif
          @if(isset($site) && $site->youtube)<a href="{{ $site->youtube }}" class="footer-social" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>@endif
        </div>
      </div>
      <div class="footer-col">
        <h5>{{ $site->footer_nav_title ?? 'Navigation' }}</h5>
        <ul>
          <li><a href="{{ wu('home') }}"><i class="fas fa-chevron-right"></i>Home</a></li>
          <li><a href="{{ wu('about') }}"><i class="fas fa-chevron-right"></i>About Us</a></li>
          <li><a href="{{ wu('academics') }}"><i class="fas fa-chevron-right"></i>Academics</a></li>
          <li><a href="{{ wu('admissions') }}"><i class="fas fa-chevron-right"></i>Admissions</a></li>
          <li><a href="{{ wu('events') }}"><i class="fas fa-chevron-right"></i>Events &amp; News</a></li>
          <li><a href="{{ wu('gallery') }}"><i class="fas fa-chevron-right"></i>Gallery</a></li>
          <li><a href="{{ wu('contact') }}"><i class="fas fa-chevron-right"></i>Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>{{ $site->footer_programs_title ?? 'Programs' }}</h5>
        <ul>
          @php
          $footerPrograms = $site->footer_programs ?? [
            ['name'=>'Day Care (Ages 1–2)',      'href'=>wu('academics')],
            ['name'=>'Nursery (Ages 2–3)',        'href'=>wu('academics')],
            ['name'=>'Preschool (Ages 3–4)',      'href'=>wu('academics')],
            ['name'=>'Kindergarten (Ages 4–5)',   'href'=>wu('academics')],
          ];
          @endphp
          @foreach($footerPrograms as $fp)
          <li><a href="{{ $fp['href'] ?? wu('academics') }}"><i class="fas fa-chevron-right"></i>{{ $fp['name'] }}</a></li>
          @endforeach
          <li><a href="{{ wu('apply') }}" style="color:var(--gold);"><i class="fas fa-chevron-right"></i>{{ $site->footer_apply_text ?? 'Apply Online' }}</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>{{ $site->footer_contact_title ?? 'Contact' }}</h5>
        @if(!empty($site->footer_address) || !empty($site->address))
          <div class="footer-contact-item"><i class="fas fa-map-marker-alt"></i><span>{{ $site->footer_address ?: $site->address }}</span></div>
        @endif
        @if(!empty($site->footer_phone) || !empty($site->contact_phone))
          @php $fPhone = $site->footer_phone ?: $site->contact_phone; @endphp
          <div class="footer-contact-item"><i class="fas fa-phone"></i><span><a href="tel:{{ $fPhone }}" style="color:rgba(255,255,255,.5);">{{ $fPhone }}</a></span></div>
        @endif
        @if(!empty($site->footer_email) || !empty($site->contact_email))
          @php $fEmail = $site->footer_email ?: $site->contact_email; @endphp
          <div class="footer-contact-item"><i class="fas fa-envelope"></i><span><a href="mailto:{{ $fEmail }}" style="color:rgba(255,255,255,.5);">{{ $fEmail }}</a></span></div>
        @endif
        <div class="footer-contact-item"><i class="fas fa-clock"></i><span>{{ $site->footer_office_hours ?? $site->office_hours ?? 'Mon–Fri: 7:00 AM – 5:00 PM' }}</span></div>
        <a href="{{ wu('portal') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;font-size:.82rem;font-weight:700;color:var(--gold);"><i class="fas fa-shield-halved"></i> {{ $site->footer_portal_text ?? 'Staff Portals' }}</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; {{ date('Y') }} {{ $site->footer_site_name ?? $site->site_name ?? 'School' }}. {{ $site->footer_copyright ?? 'All rights reserved.' }}</p>
      <div class="footer-bottom-links">
        <a href="{{ $site->footer_privacy_url ?: '#' }}">{{ $site->footer_privacy_text ?? 'Privacy Policy' }}</a>
        <a href="{{ $site->footer_terms_url ?: '#' }}">{{ $site->footer_terms_text ?? 'Terms of Use' }}</a>
        <a href="{{ $site->footer_support_url ?: wu('contact') }}">{{ $site->footer_support_text ?? 'Support' }}</a>
      </div>
    </div>
    <div style="text-align:center;padding:10px 0 20px;border-top:1px solid rgba(255,255,255,.05);margin-top:0;">
      <p style="font-size:.76rem;color:rgba(255,255,255,.28);letter-spacing:.02em;">
        Powered &amp; built by <a href="https://saningtech.com" target="_blank" rel="noopener" style="color:rgba(255,255,255,.45);font-weight:700;text-decoration:none;transition:color .2s;" onmouseover="this.style.color='var(--gold)'" onmouseout="this.style.color='rgba(255,255,255,.45)'">SaningTech Solutions</a>
      </p>
    </div>
  </div>
</footer>

<button class="scroll-top" id="scrollTop" aria-label="Back to top"><i class="fas fa-arrow-up"></i></button>

<script>
(function(){
  var nav = document.getElementById('mainNav');
  var hbg = document.getElementById('hamburger');
  var mob = document.getElementById('mobileNav');
  var st  = document.getElementById('scrollTop');

  function onScroll() {
    var y = window.scrollY;
    nav.classList.toggle('nav--top', y < 40);
    nav.classList.toggle('nav--scrolled', y >= 40);
    st.classList.toggle('show', y > 320);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  hbg.addEventListener('click', function() {
    var open = mob.classList.toggle('open');
    hbg.classList.toggle('open', open);
    hbg.setAttribute('aria-expanded', open);
    document.body.style.overflow = open ? 'hidden' : '';
  });
  mob.querySelectorAll('a').forEach(function(a){ a.addEventListener('click', function(){ mob.classList.remove('open'); hbg.classList.remove('open'); document.body.style.overflow = ''; }); });

  st.addEventListener('click', function(){ window.scrollTo({ top: 0, behavior: 'smooth' }); });

  var io = new IntersectionObserver(function(entries) {
    entries.forEach(function(e) { if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); } });
  }, { threshold: 0.1 });
  document.querySelectorAll('.reveal').forEach(function(el){ io.observe(el); });

  document.querySelectorAll('.flash').forEach(function(el){
    setTimeout(function(){ el.style.opacity = '0'; el.style.transform = 'translateX(110%)'; el.style.transition = 'all .4s ease'; }, 5000);
    setTimeout(function(){ el.remove(); }, 5450);
  });
})();
</script>
@yield('extra_js')
</body>
</html>
