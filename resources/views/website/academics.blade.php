@extends('website.layout')
@section('page_title','Academics')
@section('meta_description','Our academic programs at '.($site->site_name ?? 'our school').' — comprehensive programs designed for excellence.')
@section('extra_css')
.prog-list{display:flex;flex-direction:column;gap:32px;max-width:960px;margin:0 auto;}
.prog-row{display:grid;grid-template-columns:220px 1fr;border-radius:var(--r-xl);overflow:hidden;background:var(--white);box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);transition:var(--trans);}
.prog-row:hover{box-shadow:var(--shadow-lg);border-color:transparent;transform:translateY(-3px);}
.prog-sidebar{background:linear-gradient(160deg,var(--navy) 0%,#162d60 100%);padding:36px 28px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;}
.prog-sidebar .prog-img{width:100%;height:120px;object-fit:cover;border-radius:12px;margin-bottom:14px;}
.prog-emoji{font-size:2.8rem;margin-bottom:12px;}
.prog-sidebar-name{font-size:1.1rem;font-weight:800;color:var(--white);margin-bottom:6px;}
.prog-sidebar-age{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:100px;background:rgba(233,164,34,.2);color:var(--gold);font-size:.72rem;font-weight:700;margin-bottom:18px;}
.prog-sidebar .btn{font-size:.8rem;padding:9px 20px;}
.prog-content{padding:32px 36px;}
.prog-content h3{font-size:1.25rem;font-weight:800;color:var(--navy);margin-bottom:10px;}
.prog-content>p{color:var(--gray-500);line-height:1.82;font-size:.95rem;margin-bottom:18px;}
.prog-section-title{font-size:.72rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.1em;margin-bottom:7px;display:flex;align-items:center;gap:6px;}
.prog-detail-text{font-size:.9rem;color:var(--gray-600);line-height:1.75;margin-bottom:16px;}
.prog-features{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:16px;}
.prog-feature-tag{display:inline-flex;align-items:center;gap:5px;padding:4px 10px;background:#f0f4ff;color:#3b4f8a;border-radius:100px;font-size:.75rem;font-weight:600;}
.prog-feature-tag::before{content:'✓';color:#2e7d32;font-weight:700;}
.prog-empty{text-align:center;padding:80px 0;color:var(--gray-400);}
.prog-empty i{font-size:3rem;display:block;margin-bottom:16px;}
.admin-note{background:#fef9c3;border:1px solid #fde68a;border-radius:12px;padding:16px 20px;margin-bottom:32px;display:flex;align-items:center;gap:12px;font-size:.9rem;color:#78520a;}
@media(max-width:700px){
  .prog-row{grid-template-columns:1fr;}
  .prog-sidebar{padding:22px 18px;flex-direction:row;justify-content:flex-start;gap:16px;text-align:left;}
  .prog-sidebar .prog-img{width:56px;height:56px;margin:0;flex-shrink:0;}
  .prog-sidebar .btn{font-size:.75rem;padding:7px 14px;}
  .prog-content{padding:18px;}
  .prog-content h3{font-size:1.05rem;}
}
@media(max-width:480px){
  .prog-sidebar{flex-direction:column;text-align:center;align-items:center;}
  .prog-sidebar .prog-img{width:72px;height:72px;}
}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>{!! $site->academics_hero_title ?? 'Our <em>Academic Programs</em>' !!}</h1>
    <p>{{ $site->academics_hero_subtitle ?? 'Carefully designed programs rooted in academic excellence and Kingdom values.' }}</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Academics</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">

    @if($programs->count())
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->academics_eyebrow ?? 'What We Offer' }}</span>
      <h2>{{ $site->academics_section_title ?? 'Programs for Every Learner' }}</h2>
      <p>{{ $site->academics_section_subtitle ?? 'Every program is thoughtfully designed to develop knowledge, character, and skills.' }}</p>
    </div>

    <div class="prog-list">
      @foreach($programs as $prog)
      <div class="prog-row reveal" style="transition-delay:{{ $loop->index * 0.08 }}s;">

        {{-- Sidebar --}}
        <div class="prog-sidebar">
          @if(!empty($prog->image_path))
            <img src="{{ filter_var($prog->image_path, FILTER_VALIDATE_URL) ? $prog->image_path : Storage::url($prog->image_path) }}"
                 alt="{{ $prog->name }}" class="prog-img">
          @else
            <div class="prog-emoji">{{ $prog->icon ?? '🎓' }}</div>
          @endif
          <div class="prog-sidebar-name">{{ $prog->name }}</div>
          @if(!empty($prog->age_range) && $prog->age_range !== 'All Ages')
          <div class="prog-sidebar-age"><i class="fas fa-child"></i> {{ $prog->age_range }}</div>
          @endif
          <a href="{{ route('website.apply') }}" class="btn btn-primary">{{ $site->academics_apply_btn ?? 'Apply Now' }}</a>
        </div>

        {{-- Content --}}
        <div class="prog-content">
          <h3>{{ $prog->name }}</h3>
          <p>{{ $prog->description }}</p>

          {{-- Feature tags --}}
          @if(!empty($prog->features) && is_array($prog->features))
          <div class="prog-features">
            @foreach($prog->features as $feat)
            <span class="prog-feature-tag">{{ $feat }}</span>
            @endforeach
          </div>
          @endif

          @if(!empty($prog->curriculum))
            <div class="prog-section-title"><i class="fas fa-book-open"></i> {{ $site->academics_curriculum_label ?? 'Curriculum Overview' }}</div>
            <p class="prog-detail-text">{{ $prog->curriculum }}</p>
          @endif

          @if(!empty($prog->highlights))
            <div class="prog-section-title"><i class="fas fa-star"></i> {{ $site->academics_highlights_label ?? 'Program Highlights' }}</div>
            <p class="prog-detail-text">{{ $prog->highlights }}</p>
          @endif

          @if(!empty($prog->schedule))
            <div class="prog-section-title"><i class="fas fa-clock"></i> {{ $site->academics_schedule_label ?? 'Schedule' }}</div>
            <p class="prog-detail-text">{{ $prog->schedule }}</p>
          @endif
        </div>

      </div>
      @endforeach
    </div>

    @else
    {{-- Empty state --}}
    <div class="prog-empty reveal">
      <i class="fas fa-graduation-cap"></i>
      <p style="font-size:1.05rem;margin-bottom:20px;">
        {{ $site->academics_empty_text ?? 'Our programs are being updated. Check back soon!' }}
      </p>
      <a href="{{ route('website.contact') }}" class="btn btn-navy">
        {{ $site->academics_contact_btn ?? 'Contact Us' }}
      </a>
    </div>
    @endif

    {{-- CTA strip --}}
    @if($programs->count())
    <div style="margin-top:60px;text-align:center;" class="reveal">
      <p style="font-size:1.05rem;color:var(--gray-500);margin-bottom:20px;">
        {{ $site->academics_cta_text ?? 'Ready to enroll your child? Spaces fill up fast.' }}
      </p>
      <div style="display:flex;gap:14px;justify-content:center;flex-wrap:wrap;">
        <a href="{{ route('website.apply') }}" class="btn btn-primary">{{ $site->academics_cta_btn1_text ?? 'Apply Now' }}</a>
        <a href="{{ route('website.admissions') }}" class="btn btn-outline">{{ $site->academics_cta_btn2_text ?? 'Admissions Info' }}</a>
        <a href="{{ route('website.contact') }}" class="btn btn-navy">{{ $site->academics_cta_btn3_text ?? 'Contact Us' }}</a>
      </div>
    </div>
    @endif

  </div>
</section>
@endsection
