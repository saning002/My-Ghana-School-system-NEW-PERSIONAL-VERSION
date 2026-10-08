@extends('website.layout')
@section('page_title','Admissions')
@section('meta_description','How to apply to '.($site->site_name ?? 'our school').' — requirements, process and enrollment information.')

@section('extra_css')
.steps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:72px;position:relative;}
.steps-grid::before{content:'';position:absolute;top:32px;left:calc(12.5% + 16px);right:calc(12.5% + 16px);height:2px;background:linear-gradient(90deg,var(--gold),rgba(233,164,34,.15));z-index:0;}
.step-card{background:var(--white);border-radius:var(--r-xl);padding:28px 22px 26px;text-align:center;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);position:relative;z-index:1;transition:var(--trans);}
.step-card:hover{transform:translateY(-4px);box-shadow:var(--shadow-lg);border-color:transparent;}
.step-num{width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-dark));display:flex;align-items:center;justify-content:center;font-size:1.15rem;font-weight:800;color:var(--navy);margin:0 auto 16px;box-shadow:0 4px 14px rgba(233,164,34,.35);}
.step-card h3{font-size:.95rem;font-weight:800;color:var(--navy);margin-bottom:8px;}
.step-card p{font-size:.84rem;color:var(--gray-500);line-height:1.7;}
.info-cols{display:grid;grid-template-columns:1fr 1fr;gap:40px;align-items:start;}
.req-list{display:flex;flex-direction:column;gap:10px;margin-top:18px;}
.req-item{display:flex;align-items:center;gap:12px;padding:12px 16px;background:var(--off-white);border-radius:var(--r);border:1px solid var(--gray-200);}
.req-item i{color:var(--green);flex-shrink:0;font-size:.85rem;}
.req-item span{font-size:.9rem;color:var(--gray-700);}
.info-box{background:var(--white);border-radius:var(--r-xl);padding:36px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);}
.info-box h3{font-size:1.05rem;font-weight:800;color:var(--navy);margin-bottom:18px;}
.info-row{display:flex;align-items:center;gap:12px;margin-bottom:14px;}
.info-row i{color:var(--gold);width:16px;flex-shrink:0;font-size:.9rem;}
.info-row span{font-size:.92rem;color:var(--gray-600);}
@media(max-width:900px){.steps-grid{grid-template-columns:1fr 1fr;}.steps-grid::before{display:none;}.info-cols{grid-template-columns:1fr;}}
@media(max-width:500px){
  .steps-grid{grid-template-columns:1fr;}
  .step-card{padding:22px 18px;}
  .info-box{padding:24px 18px;}
}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1><em>Admissions</em> &amp; Enrollment</h1>
    <p>Begin your child's journey with us. We welcome applications year-round.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Admissions</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="section-header reveal">
      <span class="eyebrow">{{ $site->admissions_steps_eyebrow ?? 'Simple Process' }}</span>
      <h2>{{ $site->admissions_title ?? 'How to Apply in 4 Steps' }}</h2>
      <p>{{ $site->admissions_subtitle ?? 'Enrolling your child is quick and easy. Follow these steps to get started.' }}</p>
    </div>
    <div class="steps-grid">
      @php
      $steps = $site->admissions_steps ?? [
        ['num'=>'1','title'=>'Fill Application', 'desc'=>'Complete our online form with your child\'s and parent\'s details.'],
        ['num'=>'2','title'=>'Submit Documents',  'desc'=>'Provide the required documents listed below.'],
        ['num'=>'3','title'=>'Orientation',       'desc'=>'Your child attends a brief, friendly orientation session.'],
        ['num'=>'4','title'=>'Confirmation',      'desc'=>'Receive your official enrollment confirmation and welcome pack.'],
      ];
      @endphp
      @foreach($steps as $step)
      <div class="step-card reveal" style="transition-delay:{{ $loop->index * 0.1 }}s;">
        <div class="step-num">{{ $step['num'] }}</div>
        <h3>{{ $step['title'] }}</h3>
        <p>{{ $step['desc'] }}</p>
      </div>
      @endforeach
    </div>

    <div class="info-cols reveal">
      <div>
        <span class="eyebrow">Documents Needed</span>
        <h2 style="font-size:1.5rem;color:var(--navy);margin:10px 0 6px;">Requirements</h2>
        <p style="font-size:.92rem;color:var(--gray-500);margin-bottom:4px;">Please bring the following when applying:</p>
        <div class="req-list">
          @php
          $reqs = $site->admissions_requirements ?? [
            'Birth certificate','Immunisation records','2 passport photos',
            'Parent / guardian ID','Previous school records (if any)','Completed application form',
          ];
          @endphp
          @foreach($reqs as $req)
          <div class="req-item"><i class="fas fa-check-circle"></i><span>{{ $req }}</span></div>
          @endforeach
        </div>
      </div>
      <div class="info-box">
        @if($site->admissions_info)
          <h3>Additional Information</h3>
          <div style="color:var(--gray-600);line-height:1.9;font-size:.93rem;">{!! nl2br(e($site->admissions_info)) !!}</div>
        @else
          <h3>School Information</h3>
          <div class="info-row"><i class="fas fa-clock"></i><span>Monday – Friday: 7:00 AM – 5:00 PM</span></div>
          <div class="info-row"><i class="fas fa-calendar"></i><span>Applications open year-round</span></div>
          <div class="info-row"><i class="fas fa-info-circle"></i><span>Limited spots available per term</span></div>
          <div class="info-row"><i class="fas fa-phone"></i><span>{{ $site->contact_phone ?? 'Contact us for more info' }}</span></div>
        @endif
        <a href="{{ route('website.apply') }}" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:24px;">
          <i class="fas fa-file-alt"></i> Apply Online Now
        </a>
      </div>
    </div>
  </div>
</section>
@endsection
