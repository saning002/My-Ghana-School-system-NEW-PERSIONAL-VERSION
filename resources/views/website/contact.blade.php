@extends('website.layout')
@section('page_title','Contact')
@section('meta_description','Contact '.($site->site_name ?? 'our school').' — we\'d love to hear from you.')

@section('extra_css')
.contact-layout{display:grid;grid-template-columns:1fr 1fr;gap:44px;align-items:start;}
.contact-info-panel{background:linear-gradient(160deg,var(--navy) 0%,#162d60 100%);border-radius:var(--r-xl);padding:40px;color:var(--white);position:sticky;top:88px;}
.contact-info-panel h3{font-size:1.2rem;font-weight:800;color:var(--white);margin-bottom:28px;}
.ci-item{display:flex;gap:16px;margin-bottom:22px;align-items:flex-start;}
.ci-icon{width:42px;height:42px;border-radius:12px;background:rgba(233,164,34,.15);border:1px solid rgba(233,164,34,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ci-icon i{color:var(--gold);font-size:.9rem;}
.ci-label{font-size:.68rem;font-weight:700;color:rgba(255,255,255,.38);text-transform:uppercase;letter-spacing:.1em;margin-bottom:3px;}
.ci-value{font-size:.9rem;color:rgba(255,255,255,.88);}
.ci-value a{color:rgba(255,255,255,.88);transition:var(--trans);}
.ci-value a:hover{color:var(--gold);}
.map-area{margin-top:28px;border-radius:var(--r-lg);overflow:hidden;border:1px solid rgba(255,255,255,.08);}
.map-placeholder{height:180px;background:rgba(255,255,255,.04);display:flex;flex-direction:column;align-items:center;justify-content:center;color:rgba(255,255,255,.25);gap:8px;}
.map-placeholder i{font-size:1.8rem;}
.map-placeholder p{font-size:.8rem;}
.contact-form-panel{background:var(--white);border-radius:var(--r-xl);padding:40px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);}
.contact-form-panel h3{font-size:1.15rem;font-weight:800;color:var(--navy);margin-bottom:26px;}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.field{margin-bottom:16px;}
.field label{display:block;font-size:.72rem;font-weight:700;color:var(--gray-700);margin-bottom:6px;text-transform:uppercase;letter-spacing:.07em;}
.field input,.field select,.field textarea{width:100%;padding:11px 15px;border:1.5px solid var(--gray-200);border-radius:var(--r);font-size:.9rem;color:var(--gray-900);font-family:inherit;transition:var(--trans);background:var(--white);}
.field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,164,34,.12);}
.form-err{background:#fef2f2;border-radius:var(--r);padding:12px 16px;margin-bottom:16px;color:#7f1d1d;font-size:.85rem;}
@media(max-width:900px){.contact-layout{grid-template-columns:1fr;}.contact-info-panel{position:static;}.form-row{grid-template-columns:1fr;}}
@media(max-width:480px){
  .contact-info-panel{padding:28px 20px;}
  .contact-form-panel{padding:28px 20px;}
}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Get in <em>Touch</em></h1>
    <p>We'd love to hear from you. Send us a message and we'll get back to you promptly.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a>
      <i class="fas fa-chevron-right"></i><span>Contact</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="contact-layout">
      <div class="contact-info-panel reveal">
        <h3>Find Us</h3>
        @if(isset($site) && $site->address)
        <div class="ci-item"><div class="ci-icon"><i class="fas fa-map-marker-alt"></i></div><div><div class="ci-label">Address</div><div class="ci-value">{{ $site->address }}</div></div></div>
        @endif
        @if(isset($site) && $site->contact_phone)
        <div class="ci-item"><div class="ci-icon"><i class="fas fa-phone"></i></div><div><div class="ci-label">Phone</div><div class="ci-value"><a href="tel:{{ $site->contact_phone }}">{{ $site->contact_phone }}</a></div></div></div>
        @endif
        @if(isset($site) && $site->contact_email)
        <div class="ci-item"><div class="ci-icon"><i class="fas fa-envelope"></i></div><div><div class="ci-label">Email</div><div class="ci-value"><a href="mailto:{{ $site->contact_email }}">{{ $site->contact_email }}</a></div></div></div>
        @endif
        <div class="ci-item"><i class="fas fa-clock"></i></div><div><div class="ci-label">Office Hours</div><div class="ci-value">{{ $site->office_hours ?? 'Mon – Fri: 7:00 AM – 5:00 PM' }}</div></div>
        <div class="map-area">
          @if(isset($site) && $site->map_embed_url)
            {!! $site->map_embed_url !!}
          @else
            <div class="map-placeholder"><i class="fas fa-map"></i><p>Map configured in Site Settings</p></div>
          @endif
        </div>
      </div>

      <div class="contact-form-panel reveal reveal-delay-1">
        <h3>Send Us a Message</h3>
        <form method="POST" action="{{ route('website.contact.submit') }}" novalidate>
          @csrf
          @if($errors->any())
          <div class="form-err">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
          @endif
          <div class="form-row">
            <div class="field"><label>Your Name *</label><input type="text" name="name" value="{{ old('name') }}" required></div>
            <div class="field"><label>Email Address *</label><input type="email" name="email" value="{{ old('email') }}" required></div>
          </div>
          <div class="form-row">
            <div class="field"><label>Phone Number</label><input type="tel" name="phone" value="{{ old('phone') }}"></div>
            <div class="field"><label>Subject *</label>
              <select name="subject" required>
                <option value="">Select a subject…</option>
                @foreach($site->contact_subjects ?? ['General Inquiry','Admissions','Programs','Fees & Payments','Other'] as $s)
                <option value="{{ $s }}" {{ old('subject')===$s ? 'selected':'' }}>{{ $s }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="field"><label>Your Message *</label><textarea name="message" rows="5" required placeholder="Write your message here…">{{ old('message') }}</textarea></div>
          <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:14px;">
            <i class="fas fa-paper-plane"></i> Send Message
          </button>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
