@extends('layouts.app')
@section('title','Website Settings')
@section('subtitle','Fully customise every text, section and detail on the public website')
@section('content')
@php $s = $setting; @endphp

<form method="POST" action="{{ route('admin.website.site-settings.save') }}" enctype="multipart/form-data" class="space-y-5">
@csrf
@if($errors->any())
<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-4 text-sm text-red-800">
    <strong>Fix the following:</strong>
    <ul class="mt-1 list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif
@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i>{{ session('success') }}
</div>
@endif

{{-- Tab Navigation --}}
<div class="flex gap-1 p-1 bg-slate-100 rounded-2xl overflow-x-auto" id="wsTabs">
@foreach([
    ['identity','fa-school','Identity'],
    ['hero','fa-image','Hero'],
    ['home','fa-home','Home'],
    ['about','fa-info-circle','About'],
    ['academics','fa-graduation-cap','Academics'],
    ['admissions','fa-file-alt','Admissions'],
    ['contact','fa-envelope','Contact'],
    ['footer','fa-layer-group','Footer']
] as [$id,$icon,$label])
<button type="button" onclick="wsTab('{{ $id }}')" id="wsbtn-{{ $id }}"
    class="ws-tab flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition-all text-slate-500 hover:text-slate-800">
    <i class="fas {{ $icon }} text-[10px]"></i> {{ $label }}
</button>
@endforeach
</div>

{{-- IDENTITY TAB --}}
<div id="ws-identity" class="ws-panel space-y-4">
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-yellow-50 flex items-center gap-2">
            <i class="fas fa-school text-yellow-600"></i>
            <h3 class="text-sm font-bold text-yellow-900">School Identity</h3>
        </div>
        <div class="px-5 py-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="ws-label">Site / School Name *</label>
                <input type="text" name="site_name" value="{{ old('site_name',$s->site_name) }}" required class="ws-input">
            </div>
            <div>
                <label class="ws-label">Tagline</label>
                <input type="text" name="tagline" value="{{ old('tagline',$s->tagline) }}" placeholder="Where Every Child Comes First" class="ws-input">
            </div>
            <div>
                <label class="ws-label">Navbar Sub-text</label>
                <input type="text" name="nav_brand_sub" value="{{ old('nav_brand_sub',$s->nav_brand_sub ?? 'Education & Excellence') }}" class="ws-input">
            </div>
            <div>
                <label class="ws-label">Enroll Button Text</label>
                <input type="text" name="nav_enroll_text" value="{{ old('nav_enroll_text',$s->nav_enroll_text ?? 'Enroll Now') }}" class="ws-input">
            </div>
        </div>
    </div>
    
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-green-50">
            <h3 class="text-sm font-bold text-green-900"><i class="fas fa-image text-green-500 mr-1.5"></i>School Logo</h3>
        </div>
        <div class="px-5 py-5">
            @if($s->logo_url)
            <img src="{{ $s->logo_url }}" class="h-14 mb-3 rounded-xl border border-gray-200 p-1">
            @endif
            <input type="file" name="logo" accept="image/*" class="text-sm text-gray-600">
        </div>
    </div>
</div>

{{-- HERO TAB --}}
<div id="ws-hero" class="ws-panel hidden space-y-4">

    {{-- Hero Text Content --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-indigo-50 flex items-center gap-2">
            <i class="fas fa-heading text-indigo-500"></i>
            <h3 class="text-sm font-bold text-indigo-900">Hero Text Content</h3>
        </div>
        <div class="px-5 py-5 space-y-4">
            <div>
                <label class="ws-label">Eyebrow Badge Text</label>
                <input type="text" name="hero_eyebrow" value="{{ old('hero_eyebrow',$s->hero_eyebrow ?? 'Welcome to '.$s->site_name) }}" class="ws-input" placeholder="Welcome to Our School">
            </div>
            <div>
                <label class="ws-label">Hero Title <span class="normal-case font-normal text-gray-400">(wrap words in &lt;em&gt; for gold highlight)</span></label>
                <input type="text" name="hero_title" value="{{ old('hero_title',$s->hero_title) }}" class="ws-input" placeholder="Nurturing Young Minds for a <em>Bright Future</em>">
            </div>
            <div>
                <label class="ws-label">Hero Subtitle</label>
                <textarea name="hero_subtitle" rows="2" class="ws-input">{{ old('hero_subtitle',$s->hero_subtitle) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ws-label">Primary Button Text</label>
                    <input type="text" name="hero_cta_primary_text" value="{{ old('hero_cta_primary_text',$s->hero_cta_primary_text ?? 'Apply Now') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Secondary Button Text</label>
                    <input type="text" name="hero_cta_secondary_text" value="{{ old('hero_cta_secondary_text',$s->hero_cta_secondary_text ?? 'Learn More') }}" class="ws-input">
                </div>
            </div>
        </div>
    </div>

    {{-- Hero Layout --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-purple-50 flex items-center gap-2">
            <i class="fas fa-th-large text-purple-500"></i>
            <h3 class="text-sm font-bold text-purple-900">Layout & Content Position</h3>
        </div>
        <div class="px-5 py-5">
            <label class="ws-label mb-3">Hero Layout Style</label>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                @foreach([
                    ['split','fa-columns','Split','Text left, panel right (default)'],
                    ['centered','fa-align-center','Centered','Everything centered, no panel'],
                    ['fullscreen','fa-expand','Fullscreen','Centered with extra padding'],
                    ['minimal','fa-minus','Minimal','Title only, no buttons or stats'],
                ] as [$val,$icon,$label,$desc])
                <label class="relative cursor-pointer">
                    <input type="radio" name="hero_layout" value="{{ $val }}"
                           {{ (old('hero_layout',$s->hero_layout ?? 'split') === $val) ? 'checked' : '' }}
                           class="peer sr-only">
                    <div class="border-2 border-gray-200 rounded-xl p-4 text-center transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 hover:border-indigo-300">
                        <i class="fas {{ $icon }} text-xl text-gray-400 peer-checked:text-indigo-600 mb-2 block"></i>
                        <div class="text-xs font-bold text-gray-800">{{ $label }}</div>
                        <div class="text-[10px] text-gray-400 mt-1 leading-tight">{{ $desc }}</div>
                    </div>
                </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Hero Background --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-orange-50 flex items-center gap-2">
            <i class="fas fa-paint-brush text-orange-500"></i>
            <h3 class="text-sm font-bold text-orange-900">Hero Background</h3>
        </div>
        <div class="px-5 py-5 space-y-5">

            {{-- Background type selector --}}
            <div>
                <label class="ws-label mb-3">Background Type</label>
                <div class="grid grid-cols-3 sm:grid-cols-6 gap-2" id="heroBgTypeSelector">
                    @foreach([
                        ['default','fa-layer-group','Default','Navy gradient'],
                        ['solid','fa-square','Solid','One flat colour'],
                        ['gradient','fa-fill-drip','Gradient','Two+ colours'],
                        ['glassmorphism','fa-glass-martini-alt','Glass','Frosted glass'],
                        ['image','fa-image','Image','Upload photo'],
                        ['video','fa-film','Video','URL or upload'],
                    ] as [$val,$icon,$label,$desc])
                    <label class="cursor-pointer">
                        <input type="radio" name="hero_bg_type" value="{{ $val }}"
                               {{ (old('hero_bg_type',$s->hero_bg_type ?? 'default') === $val) ? 'checked' : '' }}
                               class="peer sr-only" onchange="heroShowBgPanel('{{ $val }}')">
                        <div class="border-2 border-gray-200 rounded-xl p-3 text-center transition-all peer-checked:border-orange-500 peer-checked:bg-orange-50 hover:border-orange-300">
                            <i class="fas {{ $icon }} text-lg text-gray-400 mb-1 block"></i>
                            <div class="text-xs font-bold text-gray-700">{{ $label }}</div>
                            <div class="text-[10px] text-gray-400 leading-tight">{{ $desc }}</div>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Solid colour panel --}}
            <div id="heroBg-solid" class="hero-bg-panel space-y-3 hidden">
                <div class="rounded-xl border border-orange-200 bg-orange-50/40 p-4">
                    <label class="ws-label">Background Colour</label>
                    <div class="flex items-center gap-3 mt-1">
                        <input type="color" name="hero_bg_solid_color"
                               value="{{ old('hero_bg_solid_color', $s->hero_bg_solid_color ?? '#0a1f44') }}"
                               class="w-12 h-10 rounded-lg border border-gray-300 cursor-pointer p-1">
                        <input type="text" id="solidColorText" value="{{ old('hero_bg_solid_color', $s->hero_bg_solid_color ?? '#0a1f44') }}"
                               class="ws-input w-32 font-mono" placeholder="#0a1f44"
                               oninput="syncColor(this,'[name=hero_bg_solid_color]')">
                        <div id="solidPreview" class="flex-1 h-10 rounded-xl border border-gray-200"
                             style="background:{{ $s->hero_bg_solid_color ?? '#0a1f44' }}"></div>
                    </div>
                </div>
            </div>

            {{-- Gradient panel --}}
            <div id="heroBg-gradient" class="hero-bg-panel space-y-3 hidden">
                <div class="rounded-xl border border-orange-200 bg-orange-50/40 p-4 space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="ws-label">From Colour</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="color" name="hero_gradient_from"
                                       value="{{ old('hero_gradient_from', $s->hero_gradient_from ?? '#050d22') }}"
                                       class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5"
                                       oninput="updateGradientPreview()">
                                <input type="text" value="{{ old('hero_gradient_from', $s->hero_gradient_from ?? '#050d22') }}"
                                       class="ws-input font-mono" placeholder="#050d22"
                                       oninput="syncColor(this,'[name=hero_gradient_from]');updateGradientPreview()">
                            </div>
                        </div>
                        <div>
                            <label class="ws-label">Via Colour <span class="normal-case font-normal text-gray-400">(optional)</span></label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="color" name="hero_gradient_via"
                                       value="{{ old('hero_gradient_via', $s->hero_gradient_via ?? '') }}"
                                       class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5"
                                       oninput="updateGradientPreview()">
                                <input type="text" value="{{ old('hero_gradient_via', $s->hero_gradient_via ?? '') }}"
                                       class="ws-input font-mono" placeholder="#1e4db7 (optional)"
                                       oninput="syncColor(this,'[name=hero_gradient_via]');updateGradientPreview()">
                            </div>
                        </div>
                        <div>
                            <label class="ws-label">To Colour</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="color" name="hero_gradient_to"
                                       value="{{ old('hero_gradient_to', $s->hero_gradient_to ?? '#122859') }}"
                                       class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5"
                                       oninput="updateGradientPreview()">
                                <input type="text" value="{{ old('hero_gradient_to', $s->hero_gradient_to ?? '#122859') }}"
                                       class="ws-input font-mono" placeholder="#122859"
                                       oninput="syncColor(this,'[name=hero_gradient_to]');updateGradientPreview()">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="ws-label">Gradient Angle: <span id="angleValue">{{ old('hero_gradient_angle', $s->hero_gradient_angle ?? 150) }}°</span></label>
                        <input type="range" name="hero_gradient_angle" min="0" max="360"
                               value="{{ old('hero_gradient_angle', $s->hero_gradient_angle ?? 150) }}"
                               class="w-full mt-2 accent-orange-500"
                               oninput="document.getElementById('angleValue').textContent=this.value+'°';updateGradientPreview()">
                    </div>
                    <div>
                        <label class="ws-label">Live Preview</label>
                        <div id="gradientPreview" class="mt-1 h-16 rounded-xl border border-gray-200"></div>
                    </div>
                </div>
            </div>

            {{-- Glassmorphism panel --}}
            <div id="heroBg-glassmorphism" class="hero-bg-panel space-y-3 hidden">
                <div class="rounded-xl border border-orange-200 bg-orange-50/40 p-4 space-y-4">
                    <p class="text-xs text-gray-500">Glassmorphism uses your gradient colours with a blur effect. Set your From/To colours below.</p>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="ws-label">From Colour</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="color" name="hero_gradient_from"
                                       value="{{ old('hero_gradient_from', $s->hero_gradient_from ?? '#050d22') }}"
                                       class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5">
                                <input type="text" value="{{ old('hero_gradient_from', $s->hero_gradient_from ?? '#050d22') }}"
                                       class="ws-input font-mono" placeholder="#050d22"
                                       oninput="syncColor(this,'[name=hero_gradient_from]')">
                            </div>
                        </div>
                        <div>
                            <label class="ws-label">To Colour</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input type="color" name="hero_gradient_to"
                                       value="{{ old('hero_gradient_to', $s->hero_gradient_to ?? '#122859') }}"
                                       class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5">
                                <input type="text" value="{{ old('hero_gradient_to', $s->hero_gradient_to ?? '#122859') }}"
                                       class="ws-input font-mono" placeholder="#122859"
                                       oninput="syncColor(this,'[name=hero_gradient_to]')">
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="ws-label">Angle: <span id="glassAngleValue">{{ old('hero_gradient_angle', $s->hero_gradient_angle ?? 135) }}°</span></label>
                        <input type="range" name="hero_gradient_angle" min="0" max="360"
                               value="{{ old('hero_gradient_angle', $s->hero_gradient_angle ?? 135) }}"
                               class="w-full mt-2 accent-orange-500"
                               oninput="document.getElementById('glassAngleValue').textContent=this.value+'°'">
                    </div>
                </div>
            </div>

            {{-- Image panel --}}
            <div id="heroBg-image" class="hero-bg-panel space-y-3 hidden">
                <div class="rounded-xl border border-orange-200 bg-orange-50/40 p-4 space-y-3">
                    @if($s->hero_bg_image)
                    <div>
                        <label class="ws-label">Current Image</label>
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($s->hero_bg_image) }}"
                             class="mt-1 h-32 w-full object-cover rounded-xl border border-gray-200">
                    </div>
                    @endif
                    <div>
                        <label class="ws-label">Upload New Background Image <span class="normal-case font-normal text-gray-400">(JPG, PNG, WebP — max 8MB)</span></label>
                        <input type="file" name="hero_bg_image" accept="image/jpeg,image/png,image/jpg,image/webp"
                               class="mt-1 text-sm text-gray-600 block">
                    </div>
                    <div>
                        <label class="ws-label">Dark Overlay Opacity: <span id="overlayValue">{{ old('hero_overlay_opacity', $s->hero_overlay_opacity ?? 40) }}%</span></label>
                        <input type="range" name="hero_overlay_opacity" min="0" max="90"
                               value="{{ old('hero_overlay_opacity', $s->hero_overlay_opacity ?? 40) }}"
                               class="w-full mt-2 accent-orange-500"
                               oninput="document.getElementById('overlayValue').textContent=this.value+'%'">
                        <p class="text-[10px] text-gray-400 mt-1">Increase to darken image and make text more readable</p>
                    </div>
                </div>
            </div>

            {{-- Video panel --}}
            <div id="heroBg-video" class="hero-bg-panel space-y-3 hidden">
                <div class="rounded-xl border border-orange-200 bg-orange-50/40 p-4 space-y-3">
                    <div>
                        <label class="ws-label">Video URL <span class="normal-case font-normal text-gray-400">(YouTube link, MP4 URL, or any direct video URL)</span></label>
                        <input type="url" name="hero_bg_video_url"
                               value="{{ old('hero_bg_video_url', $s->hero_bg_video_url) }}"
                               class="ws-input mt-1" placeholder="https://www.youtube.com/watch?v=... or https://example.com/video.mp4">
                        <p class="text-[10px] text-gray-400 mt-1">Supports YouTube, Vimeo, and direct MP4/WebM/OGG/MOV/AVI links</p>
                    </div>
                    <div>
                        <label class="ws-label">Dark Overlay Opacity: <span id="videoOverlayValue">{{ old('hero_overlay_opacity', $s->hero_overlay_opacity ?? 45) }}%</span></label>
                        <input type="range" name="hero_overlay_opacity" min="0" max="90"
                               value="{{ old('hero_overlay_opacity', $s->hero_overlay_opacity ?? 45) }}"
                               class="w-full mt-2 accent-orange-500"
                               oninput="document.getElementById('videoOverlayValue').textContent=this.value+'%'">
                    </div>
                </div>
            </div>

            {{-- Default panel (informational only) --}}
            <div id="heroBg-default" class="hero-bg-panel">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4">
                    <p class="text-sm text-gray-500 flex items-center gap-2">
                        <i class="fas fa-info-circle text-gray-400"></i>
                        Using the default navy gradient background. Select another type above to customise.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Theme Colors --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-rose-50 flex items-center gap-2">
            <i class="fas fa-palette text-rose-500"></i>
            <h3 class="text-sm font-bold text-rose-900">Theme Colors</h3>
        </div>
        <div class="px-5 py-5 space-y-6">
            @php
            $tc  = $s->theme ?? \App\Models\Website\SiteSetting::defaultTheme();
            $wtc = $s->websiteTheme ?? \App\Models\Website\SiteSetting::defaultWebsiteTheme();
            @endphp

            {{-- ── WEBSITE THEME ── --}}
            <div class="rounded-xl border border-blue-200 bg-blue-50/40 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-blue-800">Public Website Colors</h4>
                        <p class="text-[10px] text-blue-500 mt-0.5">Only affects the public-facing website — homepage, about, admissions, etc.</p>
                    </div>
                    <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-1 rounded-full font-semibold">Website only</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach([
                        ['wt_primary',  'Primary (Gold)',   'Buttons, highlights, links',        $wtc['primary']],
                        ['wt_secondary','Secondary (Navy)', 'Headings, hero backgrounds, cards',  $wtc['secondary']],
                        ['wt_accent',   'Accent',          'Badges, active states, icons',        $wtc['accent']],
                        ['wt_text',     'Body Text',       'Main paragraph text colour',          $wtc['text']],
                        ['wt_bg',       'Page Background', 'White / off-white page bg',           $wtc['bg']],
                        ['wt_navBg',    'Navbar Background','Website top navbar colour',          $wtc['navBg']],
                    ] as [$name,$label,$desc,$val])
                    <div>
                        <label class="ws-label">{{ $label }}</label>
                        <p class="text-[10px] text-gray-400 mb-2">{{ $desc }}</p>
                        <div class="flex items-center gap-2">
                            <input type="color" name="{{ $name }}" value="{{ $val }}"
                                   class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5 wt-picker"
                                   oninput="updateWebsitePreview()">
                            <input type="text" value="{{ $val }}" class="ws-input font-mono flex-1 wt-text"
                                   data-picker="{{ $name }}"
                                   oninput="syncColorInput(this);updateWebsitePreview()">
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Website preview strip --}}
                <div>
                    <label class="ws-label mb-1">Preview</label>
                    <div class="flex rounded-xl overflow-hidden h-8 border border-gray-200">
                        @foreach(['wt_primary'=>$wtc['primary'],'wt_secondary'=>$wtc['secondary'],'wt_accent'=>$wtc['accent'],'wt_navBg'=>$wtc['navBg'],'wt_bg'=>$wtc['bg']] as $n=>$v)
                        <div class="flex-1" id="wtp-{{ $n }}" style="background:{{ $v }}"></div>
                        @endforeach
                    </div>
                    <div class="flex text-[9px] text-gray-400 mt-0.5">
                        <div class="flex-1 text-center">Primary</div>
                        <div class="flex-1 text-center">Secondary</div>
                        <div class="flex-1 text-center">Accent</div>
                        <div class="flex-1 text-center">Navbar</div>
                        <div class="flex-1 text-center">Page BG</div>
                    </div>
                </div>

                {{-- Website presets --}}
                <div>
                    <label class="ws-label mb-2">Quick Presets</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['Navy & Gold',    '#e9a422','#0a1f44','#0891b2','#3a3830','#ffffff','#0a1f44'],
                            ['Forest Green',   '#22c55e','#14532d','#0ea5e9','#1c1917','#f7fef7','#14532d'],
                            ['Royal Purple',   '#a855f7','#3b0764','#06b6d4','#1e1b4b','#faf5ff','#3b0764'],
                            ['Crimson Red',    '#ef4444','#450a0a','#f97316','#1c1917','#fff5f5','#450a0a'],
                            ['Ocean Blue',     '#3b82f6','#1e3a5f','#0ea5e9','#1e293b','#f0f9ff','#1e3a5f'],
                            ['Warm Amber',     '#f59e0b','#78350f','#10b981','#292524','#fffbeb','#78350f'],
                            ['Rose Gold',      '#f43f5e','#1c0a14','#fb923c','#1c1917','#fff1f2','#1c0a14'],
                            ['Emerald Fresh',  '#10b981','#064e3b','#3b82f6','#1c2520','#ecfdf5','#064e3b'],
                        ] as [$pn,$pp,$ps,$pa,$pt,$pb,$pnb])
                        <button type="button"
                                onclick="applyWebsitePreset('{{ $pp }}','{{ $ps }}','{{ $pa }}','{{ $pt }}','{{ $pb }}','{{ $pnb }}')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 hover:border-blue-400 transition-colors bg-white flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full" style="background:{{ $pp }}"></span>
                            <span class="w-3 h-3 rounded-full" style="background:{{ $ps }}"></span>
                            {{ $pn }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ── ADMIN THEME ── --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Admin Panel Colors</h4>
                        <p class="text-[10px] text-slate-500 mt-0.5">Only affects the admin dashboard sidebar and buttons — not the public website.</p>
                    </div>
                    <span class="text-[10px] bg-slate-200 text-slate-700 px-2 py-1 rounded-full font-semibold">Admin only</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach([
                        ['theme_adminPrimary','Admin Primary',  'Sidebar highlights & buttons', $tc['adminPrimary'] ?? '#D4A017'],
                        ['theme_adminNav',    'Admin Sidebar',  'Sidebar background colour',    $tc['adminNav']     ?? '#0B1121'],
                        ['theme_accent',      'Accent / Teal',  'Student portal active states', $tc['accent']       ?? '#0891b2'],
                        ['theme_primary',     'Admin Gold',     'Admin button base colour',     $tc['primary']      ?? '#e9a422'],
                    ] as [$name,$label,$desc,$val])
                    <div>
                        <label class="ws-label">{{ $label }}</label>
                        <p class="text-[10px] text-gray-400 mb-2">{{ $desc }}</p>
                        <div class="flex items-center gap-2">
                            <input type="color" name="{{ $name }}" value="{{ $val }}"
                                   class="w-10 h-9 rounded-lg border border-gray-300 cursor-pointer p-0.5 admin-picker"
                                   oninput="updateAdminPreview()">
                            <input type="text" value="{{ $val }}" class="ws-input font-mono flex-1 admin-text"
                                   data-picker="{{ $name }}"
                                   oninput="syncColorInput(this);updateAdminPreview()">
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Admin preview strip --}}
                <div>
                    <label class="ws-label mb-1">Preview</label>
                    <div class="flex rounded-xl overflow-hidden h-8 border border-gray-200">
                        @foreach(['theme_adminNav'=>($tc['adminNav']??'#0B1121'),'theme_adminPrimary'=>($tc['adminPrimary']??'#D4A017'),'theme_accent'=>($tc['accent']??'#0891b2')] as $n=>$v)
                        <div class="flex-1" id="atp-{{ $n }}" style="background:{{ $v }}"></div>
                        @endforeach
                    </div>
                    <div class="flex text-[9px] text-gray-400 mt-0.5">
                        <div class="flex-1 text-center">Sidebar BG</div>
                        <div class="flex-1 text-center">Primary</div>
                        <div class="flex-1 text-center">Accent</div>
                    </div>
                </div>

                {{-- Admin presets --}}
                <div>
                    <label class="ws-label mb-2">Quick Presets</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach([
                            ['Dark Navy & Gold', '#D4A017','#0B1121','#0891b2','#e9a422'],
                            ['Dark Green',       '#22c55e','#052e16','#0ea5e9','#16a34a'],
                            ['Dark Purple',      '#a855f7','#2e1065','#06b6d4','#9333ea'],
                            ['Dark Red',         '#ef4444','#27272a','#f97316','#dc2626'],
                            ['Slate Blue',       '#3b82f6','#0f172a','#0ea5e9','#2563eb'],
                        ] as [$pn,$pap,$pan,$paa,$pp])
                        <button type="button"
                                onclick="applyAdminPreset('{{ $pap }}','{{ $pan }}','{{ $paa }}','{{ $pp }}')"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border border-gray-200 hover:border-slate-400 transition-colors bg-white flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-full" style="background:{{ $pan }}"></span>
                            <span class="w-3 h-3 rounded-full" style="background:{{ $pap }}"></span>
                            {{ $pn }}
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

{{-- HOME TAB --}}
<div id="ws-home" class="ws-panel hidden space-y-4">
    @foreach([
        ['programs','Programs','Our Programs','Programs Designed for Every Stage'],
        ['testimonials','Testimonials','Parent Reviews','What Parents Say'],
        ['blog','Blog','Latest News','From Our Blog'],
        ['events','Events','School Calendar','Upcoming Events']
    ] as [$key,$label,$defaultEyebrow,$defaultTitle])
    <div class="card overflow-hidden">
        <div class="px-5 py-3.5 border-b bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800">{{ $label }} Section</h3>
        </div>
        <div class="px-5 py-4 grid grid-cols-3 gap-3">
            <div>
                <label class="ws-label">Eyebrow</label>
                <input type="text" name="{{ $key }}_eyebrow" value="{{ old($key.'_eyebrow',$s->{$key.'_eyebrow'} ?? $defaultEyebrow) }}" class="ws-input">
            </div>
            <div>
                <label class="ws-label">Title</label>
                <input type="text" name="{{ $key }}_title" value="{{ old($key.'_title',$s->{$key.'_title'} ?? $defaultTitle) }}" class="ws-input">
            </div>
            <div>
                <label class="ws-label">Subtitle</label>
                <input type="text" name="{{ $key }}_subtitle" value="{{ old($key.'_subtitle',$s->{$key.'_subtitle'} ?? '') }}" class="ws-input">
            </div>
        </div>
    </div>
    @endforeach

    {{-- CTA Banner --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-yellow-50">
            <h3 class="text-sm font-bold text-yellow-900">Call-to-Action Banner</h3>
        </div>
        <div class="px-5 py-5 space-y-3">
            <div>
                <label class="ws-label">CTA Title</label>
                <input type="text" name="cta_title" value="{{ old('cta_title',$s->cta_title ?? 'Ready to Join Our Family?') }}" class="ws-input">
            </div>
            <div>
                <label class="ws-label">CTA Subtitle</label>
                <input type="text" name="cta_subtitle" value="{{ old('cta_subtitle',$s->cta_subtitle ?? 'Applications are open. Spots are limited.') }}" class="ws-input">
            </div>
        </div>
    </div>
</div>

{{-- ABOUT TAB --}}
<div id="ws-about" class="ws-panel hidden space-y-4">
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-blue-50">
            <h3 class="text-sm font-bold text-blue-900">About Page Content</h3>
        </div>
        <div class="px-5 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ws-label">Mission</label>
                    <textarea name="mission" rows="3" class="ws-input">{{ old('mission',$s->mission) }}</textarea>
                </div>
                <div>
                    <label class="ws-label">Vision</label>
                    <textarea name="vision" rows="3" class="ws-input">{{ old('vision',$s->vision) }}</textarea>
                </div>
            </div>
            <div>
                <label class="ws-label">Our Story / History</label>
                <textarea name="about_story_text" rows="4" class="ws-input">{{ old('about_story_text',$s->about_story_text ?? $s->history) }}</textarea>
            </div>
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="ws-label">Staff Eyebrow</label>
                    <input type="text" name="staff_eyebrow" value="{{ old('staff_eyebrow',$s->staff_eyebrow ?? 'Meet the Team') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Staff Title</label>
                    <input type="text" name="staff_title" value="{{ old('staff_title',$s->staff_title ?? 'Our Dedicated Educators') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Staff Subtitle</label>
                    <input type="text" name="staff_subtitle" value="{{ old('staff_subtitle',$s->staff_subtitle ?? 'Passionate professionals committed to every child\'s success.') }}" class="ws-input">
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ACADEMICS TAB --}}
<div id="ws-academics" class="ws-panel hidden space-y-4">
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-teal-50">
            <h3 class="text-sm font-bold text-teal-900"><i class="fas fa-graduation-cap text-teal-500 mr-1.5"></i>Academics Page Content</h3>
        </div>
        <div class="px-5 py-5 space-y-4">
            {{-- Page hero --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ws-label">Page Hero Title (supports &lt;em&gt;)</label>
                    <input type="text" name="academics_hero_title" value="{{ old('academics_hero_title',$s->academics_hero_title ?? 'Our Academic Programs') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Page Hero Subtitle</label>
                    <input type="text" name="academics_hero_subtitle" value="{{ old('academics_hero_subtitle',$s->academics_hero_subtitle ?? 'Carefully designed programs rooted in academic excellence and Kingdom values.') }}" class="ws-input">
                </div>
            </div>

            {{-- Programs section heading --}}
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="ws-label">Section Eyebrow</label>
                    <input type="text" name="academics_eyebrow" value="{{ old('academics_eyebrow',$s->academics_eyebrow ?? 'What We Offer') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Section Title</label>
                    <input type="text" name="academics_section_title" value="{{ old('academics_section_title',$s->academics_section_title ?? 'Programs for Every Learner') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Section Subtitle</label>
                    <input type="text" name="academics_section_subtitle" value="{{ old('academics_section_subtitle',$s->academics_section_subtitle ?? 'Every program is thoughtfully designed to develop knowledge, character, and skills.') }}" class="ws-input">
                </div>
            </div>

            {{-- Program card labels --}}
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="ws-label">Curriculum Section Label</label>
                    <input type="text" name="academics_curriculum_label" value="{{ old('academics_curriculum_label',$s->academics_curriculum_label ?? 'Curriculum Overview') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Highlights Section Label</label>
                    <input type="text" name="academics_highlights_label" value="{{ old('academics_highlights_label',$s->academics_highlights_label ?? 'Program Highlights') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Schedule Section Label</label>
                    <input type="text" name="academics_schedule_label" value="{{ old('academics_schedule_label',$s->academics_schedule_label ?? 'Schedule') }}" class="ws-input">
                </div>
            </div>

            {{-- Empty state & button texts --}}
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="ws-label">No Programs Message</label>
                    <input type="text" name="academics_empty_text" value="{{ old('academics_empty_text',$s->academics_empty_text ?? 'Our programs are being updated. Check back soon!') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Apply Button Text (on each program card)</label>
                    <input type="text" name="academics_apply_btn" value="{{ old('academics_apply_btn',$s->academics_apply_btn ?? 'Apply Now') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Contact Button Text (empty state)</label>
                    <input type="text" name="academics_contact_btn" value="{{ old('academics_contact_btn',$s->academics_contact_btn ?? 'Contact Us') }}" class="ws-input">
                </div>
            </div>

            {{-- Bottom CTA strip on Academics page --}}
            <div class="rounded-xl border border-teal-200 bg-teal-50/50 p-4 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-teal-900">Bottom Enrollment Banner</h4>
                <div>
                    <label class="ws-label">Banner Text</label>
                    <input type="text" name="academics_cta_text" value="{{ old('academics_cta_text',$s->academics_cta_text ?? 'Ready to enroll your child? Spaces fill up fast.') }}" class="ws-input">
                </div>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="ws-label">Button 1 Text (Apply)</label>
                        <input type="text" name="academics_cta_btn1_text" value="{{ old('academics_cta_btn1_text',$s->academics_cta_btn1_text ?? 'Apply Now') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Button 2 Text (Admissions)</label>
                        <input type="text" name="academics_cta_btn2_text" value="{{ old('academics_cta_btn2_text',$s->academics_cta_btn2_text ?? 'Admissions Info') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Button 3 Text (Contact)</label>
                        <input type="text" name="academics_cta_btn3_text" value="{{ old('academics_cta_btn3_text',$s->academics_cta_btn3_text ?? 'Contact Us') }}" class="ws-input">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ADMISSIONS TAB --}}
<div id="ws-admissions" class="ws-panel hidden space-y-4">
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-slate-50">
            <h3 class="text-sm font-bold text-slate-800">Admissions Page</h3>
        </div>
        <div class="px-5 py-5 space-y-4">
            <div class="grid grid-cols-3 gap-3">
                <div>
                    <label class="ws-label">Eyebrow</label>
                    <input type="text" name="admissions_steps_eyebrow" value="{{ old('admissions_steps_eyebrow',$s->admissions_steps_eyebrow ?? 'Simple Process') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Process Title</label>
                    <input type="text" name="admissions_title" value="{{ old('admissions_title',$s->admissions_title ?? 'How to Apply in 4 Steps') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Process Subtitle</label>
                    <input type="text" name="admissions_subtitle" value="{{ old('admissions_subtitle',$s->admissions_subtitle ?? 'Enrolling your child is quick and easy.') }}" class="ws-input">
                </div>
            </div>

            {{-- Steps editor --}}
            <div>
                <label class="ws-label">Application Steps</label>
                <div class="space-y-3 mt-2" id="stepsContainer">
                    @php
                    $steps = $s->admissions_steps ?? [
                        ['num'=>'1','title'=>'Fill Application','desc'=>'Complete our online form with your child\'s and parent\'s details.'],
                        ['num'=>'2','title'=>'Submit Documents','desc'=>'Provide the required documents listed below.'],
                        ['num'=>'3','title'=>'Orientation','desc'=>'Your child attends a brief, friendly orientation session.'],
                        ['num'=>'4','title'=>'Confirmation','desc'=>'Receive your official enrollment confirmation and welcome pack.'],
                    ];
                    @endphp
                    @foreach($steps as $i => $step)
                    <div class="grid grid-cols-12 gap-2 items-start bg-slate-50 rounded-xl p-3">
                        <div class="col-span-1">
                            <label class="ws-label">No.</label>
                            <input type="text" name="admissions_steps[{{ $i }}][num]" value="{{ old('admissions_steps.'.$i.'.num', $step['num']) }}" class="ws-input text-center" maxlength="3">
                        </div>
                        <div class="col-span-4">
                            <label class="ws-label">Step Title</label>
                            <input type="text" name="admissions_steps[{{ $i }}][title]" value="{{ old('admissions_steps.'.$i.'.title', $step['title']) }}" class="ws-input">
                        </div>
                        <div class="col-span-7">
                            <label class="ws-label">Description</label>
                            <input type="text" name="admissions_steps[{{ $i }}][desc]" value="{{ old('admissions_steps.'.$i.'.desc', $step['desc']) }}" class="ws-input">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div>
                <label class="ws-label">Requirements List (one per line)</label>
                <textarea name="admissions_requirements" rows="6" class="ws-input">{{ old('admissions_requirements', is_array($s->admissions_requirements) ? implode("\n",$s->admissions_requirements) : "Birth certificate\nImmunisation records\n2 passport photos\nParent / guardian ID\nPrevious school records (if any)\nCompleted application form") }}</textarea>
            </div>
            <div>
                <label class="ws-label">Additional Information Box</label>
                <textarea name="admissions_info" rows="4" class="ws-input" placeholder="Office hours, contact info, or any extra notes shown in the info box...">{{ old('admissions_info',$s->admissions_info) }}</textarea>
            </div>
        </div>
    </div>
</div>

{{-- CONTACT TAB --}}
<div id="ws-contact" class="ws-panel hidden space-y-4">
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-green-50">
            <h3 class="text-sm font-bold text-green-900">Contact Information</h3>
        </div>
        <div class="px-5 py-5 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="ws-label">Email</label>
                    <input type="email" name="contact_email" value="{{ old('contact_email',$s->contact_email) }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Phone</label>
                    <input type="text" name="contact_phone" value="{{ old('contact_phone',$s->contact_phone) }}" class="ws-input">
                </div>
            </div>
            <div>
                <label class="ws-label">Address</label>
                <input type="text" name="address" value="{{ old('address',$s->address) }}" class="ws-input">
            </div>
            <div>
                <label class="ws-label">Office Hours</label>
                <input type="text" name="office_hours" value="{{ old('office_hours',$s->office_hours ?? 'Mon – Fri: 7:00 AM – 5:00 PM') }}" class="ws-input">
            </div>
            <div>
                <label class="ws-label">Contact Form Subjects (one per line)</label>
                <textarea name="contact_subjects" rows="4" class="ws-input">{{ old('contact_subjects', is_array($s->contact_subjects) ? implode("\n",$s->contact_subjects) : "General Inquiry\nAdmissions\nPrograms\nFees & Payments\nOther") }}</textarea>
            </div>
        </div>
    </div>
    
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-purple-50">
            <h3 class="text-sm font-bold text-purple-900">Social Media Links</h3>
        </div>
        <div class="px-5 py-5 grid grid-cols-2 gap-4">
            @foreach(['facebook'=>'fa-facebook-f','twitter'=>'fa-twitter','instagram'=>'fa-instagram','youtube'=>'fa-youtube'] as $key=>$icon)
            <div>
                <label class="ws-label"><i class="fab {{ $icon }} mr-1"></i>{{ ucfirst($key) }}</label>
                <input type="url" name="{{ $key }}" value="{{ old($key,$s->$key) }}" class="ws-input" placeholder="https://...">
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- FOOTER TAB --}}
<div id="ws-footer" class="ws-panel hidden space-y-4">
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b bg-slate-100 flex items-center gap-2">
            <i class="fas fa-layer-group text-slate-700"></i>
            <h3 class="text-sm font-bold text-slate-800">Footer Section Content</h3>
        </div>
        <div class="px-5 py-5 space-y-4">
            {{-- Brand info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ws-label">Footer Brand Name (leave blank to use Site Name)</label>
                    <input type="text" name="footer_site_name" value="{{ old('footer_site_name',$s->footer_site_name) }}" placeholder="{{ $s->site_name }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Footer Tagline / Description</label>
                    <input type="text" name="footer_tagline" value="{{ old('footer_tagline',$s->footer_tagline ?? $s->tagline) }}" class="ws-input">
                </div>
            </div>

            {{-- Column Titles --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="ws-label">Column 1 Title (Navigation)</label>
                    <input type="text" name="footer_nav_title" value="{{ old('footer_nav_title',$s->footer_nav_title ?? 'Navigation') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Column 2 Title (Programs)</label>
                    <input type="text" name="footer_programs_title" value="{{ old('footer_programs_title',$s->footer_programs_title ?? 'Programs') }}" class="ws-input">
                </div>
                <div>
                    <label class="ws-label">Apply Link Text</label>
                    <input type="text" name="footer_apply_text" value="{{ old('footer_apply_text',$s->footer_apply_text ?? 'Apply Online') }}" class="ws-input">
                </div>
            </div>

            {{-- Contact Column in Footer --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Footer Contact Column Details</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="ws-label">Column 3 Title</label>
                        <input type="text" name="footer_contact_title" value="{{ old('footer_contact_title',$s->footer_contact_title ?? 'Contact') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Staff Portal Link Text</label>
                        <input type="text" name="footer_portal_text" value="{{ old('footer_portal_text',$s->footer_portal_text ?? 'Staff Portals') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Footer Address (leave blank to use main address)</label>
                        <input type="text" name="footer_address" value="{{ old('footer_address',$s->footer_address) }}" placeholder="{{ $s->address }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Footer Phone (leave blank to use main phone)</label>
                        <input type="text" name="footer_phone" value="{{ old('footer_phone',$s->footer_phone) }}" placeholder="{{ $s->contact_phone }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Footer Email (leave blank to use main email)</label>
                        <input type="email" name="footer_email" value="{{ old('footer_email',$s->footer_email) }}" placeholder="{{ $s->contact_email }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Footer Office Hours</label>
                        <input type="text" name="footer_office_hours" value="{{ old('footer_office_hours',$s->footer_office_hours ?? $s->office_hours ?? 'Mon–Fri: 7:00 AM – 5:00 PM') }}" class="ws-input">
                    </div>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-700">Footer Bottom Bar &amp; Legal Links</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="ws-label">Copyright Notice (after &copy; Year SiteName.)</label>
                        <input type="text" name="footer_copyright" value="{{ old('footer_copyright',$s->footer_copyright ?? 'All rights reserved.') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Privacy Policy Link Text</label>
                        <input type="text" name="footer_privacy_text" value="{{ old('footer_privacy_text',$s->footer_privacy_text ?? 'Privacy Policy') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Privacy Policy URL</label>
                        <input type="text" name="footer_privacy_url" value="{{ old('footer_privacy_url',$s->footer_privacy_url) }}" placeholder="/privacy or https://..." class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Terms of Use Link Text</label>
                        <input type="text" name="footer_terms_text" value="{{ old('footer_terms_text',$s->footer_terms_text ?? 'Terms of Use') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Terms of Use URL</label>
                        <input type="text" name="footer_terms_url" value="{{ old('footer_terms_url',$s->footer_terms_url) }}" placeholder="/terms or https://..." class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Support Link Text</label>
                        <input type="text" name="footer_support_text" value="{{ old('footer_support_text',$s->footer_support_text ?? 'Support') }}" class="ws-input">
                    </div>
                    <div>
                        <label class="ws-label">Support URL</label>
                        <input type="text" name="footer_support_url" value="{{ old('footer_support_url',$s->footer_support_url) }}" placeholder="{{ route('website.contact') }}" class="ws-input">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Save Button --}}
<div class="flex gap-4 sticky bottom-0 bg-white/95 py-4 border-t border-slate-200 -mx-4 lg:-mx-6 px-4 lg:px-6" style="backdrop-filter:blur(8px)">
    <button type="submit" class="btn-gold px-8 py-3 rounded-xl text-sm font-bold shadow-sm">
        <i class="fas fa-save mr-2"></i>Save All Website Settings
    </button>
    <a href="{{ route('website.home') }}" target="_blank" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
        <i class="fas fa-external-link-alt mr-2"></i>View Website
    </a>
</div>

</form>

<style>
.ws-label { 
    display:block; 
    font-size:11px; 
    font-weight:700; 
    text-transform:uppercase; 
    letter-spacing:.04em; 
    color:#475569; 
    margin-bottom:5px; 
}
.ws-input { 
    width:100%; 
    padding:10px 14px; 
    border:1px solid #e2e8f0; 
    border-radius:10px; 
    font-size:13px; 
    background:#f8fafc; 
    transition:all .15s; 
}
.ws-input:focus { 
    outline:none; 
    border-color:#6366f1; 
    box-shadow:0 0 0 3px rgba(99,102,241,.12); 
    background:#fff; 
}
.ws-tab { 
    color:#64748b; 
}
.ws-tab.active { 
    background:#fff; 
    color:#111827; 
    box-shadow:0 1px 4px rgba(0,0,0,.1); 
    border-radius:.75rem; 
}
</style>

<script>
var WS_TABS = ['identity','hero','home','about','academics','admissions','contact','footer'];
function wsTab(id) {
    WS_TABS.forEach(t => {
        document.getElementById('ws-'+t)?.classList.toggle('hidden', t !== id);
        document.getElementById('wsbtn-'+t)?.classList.toggle('active', t === id);
    });
    localStorage.setItem('wsActiveTab', id);
}

// ── Hero background type switcher ─────────────────────────────────────────
function heroShowBgPanel(type) {
    document.querySelectorAll('.hero-bg-panel').forEach(p => p.classList.add('hidden'));
    const panel = document.getElementById('heroBg-' + type);
    if (panel) panel.classList.remove('hidden');
}

// ── Sync colour picker ↔ text input ──────────────────────────────────────
function syncColorInput(textInput) {
    const val = textInput.value.trim();
    const pickerName = textInput.dataset.picker;
    if (pickerName && /^#[0-9A-Fa-f]{6}$/.test(val)) {
        const picker = document.querySelector('[name="' + pickerName + '"]');
        if (picker) picker.value = val;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Sync all colour pickers → text inputs on picker change
    document.querySelectorAll('input[type="color"]').forEach(picker => {
        const textInput = picker.parentElement.querySelector('input[type="text"]');
        if (textInput) {
            picker.addEventListener('input', () => {
                textInput.value = picker.value;
                updateGradientPreview();
                updateWebsitePreview();
                updateAdminPreview();
            });
        }
    });

    // Init bg panel visibility
    const activeBgType = document.querySelector('input[name="hero_bg_type"]:checked')?.value || 'default';
    heroShowBgPanel(activeBgType);

    updateGradientPreview();
    updateWebsitePreview();
    updateAdminPreview();

    wsTab(localStorage.getItem('wsActiveTab') || 'identity');
});

// ── Gradient live preview ─────────────────────────────────────────────────
function updateGradientPreview() {
    const from  = document.querySelector('[name="hero_gradient_from"]')?.value || '#050d22';
    const via   = document.querySelector('[name="hero_gradient_via"]')?.value || '';
    const to    = document.querySelector('[name="hero_gradient_to"]')?.value || '#122859';
    const angle = document.querySelector('[name="hero_gradient_angle"]')?.value || 150;
    const viaStop = via && /^#[0-9A-Fa-f]{6}$/.test(via) ? ',' + via : '';
    const preview = document.getElementById('gradientPreview');
    if (preview) preview.style.background = `linear-gradient(${angle}deg,${from}${viaStop},${to})`;
}

// ── Website theme preview ─────────────────────────────────────────────────
function updateWebsitePreview() {
    const map = {
        'wt_primary':   'wtp-wt_primary',
        'wt_secondary': 'wtp-wt_secondary',
        'wt_accent':    'wtp-wt_accent',
        'wt_navBg':     'wtp-wt_navBg',
        'wt_bg':        'wtp-wt_bg',
    };
    Object.entries(map).forEach(([name, id]) => {
        const picker = document.querySelector('[name="' + name + '"]');
        const el = document.getElementById(id);
        if (picker && el) el.style.background = picker.value;
    });
}

// ── Admin theme preview ───────────────────────────────────────────────────
function updateAdminPreview() {
    const map = {
        'theme_adminNav':     'atp-theme_adminNav',
        'theme_adminPrimary': 'atp-theme_adminPrimary',
        'theme_accent':       'atp-theme_accent',
    };
    Object.entries(map).forEach(([name, id]) => {
        const picker = document.querySelector('[name="' + name + '"]');
        const el = document.getElementById(id);
        if (picker && el) el.style.background = picker.value;
    });
}

// ── Apply website preset ──────────────────────────────────────────────────
function applyWebsitePreset(primary, secondary, accent, text, bg, navBg) {
    const values = { wt_primary:primary, wt_secondary:secondary, wt_accent:accent, wt_text:text, wt_bg:bg, wt_navBg:navBg };
    Object.entries(values).forEach(([name, val]) => {
        const picker = document.querySelector('[name="' + name + '"]');
        const textInput = picker?.parentElement?.querySelector('input[type="text"]');
        if (picker) picker.value = val;
        if (textInput) textInput.value = val;
    });
    updateWebsitePreview();
}

// ── Apply admin preset ────────────────────────────────────────────────────
function applyAdminPreset(adminPrimary, adminNav, accent, primary) {
    const values = { theme_adminPrimary:adminPrimary, theme_adminNav:adminNav, theme_accent:accent, theme_primary:primary };
    Object.entries(values).forEach(([name, val]) => {
        const picker = document.querySelector('[name="' + name + '"]');
        const textInput = picker?.parentElement?.querySelector('input[type="text"]');
        if (picker) picker.value = val;
        if (textInput) textInput.value = val;
    });
    updateAdminPreview();
}
</script>

@endsection