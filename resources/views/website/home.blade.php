@extends('website.layout')
@section('page_title','Welcome')
@section('meta_description', $site->tagline ?? 'A nurturing, joyful environment where every child thrives.')

@section('extra_css')
@php
use Illuminate\Support\Facades\Storage;
$heroBgType = $site->hero_bg_type   ?? 'default';
$heroLayout = $site->hero_layout    ?? 'split';
$rawOpacity = (int)($site->hero_overlay_opacity ?? 0);
if (in_array($heroBgType, ['image','video'])) {
    $overlayOp = $rawOpacity / 100;
} else {
    $overlayOp = 0;
}
if ($heroBgType === 'solid') {
    $heroBg = 'background:' . ($site->hero_bg_solid_color ?? '#0a1f44') . ';';
} elseif ($heroBgType === 'gradient') {
    $via = $site->hero_gradient_via ? ',' . $site->hero_gradient_via : '';
    $heroBg = 'background:linear-gradient(' . ($site->hero_gradient_angle ?? 150) . 'deg,'
            . ($site->hero_gradient_from ?? '#050d22') . $via . ','
            . ($site->hero_gradient_to   ?? '#122859') . ');';
} elseif ($heroBgType === 'glassmorphism') {
    $heroBg = 'background:linear-gradient(' . ($site->hero_gradient_angle ?? 135) . 'deg,'
            . ($site->hero_gradient_from ?? '#050d22') . '80,'
            . ($site->hero_gradient_to   ?? '#122859') . 'c0);'
            . 'backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);';
} elseif ($heroBgType === 'image' && $site->hero_bg_image) {
    $heroBg = 'background:url(' . Storage::disk('public')->url($site->hero_bg_image) . ') center/cover no-repeat;';
} elseif ($heroBgType === 'video') {
    $heroBg = 'background:#050d22;'; // dark fallback shown while video loads
    $overlayOp = $rawOpacity > 0 ? $rawOpacity / 100 : 0.45; // always overlay on video
} else {
    $heroBg = 'background:linear-gradient(150deg,#050d22 0%,#0a1f44 45%,#122859 100%);';
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
    $panelShow = 'display:flex;'; $descExtra = ''; $gridCols = '1fr 1fr';
    $showStats = true; $showCtasBool = true;
}
@endphp
/* ── Hero ── */
.hero{min-height:100vh;display:flex;align-items:center;position:relative;overflow:hidden;{{ $heroBg }}}
.hero-video{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);min-width:100%;min-height:100%;width:auto;height:auto;object-fit:cover;z-index:0;}
/* YouTube iframe needs different sizing */
iframe.hero-video{width:100vw;height:56.25vw;min-height:100vh;min-width:177.78vh;top:50%;left:50%;transform:translate(-50%,-50%);}
.hero-overlay{position:absolute;inset:0;background:rgba(0,0,0,{{ $overlayOp }});z-index:1;pointer-events:none;}
.hero-noise{position:absolute;inset:0;opacity:.03;z-index:2;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.75' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");}
.hero-glow{position:absolute;border-radius:50%;filter:blur(90px);pointer-events:none;z-index:2;}
.hero-glow-1{width:560px;height:560px;background:rgba(233,164,34,.12);top:-100px;right:-60px;}
.hero-glow-2{width:400px;height:400px;background:rgba(30,77,183,.16);bottom:-80px;left:-60px;}
.hero-inner{position:relative;z-index:10;margin:0 auto;padding:120px 28px 80px;display:grid;grid-template-columns:{{ $gridCols }};gap:72px;align-items:center;{{ $innerCss }}}
.hero-eyebrow{display:inline-flex;align-items:center;gap:8px;background:rgba(233,164,34,.12);border:1px solid rgba(233,164,34,.3);color:#e9a422;padding:6px 16px;border-radius:100px;font-size:.72rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;margin-bottom:22px;}
.hero h1{font-size:clamp(2.4rem,5vw,3.8rem);font-weight:800;color:#fff;line-height:1.1;margin-bottom:22px;letter-spacing:-.02em;}
.hero h1 em{font-style:normal;color:#e9a422;}
.hero-desc{font-size:1.08rem;color:rgba(255,255,255,.65);line-height:1.82;margin-bottom:36px;max-width:460px;{{ $descExtra }}}
.hero-ctas{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:52px;{{ $ctasCss }}}
.hero-stats{display:flex;gap:32px;flex-wrap:wrap;padding-top:32px;border-top:1px solid rgba(255,255,255,.1);{{ $statsCss }}}
.hero-stat strong{display:block;font-size:1.9rem;font-weight:800;color:#e9a422;line-height:1;}
.hero-stat span{font-size:.78rem;color:rgba(255,255,255,.45);margin-top:4px;display:block;text-transform:uppercase;letter-spacing:.06em;}
.hero-panel{flex-direction:column;gap:14px;{{ $panelShow }}}
.hero-card{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:20px;padding:22px;backdrop-filter:blur(12px);}
.hero-card-label{font-size:.72rem;font-weight:700;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin-bottom:6px;}
.hero-card-value{font-size:1.05rem;font-weight:700;color:#fff;}
.hero-grid-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.hero-mini{background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:18px;text-align:center;backdrop-filter:blur(12px);}
.hero-mini-val{font-size:1.6rem;font-weight:800;color:#e9a422;line-height:1;}
.hero-mini-label{font-size:.72rem;color:rgba(255,255,255,.45);margin-top:6px;text-transform:uppercase;letter-spacing:.06em;}
@media(max-width:1024px){.hero-inner{grid-template-columns:1fr!important;gap:40px!important;}.hero-panel{display:none!important;}}
@media(max-width:768px){
  .hero-inner{padding:100px 18px 64px!important;}
  .hero h1{font-size:clamp(1.9rem,7vw,2.6rem);}
  .hero-desc{font-size:.97rem;margin-bottom:28px;}
  .hero-ctas{gap:10px;margin-bottom:36px;}
  .hero-stats{gap:20px;padding-top:24px;}
  .hero-stat strong{font-size:1.5rem;}
  .hero-eyebrow{font-size:.65rem;}
  .why-grid{grid-template-columns:1fr 1fr;}
  .why-card{padding:26px 20px;}
  .testi-grid{grid-template-columns:1fr;}
  .blog-grid{grid-template-columns:1fr;}
  .cta-banner{padding:60px 0;}
  .cta-banner h2{font-size:1.6rem;}
  .cta-banner-btns{flex-direction:column;align-items:center;}
}
@media(max-width:480px){
  .hero-inner{padding:88px 16px 52px!important;}
  .hero h1{font-size:1.75rem;}
  .hero-ctas{flex-direction:column;}
  .hero-ctas .btn{width:100%;justify-content:center;}
  .hero-stats{gap:16px;}
  .why-grid{grid-template-columns:1fr;}
  .why-card{padding:22px 18px;}
  .prog-grid{grid-template-columns:1fr;}
}
.why{background:var(--off-white);}
.why-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:28px;}
.why-card{background:var(--white);border-radius:var(--r-xl);padding:36px 30px;text-align:center;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);transition:var(--trans);}
.why-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:transparent;}
.why-icon{width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,var(--navy),var(--teal));display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:1.5rem;color:var(--white);}
.why-card h3{font-size:1.05rem;font-weight:800;color:var(--navy);margin-bottom:10px;}
.why-card p{font-size:.9rem;color:var(--gray-500);line-height:1.75;}

/* ── Programs ── */
.prog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(256px,1fr));gap:24px;}
.prog-card{border-radius:var(--r-xl);overflow:hidden;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);background:var(--white);transition:var(--trans);}
.prog-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);border-color:transparent;}
.prog-head{padding:28px 26px 22px;background:linear-gradient(140deg,var(--navy) 0%,#162d60 100%);position:relative;overflow:hidden;}
.prog-head::after{content:'';position:absolute;bottom:-30px;right:-30px;width:100px;height:100px;border-radius:50%;background:rgba(255,255,255,.04);}
.prog-num{font-size:3rem;font-weight:800;color:rgba(233,164,34,.2);line-height:1;margin-bottom:4px;}
.prog-head h3{font-size:1.15rem;font-weight:800;color:#fff;margin-bottom:4px;}
.prog-head p{font-size:.82rem;color:rgba(255,255,255,.55);}
.prog-body{padding:20px 26px 24px;}
.prog-body p{font-size:.9rem;color:var(--gray-500);line-height:1.75;margin-bottom:16px;}

/* ── Testimonials ── */
.testi-section{background:linear-gradient(160deg,#060f28 0%,#0a1f44 100%);}
.testi-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:20px;}
.testi-card{background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.08);border-radius:20px;padding:26px;transition:var(--trans);}
.testi-card:hover{background:rgba(255,255,255,.08);}
.testi-stars{color:#e9a422;font-size:.85rem;letter-spacing:2px;margin-bottom:14px;}
.testi-text{font-size:.93rem;color:rgba(255,255,255,.78);line-height:1.8;margin-bottom:20px;font-style:italic;}
.testi-author{display:flex;align-items:center;gap:12px;}
.testi-avatar{width:42px;height:42px;border-radius:50%;background:#e9a422;display:flex;align-items:center;justify-content:center;font-weight:800;color:var(--navy);font-size:1rem;flex-shrink:0;overflow:hidden;}
.testi-avatar img{width:100%;height:100%;object-fit:cover;}
.testi-name{font-size:.88rem;font-weight:700;color:#fff;}
.testi-role{font-size:.75rem;color:rgba(255,255,255,.4);margin-top:2px;}

/* ── Blog ── */
.blog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;}
.blog-card{border-radius:var(--r-xl);overflow:hidden;background:var(--white);box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);transition:var(--trans);}
.blog-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);border-color:transparent;}
.blog-thumb{height:196px;overflow:hidden;background:linear-gradient(135deg,var(--navy-light),var(--teal));display:flex;align-items:center;justify-content:center;font-size:2.8rem;}
.blog-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .45s ease;}
.blog-card:hover .blog-thumb img{transform:scale(1.04);}
.blog-body{padding:22px 24px 24px;}
.blog-date{font-size:.73rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;}
.blog-body h3{font-size:1rem;font-weight:800;color:var(--navy);margin-bottom:9px;line-height:1.45;}
.blog-body p{font-size:.88rem;color:var(--gray-500);line-height:1.72;margin-bottom:16px;}
.blog-link{font-size:.82rem;font-weight:700;color:var(--gold-dark);display:inline-flex;align-items:center;gap:5px;transition:var(--trans);}
.blog-link:hover{gap:9px;}

/* ── CTA banner ── */
.cta-banner{background:linear-gradient(135deg,var(--gold) 0%,var(--gold-dark) 100%);padding:88px 0;text-align:center;position:relative;overflow:hidden;}
.cta-banner::before{content:'';position:absolute;top:-60px;left:-60px;width:280px;height:280px;border-radius:50%;background:rgba(255,255,255,.07);}
.cta-banner::after{content:'';position:absolute;bottom:-80px;right:-40px;width:360px;height:360px;border-radius:50%;background:rgba(255,255,255,.06);}
.cta-banner-inner{position:relative;z-index:2;}
.cta-banner h2{font-size:clamp(1.8rem,3.5vw,2.8rem);font-weight:800;color:var(--navy);margin-bottom:14px;}
.cta-banner p{font-size:1.05rem;color:rgba(10,31,68,.65);margin-bottom:36px;}
.cta-banner-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;}

@media(max-width:1024px){.why-grid{grid-template-columns:1fr 1fr;}}
@media(max-width:640px){.why-grid{grid-template-columns:1fr;}}
@endsection

@section('content')

{{-- ═══════════════════════════════ HERO ════════════════════════════════ --}}
<section class="hero">
    @if($heroBgType === 'video' && $site->hero_bg_video_url)
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
                <a href="{{ route('website.apply') }}" class="btn btn-primary">
                    <i class="fas fa-graduation-cap"></i> {{ $site->hero_cta_primary_text ?? 'Apply Now' }}
                </a>
                <a href="{{ route('website.about') }}" class="btn btn-outline">
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

        {{-- Right panel — split layout only --}}
        <div class="hero-panel">
            <div class="hero-card">
                <div class="hero-card-label">{{ $site->hero_panel_title ?? 'World-Class Education' }}</div>
                <div class="hero-card-value">{{ $site->hero_panel_subtitle ?? 'Holistic development for every child' }}</div>
            </div>
            <div class="hero-grid-2">
                <div class="hero-mini"><div class="hero-mini-val">98%</div><div class="hero-mini-label">Satisfaction</div></div>
                <div class="hero-mini"><div class="hero-mini-val">5:1</div><div class="hero-mini-label">Teacher Ratio</div></div>
                <div class="hero-mini"><div class="hero-mini-val">24/7</div><div class="hero-mini-label">Support</div></div>
                <div class="hero-mini"><div class="hero-mini-val">Safe</div><div class="hero-mini-label">Environment</div></div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════ WHY CHOOSE US ═══════════════════════════ --}}
@php
$whyFeatures = $site->why_features ?? [
    ['icon'=>'fa-heart','title'=>'Child-Centred Care','desc'=>'Every decision we make puts your child\'s wellbeing and happiness first.'],
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
        <p>{{ $f['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ═══════════════════════════════ PROGRAMS ════════════════════════════ --}}
{{-- Programs section removed from home page — programs are shown on /academics --}}

{{-- ══════════════════════════════ TESTIMONIALS ═════════════════════════ --}}
@if($testimonials->count())
<section class="section testi-section">
  <div class="container">
    <div class="section-header reveal" style="color:#fff;">
      <span class="eyebrow" style="background:rgba(233,164,34,.15);border-color:rgba(233,164,34,.3);color:#e9a422;">{{ $site->testimonials_eyebrow ?? 'Parent Reviews' }}</span>
      <h2 style="color:#fff;">{{ $site->testimonials_title ?? 'What Parents Say' }}</h2>
      <p style="color:rgba(255,255,255,.6);">{{ $site->testimonials_subtitle ?? 'Real stories from our school community.' }}</p>
    </div>
    <div class="testi-grid">
      @foreach($testimonials as $t)
      <div class="testi-card reveal">
        <div class="testi-stars">{{ str_repeat('★', $t->rating) }}{{ str_repeat('☆', 5 - $t->rating) }}</div>
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

{{-- ════════════════════════════════ BLOG ══════════════════════════════ --}}
@if($posts->count())
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->blog_eyebrow ?? 'Latest News' }}</span>
      <h2>{{ $site->blog_title ?? 'From Our Blog' }}</h2>
      <p>{{ $site->blog_subtitle ?? 'Stay up to date with school news and events.' }}</p>
    </div>
    <div class="blog-grid">
      @foreach($posts as $post)
      <article class="blog-card reveal">
        <div class="blog-thumb">
          @if($post->featured_image_path)
            <img src="{{ Storage::disk('public')->url($post->featured_image_path) }}" alt="{{ $post->title }}">
          @else
            📰
          @endif
        </div>
        <div class="blog-body">
          <div class="blog-date">{{ $post->created_at->format('M d, Y') }}</div>
          <h3>{{ $post->title }}</h3>
          <p>{{ Str::limit($post->excerpt ?? $post->content, 100) }}</p>
          <a href="{{ route('website.blog.detail', $post->slug) }}" class="blog-link">Read more <i class="fas fa-arrow-right"></i></a>
        </div>
      </article>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ════════════════════════════════ EVENTS ════════════════════════════ --}}
@if($events->count())
<section class="section" style="background:var(--off-white);">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->events_eyebrow ?? 'School Calendar' }}</span>
      <h2>{{ $site->events_title ?? 'Upcoming Events' }}</h2>
      <p>{{ $site->events_subtitle ?? 'Mark your calendar for these exciting school events.' }}</p>
    </div>
    <div class="blog-grid">
      @foreach($events as $event)
      <div class="blog-card reveal">
        <div class="blog-thumb" style="background:linear-gradient(135deg,#0a1f44,#1e4db7);">
          @if($event->image_path)
            <img src="{{ Storage::disk('public')->url($event->image_path) }}" alt="{{ $event->title }}">
          @else
            📅
          @endif
        </div>
        <div class="blog-body">
          <div class="blog-date">{{ \Carbon\Carbon::parse($event->date)->format('M d, Y') }}{{ $event->time ? ' · ' . $event->time : '' }}</div>
          <h3>{{ $event->title }}</h3>
          <p>{{ Str::limit($event->description, 100) }}</p>
          @if($event->location)<p style="font-size:.8rem;color:var(--gold-dark);"><i class="fas fa-map-marker-alt"></i> {{ $event->location }}</p>@endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- ═════════════════════════════════ CTA ══════════════════════════════ --}}
<section class="cta-banner">
  <div class="container cta-banner-inner">
    <h2>{{ $site->cta_title ?? 'Ready to Join Our Family?' }}</h2>
    <p>{{ $site->cta_subtitle ?? 'Applications are open. Spots are limited — secure your child\'s place today.' }}</p>
    <div class="cta-banner-btns">
      <a href="{{ route('website.apply') }}" class="btn btn-navy btn-lg">
        <i class="fas fa-file-alt"></i> Apply Now
      </a>
      <a href="{{ route('website.contact') }}" class="btn btn-outline-navy btn-lg">
        <i class="fas fa-envelope"></i> Contact Us
      </a>
    </div>
  </div>
</section>

@endsection
