@extends('website.layout')
@section('page_title','About Us')
@section('meta_description','Learn about '.($site->site_name ?? 'our school').' — our mission, vision, history and dedicated team.')

@section('extra_css')
/* Mission/Vision */
.mv-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:80px;}
.mv-card{background:var(--white);border-radius:var(--r-xl);padding:40px 36px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);position:relative;overflow:hidden;}
.mv-card::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,var(--gold),var(--gold-dark));}
.mv-icon{width:52px;height:52px;border-radius:14px;background:var(--gold-pale);display:flex;align-items:center;justify-content:center;font-size:1.4rem;margin-bottom:20px;}
.mv-card h3{font-size:1.15rem;font-weight:800;color:var(--navy);margin-bottom:12px;}
.mv-card p{color:var(--gray-500);line-height:1.82;font-size:.95rem;}
/* Story */
.story-layout{display:grid;grid-template-columns:340px 1fr;gap:64px;align-items:start;}
.timeline{position:relative;padding-left:28px;}
.timeline::before{content:'';position:absolute;left:6px;top:4px;bottom:4px;width:2px;background:linear-gradient(to bottom,var(--gold),rgba(233,164,34,.1));}
.tl-item{position:relative;margin-bottom:32px;}
.tl-dot{position:absolute;left:-25px;top:5px;width:12px;height:12px;border-radius:50%;background:var(--gold);box-shadow:0 0 0 4px rgba(233,164,34,.2);}
.tl-year{font-size:.72rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px;}
.tl-text{font-size:.9rem;color:var(--gray-600);line-height:1.7;}
/* Values */
.values-bg{background:var(--off-white);}
.values-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px;}
.value-card{background:var(--white);border-radius:var(--r-lg);padding:28px 24px;border:1px solid var(--gray-200);transition:var(--trans);}
.value-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:transparent;}
.value-emoji{font-size:1.8rem;margin-bottom:12px;}
.value-card h4{font-size:.98rem;font-weight:800;color:var(--navy);margin-bottom:8px;}
.value-card p{font-size:.87rem;color:var(--gray-500);line-height:1.7;}
/* Staff */
.staff-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:22px;}
.staff-card{background:var(--white);border-radius:var(--r-xl);overflow:hidden;border:1px solid var(--gray-200);text-align:center;transition:var(--trans);}
.staff-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);border-color:transparent;}
.staff-banner{height:72px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));}
.staff-photo{width:80px;height:80px;border-radius:50%;border:3px solid var(--white);margin:-40px auto 14px;overflow:hidden;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:1.6rem;font-weight:800;color:var(--navy-mid);box-shadow:var(--shadow-sm);}
.staff-photo img{width:100%;height:100%;object-fit:cover;}
.staff-body{padding:0 18px 22px;}
.staff-body h3{font-size:.98rem;font-weight:800;color:var(--navy);margin-bottom:3px;}
.staff-pos{font-size:.72rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.07em;margin-bottom:9px;}
.staff-bio{font-size:.84rem;color:var(--gray-500);line-height:1.65;}
/* CTA */
.about-cta{background:linear-gradient(135deg,var(--gold),var(--gold-dark));padding:80px 0;text-align:center;}
.about-cta h2{font-size:clamp(1.8rem,3.5vw,2.5rem);font-weight:800;color:var(--navy);margin-bottom:12px;}
.about-cta p{color:rgba(10,31,68,.6);margin-bottom:32px;}
@media(max-width:900px){.story-layout{grid-template-columns:1fr;gap:40px;}}
@media(max-width:640px){
  .mv-grid{grid-template-columns:1fr;}
  .mv-card{padding:28px 22px;}
  .staff-grid{grid-template-columns:repeat(2,1fr);}
  .values-grid{grid-template-columns:repeat(2,1fr);}
  .about-cta{padding:52px 0;}
}
@media(max-width:420px){
  .staff-grid{grid-template-columns:1fr;}
  .values-grid{grid-template-columns:1fr;}
}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>About <em>Our School</em></h1>
    <p>{{ $site->tagline ?? 'Nurturing Young Minds for Excellence' }}</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>About Us</span>
    </div>
  </div>
</div>

{{-- Mission & Vision --}}
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->about_eyebrow ?? 'Who We Are' }}</span>
      <h2>{{ $site->about_mv_title ?? 'Our Mission &amp; Vision' }}</h2>
    </div>
    <div class="mv-grid reveal">
      <div class="mv-card">
        <div class="mv-icon">🎯</div>
        <h3>Our Mission</h3>
        <p>{{ $site->mission ?? 'To provide a safe, nurturing, and stimulating learning environment that fosters the holistic development of every child — intellectually, emotionally, socially, and physically — through innovative, play-based education.' }}</p>
      </div>
      <div class="mv-card">
        <div class="mv-icon">🌟</div>
        <h3>Our Vision</h3>
        <p>{{ $site->vision ?? 'To be the leading early childhood institution in the community, recognized for excellence in education, holistic child development, and building the foundation for lifelong learning and leadership.' }}</p>
      </div>
    </div>

    {{-- Story --}}
    <div class="story-layout">
      <div class="reveal">
        <div class="section-header" style="text-align:left;margin-bottom:24px;">
          <span class="eyebrow">History</span>
        </div>
        <div class="timeline">
          @php
          $timeline = $site->about_timeline ?? [
            ['year'=>'Founded',  'text'=>'Started with 30 students and 5 dedicated teachers.'],
            ['year'=>'Expanded', 'text'=>'Added new classrooms and launched additional programs.'],
            ['year'=>'Alumni',   'text'=>'Celebrated a milestone of 500 proud graduates.'],
            ['year'=>'Today',    'text'=>'Serving hundreds of families across all programs.'],
          ];
          @endphp
          @foreach($timeline as $tl)
          <div class="tl-item"><div class="tl-dot"></div><div class="tl-year">{{ $tl['year'] }}</div><div class="tl-text">{{ $tl['text'] }}</div></div>
          @endforeach
        </div>
      </div>
      <div class="reveal reveal-delay-1">
        <div class="section-header" style="text-align:left;margin-bottom:24px;">
          <span class="eyebrow">Our Story</span>
          <h2 style="font-size:clamp(1.6rem,3vw,2.2rem);">{{ $site->about_story_title ?? 'A Legacy of Nurturing Young Minds' }}</h2>
        </div>
        <div style="color:var(--gray-600);line-height:1.92;font-size:.97rem;">
          {!! nl2br(e($site->about_story_text ?? $site->history ?? 'Our school was founded with a simple but powerful belief: every child deserves a joyful, nurturing start to their educational journey. What began as a small nursery has grown into a thriving institution serving hundreds of families.')) !!}
        </div>
        <a href="{{ route('website.apply') }}" class="btn btn-primary" style="margin-top:28px;"><i class="fas fa-file-alt"></i> Join Our Family</a>
      </div>
    </div>
  </div>
</section>

{{-- Core Values --}}
<section class="section values-bg">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">What We Stand For</span>
      <h2>Our Core Values</h2>
      <p>The principles that guide everything we do, every single day.</p>
    </div>
    <div class="values-grid">
      @php
      $coreValues = $site->core_values ?? [
        ['emoji'=>'🌟','title'=>'Excellence','desc'=>'We strive for the highest standards in every aspect of education and school life.'],
        ['emoji'=>'🤝','title'=>'Respect','desc'=>'Every child, parent, and staff member is treated with dignity, empathy, and genuine care.'],
        ['emoji'=>'💡','title'=>'Innovation','desc'=>'We embrace modern, research-based teaching methods that make learning exciting.'],
        ['emoji'=>'🌱','title'=>'Growth','desc'=>'We celebrate every milestone and nurture each child\'s potential at their own pace.'],
        ['emoji'=>'🛡️','title'=>'Safety','desc'=>'The physical and emotional safety of every child is our absolute top priority.'],
        ['emoji'=>'🤗','title'=>'Community','desc'=>'We build strong partnerships with families and the wider community.'],
      ];
      @endphp
      @foreach($coreValues as $v)
      <div class="value-card reveal" style="transition-delay:{{ $loop->index * 0.07 }}s;">
        <div class="value-emoji">{{ $v['emoji'] }}</div>
        <h4>{{ $v['title'] }}</h4>
        <p>{{ $v['desc'] }}</p>
      </div>
      @endforeach
    </div>
  </div>
</section>

{{-- Staff --}}
@if($staff->count())
<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->staff_eyebrow ?? 'Meet the Team' }}</span>
      <h2>{{ $site->staff_title ?? 'Our Dedicated Educators' }}</h2>
      <p>{{ $site->staff_subtitle ?? 'Passionate professionals committed to every child\'s success.' }}</p>
    </div>
    <div class="staff-grid">
      @foreach($staff as $member)
      <div class="staff-card reveal" style="transition-delay:{{ $loop->index * 0.07 }}s;">
        <div class="staff-banner"></div>
        <div class="staff-photo">
          @if($member->photo_url)<img src="{{ $member->photo_url }}" alt="{{ $member->name }}">
          @else{{ strtoupper(substr($member->name,0,1)) }}@endif
        </div>
        <div class="staff-body">
          <h3>{{ $member->name }}</h3>
          <div class="staff-pos">{{ $member->position }}</div>
          @if($member->bio)<p class="staff-bio">{{ Str::limit($member->bio, 90) }}</p>@endif
        </div>
      </div>
      @endforeach
    </div>
  </div>
</section>
@endif

{{-- CTA --}}
<section class="about-cta">
  <div class="container reveal">
    <h2>{{ $site->about_cta_title ?? 'Want to Be Part of Our Story?' }}</h2>
    <p>{{ $site->about_cta_subtitle ?? 'Applications for the new school year are now open. Spots fill up fast!' }}</p>
    <a href="{{ route('website.apply') }}" class="btn btn-navy"><i class="fas fa-file-alt"></i> Apply Now</a>
  </div>
</section>
@endsection
