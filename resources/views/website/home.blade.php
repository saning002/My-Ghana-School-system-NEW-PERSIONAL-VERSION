@extends('website.layout')
@section('page_title','Welcome')
@section('meta_description', $site->tagline ?? 'A nurturing, joyful environment where every child thrives.')

@section('extra_css')
@php
$heroBgType = $site->hero_bg_type   ?? 'default';
$heroLayout = $site->hero_layout    ?? 'split';
$rawOpacity = (int)($site->hero_overlay_opacity ?? 0);
// Get theme colours so hero gradient defaults follow the school theme
$wt = $site->websiteTheme;
$themePrimary   = $wt['primary']   ?? '#e9a422';
$themeSecondary = $wt['secondary'] ?? '#0a1f44';
$themeNavBg     = $wt['navBg']     ?? '#0a1f44';

if (in_array($heroBgType, ['image','video'])) {
    $overlayOp = $rawOpacity / 100;
} else {
    $overlayOp = 0;
}
if ($heroBgType === 'solid') {
    $heroBg = 'background:' . ($site->hero_bg_solid_color ?? $themeSecondary) . ';';
} elseif ($heroBgType === 'gradient') {
    $via = $site->hero_gradient_via ? ',' . $site->hero_gradient_via : '';
    $from = $site->hero_gradient_from ?? $themeSecondary;
    $to   = $site->hero_gradient_to   ?? $themeNavBg;
    $heroBg = 'background:linear-gradient(' . ($site->hero_gradient_angle ?? 150) . 'deg,'
            . $from . $via . ',' . $to . ');';
} elseif ($heroBgType === 'glassmorphism') {
    $from = $site->hero_gradient_from ?? $themeSecondary;
    $to   = $site->hero_gradient_to   ?? $themeNavBg;
    $heroBg = 'background:linear-gradient(' . ($site->hero_gradient_angle ?? 135) . 'deg,'
            . $from . '80,' . $to . 'c0);'
            . 'backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);';
} elseif ($heroBgType === 'image' && $site->hero_bg_image) {
    $imgUrl = img_url($site->hero_bg_image);
    $heroBg = $imgUrl
        ? 'background:url(' . $imgUrl . ') center/cover no-repeat;'
        : 'background:linear-gradient(150deg,' . $themeSecondary . ' 0%,' . $themeNavBg . ' 100%);';
} elseif ($heroBgType === 'video') {
    $heroBg = 'background:' . $themeSecondary . ';'; // dark fallback shown while video loads
    $overlayOp = $rawOpacity > 0 ? $rawOpacity / 100 : 0.45;
} else {
    // Default - use the school's theme secondary/navBg colours
    $heroBg = 'background:linear-gradient(150deg,' . $themeSecondary . ' 0%,' . $themeNavBg . ' 45%,' . $themeNavBg . ' 100%);';
}
if ($heroLayout === 'centered') {
    $innerCss = 'max-width:800px;text-align:center;';
    $ctasCss  = 'justify-content:center;'; $statsCss = 'justify-content:center;';
    $panelShow = 'display:none;'; $descExtra = 'max-width:none;margin-left:auto;margin-right:auto;';
    $gridCols = '1fr'; $showStats = true; $showCtasBool = true;
} elseif ($heroLayout === 'fullscreen') {
    $innerCss = 'max-width:1000px;text-align:center;padding:160px 28px 120px;';
    $ctasCss  = 'justify-content:center;'; $statsCss = 'justify-content:center;';
    $panelShow = 'display:none;'; $descExtra = 'max-width:none;margin-left:auto;margin-right:auto;';
    $gridCols = '1fr'; $showStats = true; $showCtasBool = true;
} elseif ($heroLayout === 'minimal') {
    $innerCss = 'max-width:600px;text-align:center;padding:160px 28px;';
    $ctasCss  = 'display:none;'; $statsCss = 'display:none;';
    $panelShow = 'display:none;'; $descExtra = 'max-width:none;margin-left:auto;margin-right:auto;';
    $gridCols = '1fr'; $showStats = false; $showCtasBool = false;
} else {
    $innerCss = 'max-width:1180px;'; $ctasCss = 'justify-content:flex-start;'; $statsCss = 'justify-content:flex-start;';
    $panelShow = 'display:flex;'; $descExtra = ''; $gridCols = '1fr 1fr'?'0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'?''?'';position:absolute;top:-60px;left:-60px;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.07);}
.cta-banner::after{content:'';position:absolute;bottom:-80px;right:-40px;width:360px;height:360px;border-radius:50%;background:rgba(255,255,255,.06);}
.cta-banner-inner{position:relative;z-index:2;}
.cta-banner h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:800;color:var(--navy);margin-bottom:14px;}
.cta-banner p{font-size:1.05rem;color:var(--gray-700);opacity:.65;margin-bottom:36px;}
.cta-banner-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}

@media(max-width:1024px){.why-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:640px){.why-grid{grid-template-columns:1fr;}}
@endsection

@section('content'?'video' && $site->hero_bg_video_url)
        @php
            $videoUrl = $site->hero_bg_video_url;
            $isYoutube = str_contains($videoUrl, 'youtube.com') || str_contains($videoUrl, 'youtu.be');
            if ($isYoutube) {
                // Extract video ID and build embed URL
                preg_match('/(?:v=|youtu\.be\/|embed\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $m);
                $ytId = $m[1] ?? '';
                $embedUrl = 'https://www.youtube.com/embed/' . $ytId . '?autoplay=1&mute=1&loop=1&playlist=' . $ytId . '&controls=0&showinfo=0&rel=0&modestbranding=1';
            }
        @endphp
        @if($isYoutube && !empty($ytId))
        <iframe class="hero-video" src="{{ $embedUrl }}"
                frameborder="0" allow="autoplay; encrypted-media" allowfullscreen
                style="pointer-events:none;"></iframe>
        @else
        <video class="hero-video" autoplay muted loop playsinline>
            <source src="{{ $videoUrl }}" type="video/mp4">
        </video>
        @endif
    @endif
    <div class="hero-overlay"></div>
    <div class="hero-noise"></div>
    <div class="hero-glow hero-glow-1"></div>
    <div class="hero-glow hero-glow-2"></div>

    <div class="hero-inner">
        {{-- Left / main content --}}
        <div>
            <div class="hero-eyebrow">
                <span class="relative flex h-2 w-2" style="display:inline-block;width:8px;height:8px;border-radius:50%;background:#34d399;margin-right:4px;"></span>
                {{ $site->hero_eyebrow ?? 'Welcome to ' . $site->site_name }}
            </div>
            <h1>{!! $site->hero_title ?? 'Nurturing Young Minds for a <em>Bright Future</em>' !!}</h1>
            <p class="hero-desc">{{ $site->hero_subtitle ?? 'A premier learning environment where every child thrives through play-based education, expert care, and endless possibilities.' }}</p>

            @if($showCtasBool ?? true)
            <div class="hero-ctas">
                <a href="{{ wu('apply') }}" class="btn btn-primary">
                    <i class="fas fa-graduation-cap"></i> {{ $site->hero_cta_primary_text ?? 'Apply Now' }}
                </a>
                <a href="{{ wu('about') }}" class="btn btn-outline">
                    <i class="fas fa-arrow-right"></i> {{ $site->hero_cta_secondary_text ?? 'Learn More' }}
                </a>
            </div>
            @endif

            @if($showStats ?? true)
            <div class="hero-stats">
                <div class="hero-stat"><strong>200+</strong><span>Students</span></div>
                <div class="hero-stat"><strong>15+</strong><span>Teachers</span></div>
                <div class="hero-stat"><strong>10+</strong><span>Years</span></div>
            </div>
            @endif
        </div>

        {{-- Right panel – split layout only --}}
        <div class="hero-panel">
            <div class="hero-card">
                <div class="hero-card-label">{{ $site->hero_panel_title ?? 'World-Class Education' }}</div>
                <div class="hero-card-value">{{ $site->hero_panel_subtitle ?? 'Holistic development for every child'?'icon'=>'fa-heart','title'=>'Child-Centred Care','desc'=>'Every decision we make puts your child\'s wellbeing and happiness first.'],
    ['icon'=>'fa-chalkboard-teacher','title'=>'Expert Educators','desc'=>'Our qualified teachers bring passion and expertise to every classroom.'],
    ['icon'=>'fa-shield-alt','title'=>'Safe Environment','desc'=>'Secure, nurturing spaces where children feel confident to learn and grow.'],
    ['icon'=>'fa-users','title'=>'Strong Community','desc'=>'A welcoming family of parents, staff, and children working together.'],
    ['icon'=>'fa-book-open','title'=>'Rich Curriculum','desc'=>'Play-based and structured learning that sparks curiosity at every stage.'],
    ['icon'=>'fa-star','title'=>'Proven Results','desc'=>'Years of experience delivering outstanding early-childhood outcomes.'],
];
@endphp
<section class="section why">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->why_eyebrow ?? 'Why Choose Us' }}</span>
      <h2>{{ $site->why_title ?? 'Where Every Child Comes First' }}</h2>
      <p>{{ $site->why_subtitle ?? 'We provide a safe, stimulating environment for children to thrive.' }}</p>
    </div>
    <div class="why-grid">
      @foreach($whyFeatures as $f)
      <div class="why-card reveal">
        <div class="why-icon"><i class="fas {{ $f['icon'] ?? 'fa-star' }}"></i></div>
        <h3>{{ $f['title'] }}</h3>
        <p>{{ $f['desc'?'Parent Reviews' }}</span>
      <h2 style="color:#fff;">{{ $site->testimonials_title ?? 'What Parents Say' }}</h2>
      <p style="color:rgba(255,255,255,.6);">{{ $site->testimonials_subtitle ?? 'Real stories from our school community.' }}</p>
    </div>
    <div class="testi-grid">
      @foreach($testimonials as $t)
      <div class="testi-card reveal">
        <div class="testi-stars">{{ str_repeat('?', $t->rating) }}{{ str_repeat('?', 5 - $t->rating) }}</div>
        <p class="testi-text">"{{ $t->message }}"</p>
        <div class="testi-author">
          <div class="testi-avatar">{{ strtoupper(substr($t->parent_name,0,1)) }}</div>
          <div>
            <div class="testi-name">{{ $t->parent_name }}</div>
            @if($t->child_name)<div class="testi-role">Parent of {{ $t->child_name }}</div>@endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ?.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.? BLOG ?.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.??.? --}}
@if($posts->count())
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->blog_eyebrow ?? 'Latest News' }}</span>
      <h2>{{ $site->blog_title ?? 'From Our Blog' }}</h2>
      <p>{{ $site->blog_subtitle ?? 'Stay up to date with school news and events.'?'M d, Y') }}</div>
          <h3>{{ $post->title }}</h3>
          <p>{{ Str::limit($post->excerpt ?? $post->content, 100) }}</p>
          <a href="{{ wu('blog', '/'?'School Calendar' }}</span>
      <h2>{{ $site->events_title ?? 'Upcoming Events' }}</h2>
      <p>{{ $site->events_subtitle ?? 'Mark your calendar for these exciting school events.'?'M d, Y') }}{{ $event->time ? ' · ' . $event->time : ''?'Ready to Join Our Family?' }}</h2>
    <p>{{ $site->cta_subtitle ?? 'Applications are open. Spots are limited – secure your child\'s place today.' }}</p>
    <div class="cta-banner-btns">
      <a href="{{ wu('apply') }}" class="btn btn-navy btn-lg">
        <i class="fas fa-file-alt"></i> Apply Now
      </a>
      <a href="{{ wu('contact') }}" class="btn btn-outline-navy btn-lg">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
    </div>
  </div>
</section>

@endsection
