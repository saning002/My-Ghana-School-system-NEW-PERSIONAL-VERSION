@extends('website.layout')
@section('page_title','Events')
@section('meta_description','Events and news from '.($site->site_name ?? 'our school').'.')

@section('extra_css')
.events-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:22px;}
.event-card{background:var(--white);border-radius:var(--r-xl);overflow:hidden;border:1px solid var(--gray-200);transition:var(--trans);}
.event-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);border-color:transparent;}
.event-thumb{height:176px;background:linear-gradient(135deg,var(--navy-light),var(--teal));display:flex;align-items:center;justify-content:center;font-size:2.8rem;overflow:hidden;position:relative;}
.event-thumb img{width:100%;height:100%;object-fit:cover;}
.event-thumb-date{position:absolute;top:14px;left:14px;background:var(--white);border-radius:10px;padding:6px 10px;text-align:center;box-shadow:var(--shadow-sm);}
.event-thumb-date .day{font-size:1.3rem;font-weight:800;color:var(--navy);line-height:1;}
.event-thumb-date .mon{font-size:.65rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.06em;}
.event-body{padding:20px 22px 22px;}
.event-body h3{font-size:1rem;font-weight:800;color:var(--navy);margin-bottom:8px;line-height:1.4;}
.event-body p{font-size:.88rem;color:var(--gray-500);line-height:1.72;margin-bottom:12px;}
.event-meta{display:flex;gap:14px;flex-wrap:wrap;}
.event-meta span{font-size:.75rem;color:var(--gray-400);display:flex;align-items:center;gap:5px;}
.section-label{display:flex;align-items:center;gap:14px;margin:56px 0 28px;}
.section-label h2{font-size:1.2rem;font-weight:800;color:var(--navy);white-space:nowrap;}
.section-label::after{content:'';flex:1;height:1px;background:var(--gray-200);}
.past-card{opacity:.7;filter:saturate(.6);}
.past-card:hover{opacity:1;filter:saturate(1);}
.empty-state{text-align:center;padding:80px 0;color:var(--gray-400);}
.empty-state i{font-size:3rem;display:block;margin-bottom:14px;}
@media(max-width:600px){
  .events-grid{grid-template-columns:1fr;}
  .event-body{padding:16px 18px 18px;}
  .event-body h3{font-size:.95rem;}
  .event-thumb{height:150px;}
  .section-label h2{font-size:1.05rem;}
}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Events &amp; <em>News</em></h1>
    <p>Stay up to date with everything happening at {{ $site->site_name ?? 'our school' }}.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Events</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    @if($upcoming->count())
    <div class="section-label reveal"><h2>Upcoming Events</h2></div>
    <div class="events-grid">
      @foreach($upcoming as $event)
      <div class="event-card reveal" style="transition-delay:{{ $loop->index * 0.08 }}s;">
        <div class="event-thumb">
          @if($event->image_url)<img src="{{ $event->image_url }}" alt="{{ $event->title }}">@else🎉@endif
          <div class="event-thumb-date">
            <div class="day">{{ $event->date->format('d') }}</div>
            <div class="mon">{{ $event->date->format('M') }}</div>
          </div>
        </div>
        <div class="event-body">
          <h3>{{ $event->title }}</h3>
          <p>{{ Str::limit($event->description, 100) }}</p>
          <div class="event-meta">
            @if($event->time)<span><i class="fas fa-clock"></i>{{ \Carbon\Carbon::parse($event->time)->format('g:i A') }}</span>@endif
            @if($event->location)<span><i class="fas fa-map-marker-alt"></i>{{ $event->location }}</span>@endif
          </div>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    @if($past->count())
    <div class="section-label reveal" style="margin-top:72px;"><h2>Past Events</h2></div>
    <div class="events-grid">
      @foreach($past as $event)
      <div class="event-card past-card reveal" style="transition-delay:{{ $loop->index * 0.06 }}s;">
        <div class="event-thumb">
          @if($event->image_url)<img src="{{ $event->image_url }}" alt="{{ $event->title }}">@else📅@endif
          <div class="event-thumb-date">
            <div class="day">{{ $event->date->format('d') }}</div>
            <div class="mon">{{ $event->date->format('M') }}</div>
          </div>
        </div>
        <div class="event-body">
          <h3>{{ $event->title }}</h3>
          <p>{{ Str::limit($event->description, 100) }}</p>
        </div>
      </div>
      @endforeach
    </div>
    @endif

    @if($upcoming->isEmpty() && $past->isEmpty())
    <div class="empty-state reveal">
      <i class="fas fa-calendar-times"></i>
      <p>No events at the moment. Check back soon!</p>
    </div>
    @endif
  </div>
</section>
@endsection
