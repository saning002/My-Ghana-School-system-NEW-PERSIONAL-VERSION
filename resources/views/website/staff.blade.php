@extends('website.layout')
@section('page_title','Our Team')
@section('meta_description','Meet the educators and staff of '.($site->site_name ?? 'our school').'.')

@section('extra_css')
.staff-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:22px;padding:80px 0 96px;}
.staff-card{background:var(--white);border-radius:var(--r-xl);overflow:hidden;border:1px solid var(--gray-200);text-align:center;transition:var(--trans);}
.staff-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-lg);border-color:transparent;}
.staff-banner{height:72px;background:linear-gradient(135deg,var(--navy),var(--navy-mid));}
.staff-photo{width:84px;height:84px;border-radius:50%;border:3px solid var(--white);margin:-42px auto 14px;overflow:hidden;background:var(--gray-100);display:flex;align-items:center;justify-content:center;font-size:1.7rem;font-weight:800;color:var(--navy-mid);box-shadow:var(--shadow-sm);}
.staff-photo img{width:100%;height:100%;object-fit:cover;}
.staff-body{padding:0 20px 24px;}
.staff-name{font-size:1rem;font-weight:800;color:var(--navy);margin-bottom:3px;}
.staff-pos{font-size:.7rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
.staff-bio{font-size:.84rem;color:var(--gray-500);line-height:1.68;margin-bottom:12px;}
.staff-email{display:inline-flex;align-items:center;gap:5px;font-size:.78rem;color:var(--navy-mid);font-weight:600;transition:var(--trans);}
.staff-email:hover{color:var(--gold-dark);}
.empty-state{text-align:center;padding:80px 0;color:var(--gray-400);}
.empty-state i{font-size:3rem;display:block;margin-bottom:14px;}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Our <em>Dedicated Team</em></h1>
    <p>Passionate educators and professionals committed to every child's success.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Our Team</span>
    </div>
  </div>
</div>

<div class="container">
  @if($staff->count())
  <div class="staff-grid">
    @foreach($staff as $member)
    <div class="staff-card reveal" style="transition-delay:{{ min($loop->index * 0.07, 0.5) }}s;">
      <div class="staff-banner"></div>
      <div class="staff-photo">
        @if($member->photo_url)<img src="{{ $member->photo_url }}" alt="{{ $member->name }}">
        @else{{ strtoupper(substr($member->name,0,1)) }}@endif
      </div>
      <div class="staff-body">
        <div class="staff-name">{{ $member->name }}</div>
        <div class="staff-pos">{{ $member->position }}</div>
        @if($member->bio)<p class="staff-bio">{{ Str::limit($member->bio, 110) }}</p>@endif
        @if($member->email)<a href="mailto:{{ $member->email }}" class="staff-email"><i class="fas fa-envelope"></i>{{ $member->email }}</a>@endif
      </div>
    </div>
    @endforeach
  </div>
  @else
  <div class="empty-state reveal">
    <i class="fas fa-users"></i>
    <p>Staff profiles coming soon.</p>
  </div>
  @endif
</div>
@endsection
