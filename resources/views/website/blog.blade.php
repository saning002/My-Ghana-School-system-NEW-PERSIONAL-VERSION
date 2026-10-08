@extends('website.layout')
@section('page_title','Blog')
@section('meta_description','News and insights from '.($site->site_name ?? 'our school').'.')

@section('extra_css')
.blog-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(310px,1fr));gap:24px;}
.blog-card{border-radius:var(--r-xl);overflow:hidden;background:var(--white);border:1px solid var(--gray-200);transition:var(--trans);}
.blog-card:hover{transform:translateY(-5px);box-shadow:var(--shadow-lg);border-color:transparent;}
.blog-thumb{height:200px;overflow:hidden;background:linear-gradient(135deg,var(--navy-light),var(--teal));display:flex;align-items:center;justify-content:center;font-size:2.8rem;}
.blog-thumb img{width:100%;height:100%;object-fit:cover;transition:transform .45s var(--ease);}
.blog-card:hover .blog-thumb img{transform:scale(1.05);}
.blog-body{padding:22px 24px 24px;}
.blog-date{font-size:.72rem;font-weight:700;color:var(--gold-dark);text-transform:uppercase;letter-spacing:.1em;margin-bottom:9px;}
.blog-body h3{font-size:1.02rem;font-weight:800;color:var(--navy);margin-bottom:9px;line-height:1.45;}
.blog-body p{font-size:.88rem;color:var(--gray-500);line-height:1.75;margin-bottom:16px;}
.blog-link{font-size:.82rem;font-weight:700;color:var(--gold-dark);display:inline-flex;align-items:center;gap:6px;transition:var(--trans);}
.blog-link:hover{gap:10px;}
.empty-state{text-align:center;padding:80px 0;color:var(--gray-400);}
.empty-state i{font-size:3rem;display:block;margin-bottom:14px;}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>School <em>Blog</em></h1>
    <p>News, updates and insights from our school community.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Blog</span>
    </div>
  </div>
</div>

<section class="section" style="background:var(--off-white);">
  <div class="container">
    @if($posts->count())
    <div class="blog-grid">
      @foreach($posts as $post)
      <div class="blog-card reveal" style="transition-delay:{{ min($loop->index * 0.08, 0.4) }}s;">
        <div class="blog-thumb">
          @if($post->featured_image_url)<img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}">@else📰@endif
        </div>
        <div class="blog-body">
          <div class="blog-date"><i class="fas fa-calendar-alt"></i> {{ $post->created_at->format('M j, Y') }}</div>
          <h3>{{ $post->title }}</h3>
          <p>{{ $post->auto_excerpt }}</p>
          <a href="{{ route('website.blog.detail', $post->slug) }}" class="blog-link">Read More <i class="fas fa-arrow-right"></i></a>
        </div>
      </div>
      @endforeach
    </div>
    <div style="margin-top:44px;">{{ $posts->links() }}</div>
    @else
    <div class="empty-state reveal">
      <i class="fas fa-newspaper"></i>
      <p>No posts yet. Check back soon!</p>
    </div>
    @endif
  </div>
</section>
@endsection
