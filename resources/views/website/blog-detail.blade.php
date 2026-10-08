@extends('website.layout')
@section('page_title', $post->title)
@section('meta_description', $post->auto_excerpt)

@section('extra_css')
.article-wrap{display:grid;grid-template-columns:1fr 320px;gap:52px;align-items:start;}
.article-meta{display:flex;gap:18px;flex-wrap:wrap;font-size:.78rem;color:var(--gray-400);margin-bottom:24px;}
.article-meta span{display:flex;align-items:center;gap:5px;}
.article-meta i{color:var(--gold-dark);}
.article-featured{border-radius:var(--r-xl);overflow:hidden;margin-bottom:36px;min-height:300px;background:linear-gradient(135deg,var(--navy-light),var(--teal));display:flex;align-items:center;justify-content:center;font-size:5rem;}
.article-featured img{width:100%;height:100%;object-fit:cover;}
.article-body{color:var(--gray-700);font-size:.97rem;line-height:1.92;}
.article-body p{margin-bottom:20px;}
.article-body h2{font-size:1.4rem;font-weight:800;color:var(--navy);margin:36px 0 14px;}
.article-body h3{font-size:1.15rem;font-weight:800;color:var(--navy);margin:28px 0 10px;}
.article-body blockquote{border-left:3px solid var(--gold);background:var(--off-white);padding:18px 22px;border-radius:0 var(--r) var(--r) 0;margin:24px 0;font-style:italic;color:var(--navy);}
.article-footer{margin-top:44px;padding-top:28px;border-top:1px solid var(--gray-200);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;}
.share-row{display:flex;gap:8px;}
.share-btn{width:36px;height:36px;border-radius:10px;border:1.5px solid var(--gray-200);display:flex;align-items:center;justify-content:center;color:var(--gray-400);font-size:.85rem;transition:var(--trans);}
.share-btn:hover{background:var(--navy);color:var(--white);border-color:var(--navy);}
/* Sidebar */
.sb-block{background:var(--off-white);border:1px solid var(--gray-200);border-radius:var(--r-xl);padding:26px;margin-bottom:20px;}
.sb-block h4{font-size:.82rem;font-weight:800;color:var(--navy);text-transform:uppercase;letter-spacing:.08em;margin-bottom:18px;padding-bottom:10px;border-bottom:1.5px solid var(--gray-200);}
.rp-item{display:flex;gap:12px;padding:11px 0;border-bottom:1px solid var(--gray-200);align-items:flex-start;}
.rp-item:last-child{border-bottom:none;}
.rp-thumb{width:52px;height:52px;border-radius:10px;flex-shrink:0;background:linear-gradient(135deg,var(--navy-light),var(--teal));display:flex;align-items:center;justify-content:center;font-size:1.2rem;overflow:hidden;}
.rp-thumb img{width:100%;height:100%;object-fit:cover;}
.rp-title{font-size:.84rem;font-weight:700;color:var(--navy);line-height:1.4;transition:var(--trans);}
.rp-title:hover{color:var(--gold-dark);}
.rp-date{font-size:.72rem;color:var(--gray-400);margin-top:3px;}
.sb-cta{background:linear-gradient(160deg,var(--navy),#162d60);border-radius:var(--r-xl);padding:26px;margin-bottom:20px;}
.sb-cta h4{font-size:.88rem;font-weight:800;color:var(--white);margin-bottom:10px;}
.sb-cta p{font-size:.83rem;color:rgba(255,255,255,.6);margin-bottom:18px;line-height:1.65;}
@media(max-width:1024px){.article-wrap{grid-template-columns:1fr;}}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1 style="font-size:clamp(1.55rem,3vw,2.2rem);max-width:680px;margin:0 auto 14px;">{{ $post->title }}</h1>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i>
      <a href="{{ route('website.blog') }}">Blog</a><i class="fas fa-chevron-right"></i>
      <span>Article</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="article-wrap">
      <article class="reveal">
        <div class="article-meta">
          <span><i class="fas fa-calendar-alt"></i> {{ $post->created_at->format('F j, Y') }}</span>
          <span><i class="fas fa-clock"></i> {{ $post->updated_at->diffForHumans() }}</span>
        </div>
        @if($post->featured_image_url)
          <div class="article-featured"><img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}"></div>
        @else
          <div class="article-featured">📰</div>
        @endif
        <div class="article-body">{!! nl2br(e($post->content)) !!}</div>
        <div class="article-footer">
          <a href="{{ route('website.blog') }}" class="btn btn-ghost btn-sm"><i class="fas fa-arrow-left"></i> Back to Blog</a>
          <div>
            <p style="font-size:.72rem;color:var(--gray-400);margin-bottom:7px;text-transform:uppercase;letter-spacing:.08em;font-weight:700;">Share</p>
            <div class="share-row">
              <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" class="share-btn" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
              <a href="https://twitter.com/intent/tweet?url={{ urlencode(request()->url()) }}&text={{ urlencode($post->title) }}" class="share-btn" target="_blank" rel="noopener"><i class="fab fa-twitter"></i></a>
              <a href="mailto:?subject={{ urlencode($post->title) }}&body={{ urlencode(request()->url()) }}" class="share-btn"><i class="fas fa-envelope"></i></a>
            </div>
          </div>
        </div>
      </article>

      <aside class="reveal reveal-delay-1">
        @if($recentPosts->count())
        <div class="sb-block">
          <h4><i class="fas fa-clock" style="color:var(--gold);margin-right:7px;"></i>Recent Posts</h4>
          @foreach($recentPosts as $rp)
          <div class="rp-item">
            <div class="rp-thumb">@if($rp->featured_image_url)<img src="{{ $rp->featured_image_url }}" alt="">@else📰@endif</div>
            <div>
              <a href="{{ route('website.blog.detail', $rp->slug) }}" class="rp-title">{{ $rp->title }}</a>
              <div class="rp-date">{{ $rp->created_at->format('M j, Y') }}</div>
            </div>
          </div>
          @endforeach
        </div>
        @endif
        <div class="sb-cta">
          <h4><i class="fas fa-star" style="color:var(--gold);margin-right:7px;"></i>Ready to Enroll?</h4>
          <p>Open slots available for this school year. Apply online today!</p>
          <a href="{{ route('website.apply') }}" class="btn btn-primary" style="width:100%;justify-content:center;font-size:.85rem;"><i class="fas fa-file-alt"></i> Apply Now</a>
        </div>
        <div class="sb-block">
          <h4><i class="fas fa-phone" style="color:var(--gold);margin-right:7px;"></i>Get in Touch</h4>
          <p style="font-size:.86rem;color:var(--gray-500);margin-bottom:14px;line-height:1.65;">Have questions? Our team is happy to help.</p>
          <a href="{{ route('website.contact') }}" class="btn btn-navy" style="width:100%;justify-content:center;font-size:.85rem;"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
