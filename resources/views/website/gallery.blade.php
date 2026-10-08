@extends('website.layout')
@section('page_title','Gallery')
@section('meta_description','Photo gallery of '.($site->site_name ?? 'our school').' — see life at our school.')

@section('extra_css')
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:36px;}
.filter-btn{padding:7px 18px;border-radius:100px;font-size:.82rem;font-weight:700;border:1.5px solid var(--gray-200);background:var(--white);color:var(--gray-600);cursor:pointer;text-decoration:none;transition:var(--trans);}
.filter-btn:hover{border-color:var(--navy);color:var(--navy);}
.filter-btn.active{background:var(--navy);color:var(--white);border-color:var(--navy);}
.gallery-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(240px,1fr));gap:14px;}
.g-item{border-radius:var(--r-lg);overflow:hidden;aspect-ratio:1;position:relative;cursor:zoom-in;background:var(--gray-100);}
.g-item img{width:100%;height:100%;object-fit:cover;transition:transform .45s var(--ease);}
.g-item:hover img{transform:scale(1.07);}
.g-overlay{position:absolute;inset:0;background:rgba(10,31,68,.55);opacity:0;transition:var(--trans);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:6px;}
.g-item:hover .g-overlay{opacity:1;}
.g-overlay i{color:var(--white);font-size:1.4rem;}
.g-overlay span{color:rgba(255,255,255,.85);font-size:.78rem;font-weight:600;padding:0 12px;text-align:center;}
.lb{position:fixed;inset:0;background:rgba(0,0,0,.93);z-index:9999;display:none;align-items:center;justify-content:center;padding:20px;}
.lb.open{display:flex;}
.lb img{max-width:92vw;max-height:90vh;border-radius:var(--r-lg);object-fit:contain;}
.lb-close{position:absolute;top:18px;right:22px;width:40px;height:40px;border-radius:50%;background:rgba(255,255,255,.12);border:none;color:var(--white);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:var(--trans);}
.lb-close:hover{background:rgba(255,255,255,.25);}
.empty-state{text-align:center;padding:80px 0;color:var(--gray-400);}
.empty-state i{font-size:3rem;display:block;margin-bottom:14px;}
@media(max-width:600px){
  .gallery-grid{grid-template-columns:repeat(2,1fr);gap:8px;}
  .filter-bar{gap:6px;}
  .filter-btn{padding:6px 13px;font-size:.78rem;}
}
@media(max-width:380px){
  .gallery-grid{grid-template-columns:repeat(2,1fr);gap:6px;}
}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Photo <em>Gallery</em></h1>
    <p>A glimpse into life at {{ $site->site_name ?? 'our school' }}.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Gallery</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    @if($categories->count())
    <div class="filter-bar reveal">
      <a href="{{ route('website.gallery') }}" class="filter-btn @if(!request('category')) active @endif">All Photos</a>
      @foreach($categories as $cat)
      <a href="{{ route('website.gallery',['category'=>$cat->slug]) }}" class="filter-btn @if(request('category')===$cat->slug) active @endif">
        {{ $cat->name }} <span style="opacity:.55;">({{ $cat->images_count }})</span>
      </a>
      @endforeach
    </div>
    @endif

    @if($images->count())
    <div class="gallery-grid">
      @foreach($images as $img)
      <div class="g-item reveal" style="transition-delay:{{ min($loop->index * 0.04, 0.5) }}s;"
           onclick="openLb('{{ $img->image_url }}','{{ e($img->title) }}')">
        <img src="{{ $img->image_url }}" alt="{{ $img->title }}" loading="lazy">
        <div class="g-overlay">
          <i class="fas fa-expand-alt"></i>
          <span>{{ $img->title }}</span>
        </div>
      </div>
      @endforeach
    </div>
    <div style="margin-top:40px;">{{ $images->links() }}</div>
    @else
    <div class="empty-state reveal">
      <i class="fas fa-images"></i>
      <p>No photos yet. Check back soon!</p>
    </div>
    @endif
  </div>
</section>

<div class="lb" id="lb" onclick="if(event.target===this)closeLb()">
  <button class="lb-close" onclick="closeLb()"><i class="fas fa-times"></i></button>
  <img id="lbImg" src="" alt="">
</div>
@endsection

@section('extra_js')
<script>
function openLb(s,a){var el=document.getElementById('lb');document.getElementById('lbImg').src=s;document.getElementById('lbImg').alt=a;el.classList.add('open');document.body.style.overflow='hidden';}
function closeLb(){document.getElementById('lb').classList.remove('open');document.body.style.overflow='';}
document.addEventListener('keydown',function(e){if(e.key==='Escape')closeLb();});
</script>
@endsection
