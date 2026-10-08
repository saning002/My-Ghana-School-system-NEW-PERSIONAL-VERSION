@extends('website.layout')
@section('page_title','Apply')
@section('meta_description','Apply for enrollment at '.($site->site_name ?? 'our school').'.')

@section('extra_css')
.apply-layout{display:grid;grid-template-columns:1fr 320px;gap:40px;align-items:start;}
.form-block{background:var(--white);border-radius:var(--r-xl);padding:36px;box-shadow:var(--shadow-sm);border:1px solid var(--gray-200);margin-bottom:20px;}
.form-block-title{display:flex;align-items:center;gap:10px;font-size:.95rem;font-weight:800;color:var(--navy);margin-bottom:22px;padding-bottom:14px;border-bottom:2px solid var(--gray-100);}
.form-block-title i{color:var(--gold);}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.field{margin-bottom:16px;}
.field label{display:block;font-size:.75rem;font-weight:700;color:var(--gray-700);margin-bottom:6px;text-transform:uppercase;letter-spacing:.06em;}
.field input,.field select,.field textarea{width:100%;padding:11px 15px;border:1.5px solid var(--gray-200);border-radius:var(--r);font-size:.9rem;color:var(--gray-900);font-family:inherit;transition:var(--trans);background:var(--white);}
.field input:focus,.field select:focus,.field textarea:focus{outline:none;border-color:var(--gold);box-shadow:0 0 0 3px rgba(233,164,34,.12);}
.field-err{font-size:.75rem;color:var(--red);margin-top:4px;}
.apply-sidebar{position:sticky;top:88px;}
.sidebar-box{background:linear-gradient(160deg,var(--navy) 0%,#162d60 100%);border-radius:var(--r-xl);padding:30px;color:var(--white);margin-bottom:16px;}
.sidebar-box h3{font-size:1rem;font-weight:800;color:var(--white);margin-bottom:18px;}
.sb-item{display:flex;gap:11px;margin-bottom:14px;align-items:flex-start;}
.sb-item i{color:var(--gold);width:16px;margin-top:2px;flex-shrink:0;}
.sb-item span{font-size:.87rem;color:rgba(255,255,255,.72);line-height:1.65;}
.error-box{background:#fef2f2;border:1px solid #fca5a5;border-radius:var(--r);padding:14px 16px;margin-bottom:20px;color:#7f1d1d;font-size:.88rem;}
.error-box strong{display:block;margin-bottom:6px;}
.error-box ul{padding-left:16px;}
.error-box ul li{margin-bottom:3px;}
@media(max-width:900px){.apply-layout{grid-template-columns:1fr;}.apply-sidebar{position:static;}.form-row{grid-template-columns:1fr;}}
@endsection

@section('content')
<div class="page-hero">
  <div class="page-hero-inner">
    <h1>Apply for <em>Enrollment</em></h1>
    <p>Complete the form below to begin your child's application.</p>
    <div class="breadcrumb">
      <a href="{{ route('website.home') }}">Home</a><i class="fas fa-chevron-right"></i>
      <a href="{{ route('website.admissions') }}">Admissions</a><i class="fas fa-chevron-right"></i>
      <span>Apply</span>
    </div>
  </div>
</div>

<section class="section">
  <div class="container">
    <div class="apply-layout">
      <form method="POST" action="{{ route('website.apply.submit') }}" novalidate>
        @csrf
        @if($errors->any())
        <div class="error-box"><strong>Please fix the following:</strong><ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <div class="form-block reveal">
          <div class="form-block-title"><i class="fas fa-child"></i>Child's Information</div>
          <div class="form-row">
            <div class="field"><label>First Name *</label><input type="text" name="child_first_name" value="{{ old('child_first_name') }}" required><div class="field-err">{{ $errors->first('child_first_name') }}</div></div>
            <div class="field"><label>Last Name *</label><input type="text" name="child_last_name" value="{{ old('child_last_name') }}" required></div>
          </div>
          <div class="form-row">
            <div class="field"><label>Date of Birth *</label><input type="date" name="child_dob" value="{{ old('child_dob') }}" required></div>
            <div class="field"><label>Gender *</label>
              <select name="child_gender" required>
                <option value="">Select…</option>
                <option value="M" {{ old('child_gender')=='M'?'selected':'' }}>Male</option>
                <option value="F" {{ old('child_gender')=='F'?'selected':'' }}>Female</option>
              </select>
            </div>
          </div>
          <div class="field"><label>Program Applying For *</label>
            <select name="program_applying" required>
              <option value="">Select a program…</option>
              @foreach([['daycare','Day Care (Ages 1–2)'],['nursery','Nursery (Ages 2–3)'],['preschool','Preschool (Ages 3–4)'],['kindergarten','Kindergarten (Ages 4–5)']] as [$v,$l])
              <option value="{{ $v }}" {{ old('program_applying')==$v?'selected':'' }}>{{ $l }}</option>
              @endforeach
            </select>
          </div>
          <div class="field"><label>Previous School (if any)</label><input type="text" name="previous_school" value="{{ old('previous_school') }}" placeholder="Leave blank if none"></div>
          <div class="field"><label>Special Needs / Medical Notes</label><textarea name="special_needs" rows="3" placeholder="Allergies, disabilities, or medical conditions we should know about…">{{ old('special_needs') }}</textarea></div>
        </div>

        <div class="form-block reveal reveal-delay-1">
          <div class="form-block-title"><i class="fas fa-user"></i>Parent / Guardian Information</div>
          <div class="form-row">
            <div class="field"><label>Full Name *</label><input type="text" name="parent_name" value="{{ old('parent_name') }}" required></div>
            <div class="field"><label>Relationship *</label>
              <select name="relationship" required>
                <option value="">Select…</option>
                @foreach(['Mother','Father','Guardian','Grandparent','Other'] as $r)
                <option value="{{ $r }}" {{ old('relationship')==$r?'selected':'' }}>{{ $r }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="field"><label>Email *</label><input type="email" name="parent_email" value="{{ old('parent_email') }}" required></div>
            <div class="field"><label>Phone *</label><input type="tel" name="parent_phone" value="{{ old('parent_phone') }}" required></div>
          </div>
          <div class="field"><label>Home Address *</label><textarea name="address" rows="2" required>{{ old('address') }}</textarea></div>
          <div class="field"><label>How Did You Hear About Us?</label>
            <select name="how_did_you_hear">
              <option value="">Select…</option>
              @foreach(['Social Media','Friend or Family','Google Search','School Website','Flyer / Poster','Other'] as $h)
              <option value="{{ $h }}" {{ old('how_did_you_hear')==$h?'selected':'' }}>{{ $h }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:15px;font-size:.95rem;">
          <i class="fas fa-paper-plane"></i> Submit Application
        </button>
      </form>

      <aside class="apply-sidebar reveal reveal-delay-2">
        <div class="sidebar-box">
          <h3><i class="fas fa-info-circle" style="color:var(--gold);margin-right:8px;"></i>Before You Apply</h3>
          <div class="sb-item"><i class="fas fa-check-circle"></i><span>Ensure your child meets the age requirement for the selected program.</span></div>
          <div class="sb-item"><i class="fas fa-check-circle"></i><span>Prepare your child's birth certificate and immunisation records.</span></div>
          <div class="sb-item"><i class="fas fa-check-circle"></i><span>We will contact you within 2–3 business days to confirm receipt.</span></div>
          <div class="sb-item"><i class="fas fa-check-circle"></i><span>A brief orientation is scheduled after document review.</span></div>
          @if(isset($site) && $site->contact_phone)
          <div style="margin-top:20px;padding-top:18px;border-top:1px solid rgba(255,255,255,.1);">
            <p style="font-size:.78rem;color:rgba(255,255,255,.45);margin-bottom:6px;">Questions? Call us:</p>
            <a href="tel:{{ $site->contact_phone }}" style="color:var(--gold);font-weight:700;font-size:.98rem;">{{ $site->contact_phone }}</a>
          </div>
          @endif
        </div>
        <div style="background:var(--off-white);border-radius:var(--r-lg);padding:22px;border:1px solid var(--gray-200);">
          <p style="font-size:.82rem;color:var(--gray-500);line-height:1.7;"><i class="fas fa-lock" style="color:var(--gold-dark);margin-right:6px;"></i>Your information is secure and will only be used for enrollment purposes.</p>
        </div>
      </aside>
    </div>
  </div>
</section>
@endsection
