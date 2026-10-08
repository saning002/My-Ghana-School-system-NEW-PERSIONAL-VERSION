<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@hasSection('page_title'){{ $site->site_name ?? 'School' }} — @yield('page_title')@else{{ $site->site_name ?? 'School' }}@endif</title>
  <meta name="description" content="@yield('meta_description', $site->tagline ?? 'Welcome to our school.')" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,700;1,9..144,300&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  @php
    $theme = ['primary'=>'#e9a422','secondary'=>'#0a1f44','accent'=>'#0891b2','text'=>'#3a3830','bg'=>'#ffffff','navBg'=>'#0a1f44'];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            $theme = ($site ?? \App\Models\Website\SiteSetting::instance())->websiteTheme;
        }
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
      --nav-bg:     {{ $theme['navBg'] }};
      /* ── Static design tokens ─── */
      --off-white:  #f8f7f4;
      --gray-100:   #f1f0ed;
      --gray-200:   #e4e2dd;
      --gray-400:   #a09e97;
      --gray-500:   #6e6c65;
      --gray-900:   #1a1814;
      --green:      #16a34a;
      --red:        #dc2626;
      --shadow-xs:  0 1px 2px rgba(0,0,0,.06);
      --shadow-sm:  0 2px 8px rgba(0,0,0,.08);
      --shadow:     0 4px 20px rgba(0,0,0,.09);
      --shadow-lg:  0 12px 48px rgba(0,0,0,.13);
      --shadow-xl:  0 24px 72px rgba(0,0,0,.16);
      --r-sm: 8px; --r: 14px; --r-lg: 22px; --r-xl: 32px;
      --ease: cubic-bezier(.4,0,.2,1);
      --trans: all .28s var(--ease);
    }
    /* ── Reset ─────────────────────────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; font-size: 16px; -webkit-text-size-adjust: 100%; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; color: var(--gray-700); background: var(--white); line-height: 1.7; overflow-x: hidden; }
    h1,h2,h3,h4,h5,h6 { font-family: 'Plus Jakarta Sans', sans-serif; line-height: 1.25; color: var(--gray-900); font-weight: 800; }
    a { text-decoration: none; color: inherit; }
    img { max-width: 100%; display: block; }
    ul { list-style: none; }
    button { font-family: inherit; cursor: pointer; }
    /* ── Utilities ─────────────────────────────────────────────────── */
    .container { max-width: 1180px; margin: 0 auto; padding: 0 28px; }
    .section    { padding: 100px 0; }
    .section-sm { padding: 64px 0; }
    .section-header { text-align: center; margin-bottom: 60px; }
    .section-header .eyebrow { display: inline-block; font-size: .7rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: var(--gold-dark); margin-bottom: 14px; }
    .section-header h2 { font-size: clamp(1.9rem, 3.5vw, 2.6rem); color: var(--navy); margin-bottom: 14px; }
    .section-header p { font-size: 1.05rem; color: var(--gray-500); max-width: 540px; margin: 0 auto; line-height: 1.75; }

    /* Buttons */
    .btn { display: inline-flex; align-items: center; gap: 8px; padding: 13px 28px; border-radius: 100px; font-weight: 700; font-size: .9rem; border: 2px solid transparent; transition: var(--trans); white-space: nowrap; letter-spacing: .01em; }
    .btn-primary { background: var(--gold); color: var(--navy); box-shadow: 0 4px 18px rgba(233,164,34,.38); }
    .btn-primary:hover { background: var(--gold-dark); transform: translateY(-2px); box-shadow: 0 8px 28px rgba(233,164,34,.45); }
    .btn-navy { background: var(--navy); color: var(--white); box-shadow: 0 4px 18px rgba(10,31,68,.28); }
    .btn-navy:hover { background: var(--navy-mid); transform: translateY(-2px); }
    .btn-outline-white { background: transparent; color: var(--white); border-color: rgba(255,255,255,.45); }
    .btn-outline-white:hover { background: rgba(255,255,255,.1); border-color: var(--white); }
    .btn-ghost { background: transparent; color: var(--gray-700); border-color: var(--gray-200); }
    .btn-ghost:hover { background: var(--gray-100); border-color: var(--gray-400); }
    .btn-sm { padding: 9px 20px; font-size: .82rem; }

    /* Cards */
    .card { background: var(--white); border-radius: var(--r-lg); box-shadow: var(--shadow-sm); transition: var(--trans); border: 1px solid var(--gray-200); }
    .card-hover:hover { transform: translateY(-5px); box-shadow: var(--shadow-lg); border-color: transparent; }
    /* ── Navbar ─────────────────────────────────────────────────────── */
    .nav { position: fixed; top: 0; left: 0; right: 0; z-index: 1000; transition: var(--trans); }
    .nav--top .nav-inner { background: transparent; border-bottom-color: transparent; }
    .nav--scrolled .nav-inner { background: color-mix(in srgb, var(--nav-bg) 96%, transparent); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); box-shadow: 0 4px 28px rgba(0,0,0,.22); }
    .nav-inner { max-width: 1180px; margin: 0 auto; padding: 0 28px; height: 72px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid transparent; transition: var(--trans); }
    .nav-brand { display: flex; align-items: center; gap: 11px; text-decoration: none; }
    .nav-logo { width: 42px; height: 42px; flex-shrink: 0; }
    .nav-brand-text { display: flex; flex-direction: column; }
    .nav-brand-name { font-size: 1.05rem; font-weight: 800; color: var(--white); line-height: 1.15; letter-spacing: -.01em; }
    .nav-brand-sub  { font-size: .62rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .1em; }
    .nav-links { display: flex; align-items: center; gap: 2px; }
    .nav-links a { padding: 7px 13px; border-radius: 8px; color: rgba(255,255,255,.78); font-size: .86rem; font-weight: 600; transition: var(--trans); }
    .nav-links a:hover, .nav-links a.active { color: var(--white); background: rgba(255,255,255,.1); }
    .nav-actions { display: flex; align-items: center; gap: 8px; margin-left: 16px; }
    .nav-cta-ghost { padding: 8px 18px; border-radius: 100px; border: 1.5px solid rgba(255,255,255,.3); color: var(--white); font-size: .84rem; font-weight: 700; transition: var(--trans); }
    .nav-cta-ghost:hover { border-color: var(--white); background: rgba(255,255,255,.08); }
    .nav-cta-primary { padding: 9px 20px; border-radius: 100px; background: var(--gold); color: var(--navy); font-size: .84rem; font-weight: 700; transition: var(--trans); box-shadow: 0 3px 14px rgba(233,164,34,.38); }
    .nav-cta-primary:hover { background: var(--gold-dark); transform: translateY(-1px); }
    .hamburger { display: none; flex-direction: column; gap: 5px; padding: 8px; border: none; background: none; cursor: pointer; }
    .hamburger span { display: block; width: 22px; height: 2px; background: var(--white); border-radius: 2px; transition: var(--trans); }
    .hamburger.open span:nth-child(1) { transform: translateY(7px) rotate(45deg); }
    .hamburger.open span:nth-child(2) { opacity: 0; }
    .hamburger.open span:nth-child(3) { transform: translateY(-7px) rotate(-45deg); }
    /* Mobile nav */
    .mobile-nav { display: none; position: fixed; inset: 0; top: 72px; background: rgba(8,20,50,.98); backdrop-filter: blur(20px); padding: 20px; z-index: 998; flex-direction: column; }
    .mobile-nav.open { display: flex; }
    .mobile-nav a { display: flex; align-items: center; gap: 14px; padding: 15px 16px; color: rgba(255,255,255,.82); font-size: .98rem; font-weight: 600; border-radius: 12px; transition: var(--trans); }
    .mobile-nav a:hover { background: rgba(255,255,255,.07); color: var(--white); }
    .mobile-nav a i { width: 18px; text-align: center; color: var(--gold); opacity: .85; font-size: .9rem; }
    .mobile-nav-divider { height: 1px; background: rgba(255,255,255,.08); margin: 10px 0; }
    .mobile-nav-actions { display: flex; flex-direction: column; gap: 10px; padding: 10px 0; }
    .mobile-nav-actions a { border-radius: 14px; padding: 14px 16px; text-align: center; font-weight: 700; }
    /* ── Page hero banner ───────────────────────────────────────────── */
    .page-hero { padding: 148px 0 72px; text-align: center; background: linear-gradient(155deg, var(--navy) 0%, #162d60 60%, #1a1a50 100%); position: relative; overflow: hidden; }
    .page-hero::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.025'%3E%3Ccircle cx='40' cy='40' r='1.5'/%3E%3C/g%3E%3C/svg%3E"); }
    .page-hero-inner { position: relative; z-index: 2; }
    .page-hero h1 { font-size: clamp(2rem, 4.5vw, 3.2rem); color: var(--white); margin-bottom: 14px; font-weight: 800; }
    .page-hero h1 em { font-style: normal; color: var(--gold); }
    .page-hero p { color: rgba(255,255,255,.65); font-size: 1.05rem; max-width: 520px; margin: 0 auto 22px; }
    .breadcrumb { display: flex; align-items: center; justify-content: center; gap: 8px; font-size: .8rem; color: rgba(255,255,255,.45); }
    .breadcrumb a { color: rgba(255,255,255,.6); transition: var(--trans); }
    .breadcrumb a:hover { color: var(--gold); }
    .breadcrumb i { font-size: .6rem; }

    /* ── Flash messages ─────────────────────────────────────────────── */
    .flash-wrap { position: fixed; top: 84px; right: 24px; z-index: 2000; width: 360px; }
    .flash { display: flex; align-items: flex-start; gap: 12px; padding: 15px 18px; border-radius: var(--r); margin-bottom: 10px; box-shadow: var(--shadow-lg); animation: slideIn .35s var(--ease); }
    .flash.success { background: #f0fdf4; border-left: 4px solid var(--green); color: #14532d; }
    .flash.error   { background: #fef2f2; border-left: 4px solid var(--red);   color: #7f1d1d; }
    .flash-close   { margin-left: auto; background: none; border: none; font-size: .95rem; opacity: .5; cursor: pointer; color: inherit; }
    @keyframes slideIn { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

    /* ── Footer ─────────────────────────────────────────────────────── */
    .footer { background: #050d1e; color: rgba(255,255,255,.6); padding: 80px 0 0; }
    .footer-grid { display: grid; grid-template-columns: 2fr 1fr 1fr 1.4fr; gap: 48px; padding-bottom: 56px; border-bottom: 1px solid rgba(255,255,255,.07); }
    .footer-logo-row { display: flex; align-items: center; gap: 10px; margin-bottom: 18px; }
    .footer-brand-name { font-size: 1.15rem; font-weight: 800; color: var(--white); }
    .footer-desc { font-size: .88rem; line-height: 1.85; margin-bottom: 22px; color: rgba(255,255,255,.48); }
    .footer-socials { display: flex; gap: 8px; }
    .footer-social { width: 36px; height: 36px; border-radius: 10px; background: rgba(255,255,255,.07); display: flex; align-items: center; justify-content: center; color: rgba(255,255,255,.6); font-size: .85rem; transition: var(--trans); }
    .footer-social:hover { background: var(--gold); color: var(--navy); transform: translateY(-2px); }
    .footer-col h5 { font-size: .8rem; font-weight: 700; color: var(--white); text-transform: uppercase; letter-spacing: .1em; margin-bottom: 18px; }
    .footer-col ul li { margin-bottom: 10px; }
    .footer-col ul li a { font-size: .88rem; color: rgba(255,255,255,.5); transition: var(--trans); display: flex; align-items: center; gap: 7px; }
    .footer-col ul li a:hover { color: var(--gold); padding-left: 3px; }
    .footer-col ul li a i { font-size: .7rem; opacity: .6; }
    .footer-contact-item { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
    .footer-contact-item i { color: var(--gold); width: 16px; margin-top: 3px; flex-shrink: 0; font-size: .85rem; }
    .footer-contact-item span { font-size: .88rem; color: rgba(255,255,255,.5); line-height: 1.6; }
    .footer-bottom { padding: 20px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; }
    .footer-bottom p { font-size: .8rem; color: rgba(255,255,255,.28); }
    .footer-bottom-links { display: flex; gap: 20px; }
    .footer-bottom-links a { font-size: .8rem; color: rgba(255,255,255,.3); transition: var(--trans); }
    .footer-bottom-links a:hover { color: var(--gold); }

    /* ── Scroll-to-top ──────────────────────────────────────────────── */
    .scroll-top { position: fixed; bottom: 28px; right: 28px; z-index: 990; width: 44px; height: 44px; border-radius: 50%; background: var(--gold); color: var(--navy); display: flex; align-items: center; justify-content: center; border: none; font-size: 1rem; box-shadow: 0 4px 18px rgba(233,164,34,.4); cursor: pointer; transition: var(--trans); opacity: 0; transform: translateY(12px) scale(.9); pointer-events: none; }
    .scroll-top.show { opacity: 1; transform: translateY(0) scale(1); pointer-events: auto; }
    .scroll-top:hover { transform: translateY(-3px) scale(1.05); }

    /* ── Animations ─────────────────────────────────────────────────── */
    .reveal { opacity: 0; transform: translateY(28px); transition: opacity .65s var(--ease), transform .65s var(--ease); }
    .reveal.in { opacity: 1; transform: translateY(0); }
    .reveal-delay-1 { transition-delay: .1s; }
    .reveal-delay-2 { transition-delay: .2s; }
    .reveal-delay-3 { transition-delay: .3s; }

    /* ── Responsive ─────────────────────────────────────────────────── */
    @media (max-width: 1024px) { .footer-grid { grid-template-columns: 1fr 1fr; gap: 36px; } }
    @media (max-width: 768px) {
      .nav-links, .nav-actions { display: none; }
      .hamburger { display: flex; }
      .section { padding: 60px 0; }
      .section-sm { padding: 44px 0; }
      .section-header { margin-bottom: 40px; }
      .section-header h2 { font-size: 1.7rem; }
      .section-header p { font-size: .95rem; }
      .container { padding: 0 18px; }
      .footer-grid { grid-template-columns: 1fr; gap: 28px; }
      .footer-bottom { flex-direction: column; text-align: center; }
      .footer-bottom-links { flex-wrap: wrap; justify-content: center; gap: 12px; }
      .footer { padding: 52px 0 0; }
      .page-hero { padding: 110px 0 52px; }
      .page-hero h1 { font-size: 1.9rem; }
      .page-hero p { font-size: .93rem; }
      .btn { padding: 11px 22px; font-size: .85rem; }
      .flash-wrap { width: calc(100vw - 32px); right: 16px; }
    }
    @media (max-width: 480px) {
      .container { padding: 0 16px; }
      .section { padding: 48px 0; }
      .section-header h2 { font-size: 1.5rem; }
      .page-hero { padding: 96px 0 44px; }
      .page-hero h1 { font-size: 1.65rem; }
      .btn { padding: 10px 18px; font-size: .82rem; }
      .scroll-top { bottom: 16px; right: 16px; width: 40px; height: 40px; }
    }

    @yield('extra_css')
  </style>
</head>
<body>

{{-- ── NAVBAR ─────────────────────────────────────────────────────── --}}
<nav class="nav nav--top" id="mainNav" role="navigation" aria-label="Main navigation">
  <div class="nav-inner">
    <a href="{{ route('website.home') }}" class="nav-brand" aria-label="{{ $site->site_name ?? 'School' }} home">
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
      <a href="{{ route('website.home') }}"       @class(['active' => request()->routeIs('website.home')])>Home</a>
      <a href="{{ route('website.about') }}"      @class(['active' => request()->routeIs('website.about')])>About</a>
      <a href="{{ route('website.academics') }}"  @class(['active' => request()->routeIs('website.academics')])>Academics</a>
      <a href="{{ route('website.admissions') }}" @class(['active' => request()->routeIs('website.admissions')])>Admissions</a>
      <a href="{{ route('website.gallery') }}"    @class(['active' => request()->routeIs('website.gallery')])>Gallery</a>
      <a href="{{ route('website.events') }}"     @class(['active' => request()->routeIs('website.events')])>Events</a>
      <a href="{{ route('website.contact') }}"    @class(['active' => request()->routeIs('website.contact')])>Contact</a>
    </div>
    <div class="nav-actions">
      <a href="{{ route('website.portal') }}" class="nav-cta-ghost"><i class="fas fa-shield-halved"></i> {{ $site->nav_portal_text ?? 'Portals' }}</a>
      <a href="{{ route('website.apply') }}"  class="nav-cta-primary">{{ $site->nav_enroll_text ?? 'Enroll Now' }}</a>
    </div>
    <button class="hamburger" id="hamburger" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>
  </div>
</nav>

{{-- Mobile nav --}}
<div class="mobile-nav" id="mobileNav" role="dialog" aria-label="Mobile navigation">
  <a href="{{ route('website.home') }}"><i class="fas fa-home"></i> Home</a>
  <a href="{{ route('website.about') }}"><i class="fas fa-school"></i> About Us</a>
  <a href="{{ route('website.academics') }}"><i class="fas fa-graduation-cap"></i> Academics</a>
  <a href="{{ route('website.admissions') }}"><i class="fas fa-file-alt"></i> Admissions</a>
  <a href="{{ route('website.gallery') }}"><i class="fas fa-images"></i> Gallery</a>
  <a href="{{ route('website.events') }}"><i class="fas fa-calendar-alt"></i> Events</a>
  <a href="{{ route('website.contact') }}"><i class="fas fa-envelope"></i> Contact</a>
  <div class="mobile-nav-divider"></div>
  <div class="mobile-nav-actions">
    <a href="{{ route('website.portal') }}" style="background:rgba(255,255,255,.07);color:rgba(255,255,255,.85);"><i class="fas fa-shield-halved" style="color:var(--gold);"></i> View Portals</a>
    <a href="{{ route('website.apply') }}"  style="background:var(--gold);color:var(--navy);"><i class="fas fa-star"></i> Enroll Now</a>
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

<main id="main">@yield('content')</main>

{{-- ── FOOTER ─────────────────────────────────────────────────────── --}}
<footer class="footer">
  <div class="container">
    <div class="footer-grid">
      <div>
        <div class="footer-logo-row">
          @if(isset($site) && $site->logo_url)
            <img src="{{ $site->logo_url }}" alt="{{ $site->footer_site_name ?? $site->site_name }}" style="height:34px;width:auto;object-fit:contain;margin-right:8px;">
          @else
            <svg width="34" height="34" viewBox="0 0 80 80" fill="none"><path d="M40 4L10 22v22c0 20 30 32 30 32s30-12 30-32V22L40 4z" fill="rgba(255,255,255,.08)" stroke="#e9a422" stroke-width="2"/><path d="M28 40l12-8 12 8v14l-12 8-12-8V40z" fill="#e9a422"/><rect x="36" y="36" width="8" height="18" rx="1" fill="#050d1e"/></svg>
          @endif
          <span class="footer-brand-name">{{ $site->footer_site_name ?? $site->site_name ?? 'School' }}</span>
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
          <li><a href="{{ route('website.home') }}"><i class="fas fa-chevron-right"></i>Home</a></li>
          <li><a href="{{ route('website.about') }}"><i class="fas fa-chevron-right"></i>About Us</a></li>
          <li><a href="{{ route('website.academics') }}"><i class="fas fa-chevron-right"></i>Academics</a></li>
          <li><a href="{{ route('website.admissions') }}"><i class="fas fa-chevron-right"></i>Admissions</a></li>
          <li><a href="{{ route('website.events') }}"><i class="fas fa-chevron-right"></i>Events &amp; News</a></li>
          <li><a href="{{ route('website.gallery') }}"><i class="fas fa-chevron-right"></i>Gallery</a></li>
          <li><a href="{{ route('website.contact') }}"><i class="fas fa-chevron-right"></i>Contact</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h5>{{ $site->footer_programs_title ?? 'Programs' }}</h5>
        <ul>
          @php
          $footerPrograms = $site->footer_programs ?? [
            ['name'=>'Day Care (Ages 1–2)',      'href'=>route('website.academics')],
            ['name'=>'Nursery (Ages 2–3)',        'href'=>route('website.academics')],
            ['name'=>'Preschool (Ages 3–4)',      'href'=>route('website.academics')],
            ['name'=>'Kindergarten (Ages 4–5)',   'href'=>route('website.academics')],
          ];
          @endphp
          @foreach($footerPrograms as $fp)
          <li><a href="{{ $fp['href'] ?? route('website.academics') }}"><i class="fas fa-chevron-right"></i>{{ $fp['name'] }}</a></li>
          @endforeach
          <li><a href="{{ route('website.apply') }}" style="color:var(--gold);"><i class="fas fa-chevron-right"></i>{{ $site->footer_apply_text ?? 'Apply Online' }}</a></li>
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
        <a href="{{ route('website.portal') }}" style="display:inline-flex;align-items:center;gap:6px;margin-top:16px;font-size:.82rem;font-weight:700;color:var(--gold);"><i class="fas fa-shield-halved"></i> {{ $site->footer_portal_text ?? 'Staff Portals' }}</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; {{ date('Y') }} {{ $site->footer_site_name ?? $site->site_name ?? 'School' }}. {{ $site->footer_copyright ?? 'All rights reserved.' }}</p>
      <div class="footer-bottom-links">
        <a href="{{ $site->footer_privacy_url ?: '#' }}">{{ $site->footer_privacy_text ?? 'Privacy Policy' }}</a>
        <a href="{{ $site->footer_terms_url ?: '#' }}">{{ $site->footer_terms_text ?? 'Terms of Use' }}</a>
        <a href="{{ $site->footer_support_url ?: route('website.contact') }}">{{ $site->footer_support_text ?? 'Support' }}</a>
      </div>
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
