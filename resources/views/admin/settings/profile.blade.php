@extends('layouts.app')
@section('title', 'Settings')
@section('subtitle', 'Manage branding, appearance, permissions and grading')

@section('content')
@php
    $currentTheme = ['primary'=>'#e9a422','secondary'=>'#0a1f44','accent'=>'#0891b2','text'=>'#3a3830','bg'=>'#ffffff','navBg'=>'#0a1f44','adminPrimary'=>'#D4A017','adminNav'=>'#0B1121'];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('site_settings')) {
            $currentTheme = \App\Models\Website\SiteSetting::instance()->theme;
        }
    } catch (\Throwable $e) {}
    $defaultTheme = \App\Models\Website\SiteSetting::defaultTheme();
@endphp

{{-- ═══════════════════════════════════════════════════════════════
     FLASH BANNERS
═══════════════════════════════════════════════════════════════ --}}
@if(session('success'))
<div class="flex items-center gap-3 px-5 py-4 rounded-2xl bg-green-50 border border-green-200 text-green-800 text-sm font-semibold shadow-sm mb-6">
    <div class="w-7 h-7 rounded-full bg-green-500 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-check text-white text-xs"></i>
    </div>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flex items-center gap-3 px-5 py-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm font-semibold shadow-sm mb-6">
    <div class="w-7 h-7 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
        <i class="fas fa-times text-white text-xs"></i>
    </div>
    {{ session('error') }}
</div>
@endif
@if($errors->any())
<div class="px-5 py-4 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-sm shadow-sm mb-6">
    <p class="font-bold mb-1"><i class="fas fa-exclamation-triangle mr-2"></i>Please fix the following:</p>
    <ul class="list-disc list-inside space-y-0.5 text-xs mt-1">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     PAGE HEADER + TAB BAR
═══════════════════════════════════════════════════════════════ --}}
<div class="mb-6">
    <div class="flex items-center gap-4 mb-5">
        <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm flex-shrink-0"
             style="background:linear-gradient(135deg,#0B1121,#1a3a6b)">
            <i class="fas fa-sliders-h text-yellow-400 text-lg"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 leading-tight">System Settings</h1>
            <p class="text-sm text-gray-500 mt-0.5">Control every aspect of how the system looks and behaves</p>
        </div>
        <a href="{{ route('website.home') }}" target="_blank"
           class="ml-auto flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-white border border-gray-200 hover:border-gray-400 transition-all shadow-sm">
            <i class="fas fa-external-link-alt text-xs"></i> View Website
        </a>
    </div>

    {{-- Tab bar --}}
    <div class="flex gap-1 p-1 bg-gray-100 rounded-2xl overflow-x-auto" id="settingsTabs">
        @foreach([
            ['branding',     'fa-school',          'Branding &amp; Labels'],
            ['theme',        'fa-palette',          'Theme &amp; Colours'],
            ['grading',      'fa-graduation-cap',   'Grading Scale'],
            ['sba',          'fa-calculator',       'SBA &amp; Weights'],
            ['expectations', 'fa-bullseye',         'Academic Expectations'],
            ['permissions',  'fa-shield-halved',    'Teacher Permissions'],
            ['login-bg',     'fa-image',            'Login Background'],
        ] as [$id, $icon, $label])
        <button type="button"
                onclick="showTab('{{ $id }}')"
                id="tab-{{ $id }}"
                class="settings-tab flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all text-gray-500 hover:text-gray-800">
            <i class="fas {{ $icon }} text-xs"></i>
            {{ $label }}
        </button>
        @endforeach
    </div>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     TAB: BRANDING
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-branding" class="settings-panel space-y-5">
<form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
@csrf
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Left: Identity --}}
    <div class="xl:col-span-2 space-y-5">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100"
                 style="background:linear-gradient(135deg,#fef9c3,#fefce8)">
                <div class="w-8 h-8 rounded-xl bg-yellow-400 flex items-center justify-center">
                    <i class="fas fa-school text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-yellow-900">School Identity</h3>
                    <p class="text-xs text-yellow-700">Your school name and tagline appear everywhere</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                        Institution Name <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="school_name" value="{{ old('school_name', $schoolName) }}" required
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                           placeholder="e.g. Kingdom Ministerial University College">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">Tagline / Subtitle</label>
                    <input type="text" name="school_subtitle" value="{{ old('school_subtitle', $schoolSubtitle) }}"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all"
                           placeholder="e.g. Raising Kingdom Leaders for the Nations">
                    <p class="text-xs text-gray-400 mt-1.5">Shown in the browser tab and page headers throughout the system</p>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                        Student ID Prefix
                        <span class="ml-1 text-gray-400 font-normal normal-case">— 2–4 letters, used in all new student IDs</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <input type="text" name="school_id_prefix"
                               value="{{ old('school_id_prefix', \App\Models\Setting::get('school_id_prefix','FOC')) }}"
                               maxlength="4" placeholder="e.g. FOC"
                               class="w-28 px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-900 uppercase font-bold focus:outline-none focus:ring-2 focus:ring-yellow-400 transition-all"
                               oninput="this.value=this.value.toUpperCase().replace(/[^A-Z]/g,'')">
                        <span class="text-xs text-gray-500">
                            Preview: <strong class="text-gray-800">{{ \App\Models\Setting::get('school_id_prefix','FOC') }}/{{ date('Y') }}/0001</strong>
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">This prefix is permanent — changing it only affects new students, not existing ones.</p>
                </div>
            </div>
        </div>

        {{-- Sidebar labels --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100"
                 style="background:linear-gradient(135deg,#eff6ff,#f0f9ff)">
                <div class="w-8 h-8 rounded-xl bg-blue-500 flex items-center justify-center">
                    <i class="fas fa-bars text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-900">Sidebar Menu Labels</h3>
                    <p class="text-xs text-blue-600">Rename navigation items to match your school's terminology</p>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 gap-3">
                    @php
                    $labelFields = [
                        ['programs','fa-book-open','Programs'],['courses','fa-chalkboard-teacher','Courses'],
                        ['students','fa-user-graduate','Students'],['lecturers','fa-chalkboard-user','Lecturers'],
                        ['attendance','fa-calendar-check','Attendance'],['exams','fa-file-alt','Exams & Results'],
                        ['fees','fa-coins','Fees & Payments'],['reports','fa-chart-bar','Reports'],
                        ['portal','fa-globe','Student Portal'],['settings','fa-cog','Settings'],
                        ['guide','fa-question-circle','Guide'],
                    ];
                    @endphp
                    @foreach($labelFields as [$key,$icon,$label])
                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <div class="w-7 h-7 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 shadow-xs">
                            <i class="fas {{ $icon }} text-gray-400 text-xs"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-500 mb-1">{{ $label }}</p>
                            <input type="text" name="sidebar_{{ $key }}_label"
                                   value="{{ old('sidebar_'.$key.'_label', $sidebarLabels[$key]) }}"
                                   class="w-full px-2.5 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-blue-400 text-gray-800"
                                   placeholder="{{ $label }}">
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Logo --}}
    <div class="space-y-5">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100"
                 style="background:linear-gradient(135deg,#f0fdf4,#f7fee7)">
                <div class="w-8 h-8 rounded-xl bg-green-500 flex items-center justify-center">
                    <i class="fas fa-image text-white text-sm"></i>
                </div>
                <h3 class="text-sm font-bold text-green-900">School Logo</h3>
            </div>
            <div class="p-6">
                @if($siteLogoUrl)
                <div class="flex items-center gap-4 mb-4 p-4 bg-gray-50 rounded-xl border border-gray-200">
                    <img src="{{ Storage::disk('public')->url($siteLogoUrl) }}" alt="Logo"
                         class="w-16 h-16 object-contain rounded-xl bg-white border border-gray-200 p-1 shadow-sm">
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-gray-700">Current logo</p>
                        <p class="text-xs text-gray-400">Upload below to replace</p>
                    </div>
                </div>
                <label class="flex items-center gap-2 cursor-pointer mb-4">
                    <input type="checkbox" name="remove_site_logo" value="1" class="rounded text-red-500">
                    <span class="text-sm text-red-600 font-semibold">Remove current logo</span>
                </label>
                @else
                <div class="flex items-center justify-center h-24 mb-4 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
                    <div class="text-center">
                        <i class="fas fa-image text-gray-300 text-2xl block mb-1"></i>
                        <p class="text-xs text-gray-400">No logo uploaded yet</p>
                    </div>
                </div>
                @endif
                <label class="flex flex-col items-center gap-2 px-4 py-5 border-2 border-dashed border-gray-200 rounded-xl cursor-pointer hover:border-yellow-400 hover:bg-yellow-50 transition-all">
                    <i class="fas fa-cloud-upload-alt text-gray-400 text-xl"></i>
                    <span class="text-xs text-gray-500 text-center" id="logo-label">Click to upload<br><span class="text-gray-400">PNG, JPG, WebP — max 5 MB</span></span>
                    <input type="file" name="site_logo" accept="image/*" class="sr-only"
                           onchange="document.getElementById('logo-label').innerHTML = this.files[0]?.name ?? 'Click to upload'">
                </label>
            </div>
        </div>

        {{-- Save button --}}
        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5 active:translate-y-0"
                style="background:linear-gradient(135deg,#D4A017,#b8860e)">
            <i class="fas fa-save"></i> Save Branding Settings
        </button>
    </div>
</div>
</form>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     TAB: THEME & COLOURS
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-theme" class="settings-panel hidden">
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Left: Colour pickers --}}
    <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100"
             style="background:linear-gradient(135deg,#f5f3ff,#ede9fe)">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#7c3aed">
                <i class="fas fa-palette text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-purple-900">Colour Palette</h3>
                <p class="text-xs text-purple-600">Changes apply instantly to both the website and admin panel</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.theme.save') }}" id="themeForm">
        @csrf
        <div class="p-6 space-y-6">

            {{-- Live preview bar --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Live Preview</p>
                <div class="rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                    <div class="flex h-12">
                        @foreach(['secondary'=>'Website Navy','primary'=>'Gold / Primary','accent'=>'Accent','text'=>'Body Text','bg'=>'Page BG','adminNav'=>'Admin Nav','adminPrimary'=>'Admin Gold'] as $key=>$lbl)
                        <div class="flex-1 relative group" id="prev-{{ $key }}" style="background:{{ $currentTheme[$key] }}">
                            <div class="absolute inset-0 flex items-end justify-center pb-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <span class="text-white text-[9px] font-bold drop-shadow">{{ $lbl }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-1.5">Hover over each colour to see its label. Swatches update as you pick.</p>
            </div>

            {{-- Website colours --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <i class="fas fa-globe text-blue-400"></i> Website (Public Pages)
                </p>
                <div class="space-y-3">
                    @foreach([
                        ['theme_primary',   'primary',   'Primary / Gold',         'Buttons, CTA highlights, star accents'],
                        ['theme_secondary', 'secondary', 'Secondary / Navy',       'Hero backgrounds, dark sections, navbar'],
                        ['theme_accent',    'accent',    'Accent / Teal',          'Links, focus rings, student portal colour'],
                        ['theme_text',      'text',      'Body Text',              'Main paragraph and heading colour'],
                        ['theme_bg',        'bg',        'Page Background',        'Main page background'],
                        ['theme_navBg',     'navBg',     'Website Nav Background', 'Top navbar on the public website'],
                    ] as [$field, $key, $label, $hint])
                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <input type="color" name="{{ $field }}" id="{{ $field }}"
                               value="{{ old($field, $currentTheme[$key]) }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-gray-200 p-0.5 flex-shrink-0"
                               oninput="syncHex('{{ $field }}', this.value); updatePreview('{{ $key }}', this.value)">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800">{{ $label }}</p>
                            <p class="text-xs text-gray-400">{{ $hint }}</p>
                        </div>
                        <input type="text" id="{{ $field }}_hex"
                               value="{{ old($field, $currentTheme[$key]) }}"
                               maxlength="7" placeholder="#000000"
                               class="w-24 px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-mono text-center flex-shrink-0 focus:outline-none focus:border-purple-400"
                               oninput="syncPicker('{{ $field }}', this.value)">
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Admin colours --}}
            <div>
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                    <i class="fas fa-shield-halved text-purple-400"></i> Admin Panel
                </p>
                <div class="space-y-3">
                    @foreach([
                        ['theme_adminNav',     'adminNav',     'Admin Sidebar',      'Background colour of the left sidebar'],
                        ['theme_adminPrimary', 'adminPrimary', 'Admin Primary Gold', 'Buttons, active nav items, gold accents'],
                    ] as [$field, $key, $label, $hint])
                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-xl border border-gray-100">
                        <input type="color" name="{{ $field }}" id="{{ $field }}"
                               value="{{ old($field, $currentTheme[$key]) }}"
                               class="w-12 h-10 rounded-xl cursor-pointer border border-gray-200 p-0.5 flex-shrink-0"
                               oninput="syncHex('{{ $field }}', this.value); updatePreview('{{ $key }}', this.value)">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800">{{ $label }}</p>
                            <p class="text-xs text-gray-400">{{ $hint }}</p>
                        </div>
                        <input type="text" id="{{ $field }}_hex"
                               value="{{ old($field, $currentTheme[$key]) }}"
                               maxlength="7" placeholder="#000000"
                               class="w-24 px-3 py-1.5 border border-gray-200 rounded-lg text-xs font-mono text-center flex-shrink-0 focus:outline-none focus:border-purple-400"
                               oninput="syncPicker('{{ $field }}', this.value)">
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-gray-100">
                <button type="submit"
                        class="flex items-center gap-2 px-7 py-3 rounded-xl text-sm font-bold text-white shadow-sm transition-all hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,#7c3aed,#5b21b6)">
                    <i class="fas fa-paint-brush"></i> Apply Theme
                </button>
            </div>
        </div>
        </form>

        <div class="px-6 pb-5">
            <form method="POST" action="{{ route('admin.theme.reset') }}" onsubmit="return confirm('Reset to the default navy & gold palette?')">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-red-500 font-semibold transition-colors">
                    <i class="fas fa-undo mr-1"></i>Reset to default navy &amp; gold
                </button>
            </form>
        </div>
    </div>

    {{-- Right: Presets --}}
    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden self-start">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100" style="background:#fafafa">
            <div class="w-8 h-8 rounded-xl bg-gray-800 flex items-center justify-center">
                <i class="fas fa-swatchbook text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-gray-800">Quick Presets</h3>
                <p class="text-xs text-gray-500">One click to apply a full palette</p>
            </div>
        </div>
        <div class="p-4 space-y-2">
            @php
            $presets = [
                ['Navy & Gold (Default)', '#0a1f44','#e9a422','#0891b2','#3a3830','#ffffff','#0a1f44','#0B1121','#D4A017'],
                ['Royal Blue & Gold',     '#1e3a8a','#f59e0b','#06b6d4','#1e293b','#f8fafc','#1e3a8a','#172554','#f59e0b'],
                ['Forest Green & Amber',  '#14532d','#d97706','#0891b2','#1c2916','#f9fafb','#14532d','#052e16','#d97706'],
                ['Deep Purple & Gold',    '#4c1d95','#f59e0b','#0891b2','#1e1b4b','#fafaf9','#4c1d95','#2e1065','#f59e0b'],
                ['Crimson & Gold',        '#7f1d1d','#d97706','#0891b2','#1c1110','#fffbf9','#7f1d1d','#450a0a','#d97706'],
                ['Slate & Orange',        '#1e293b','#ea580c','#0891b2','#334155','#f8fafc','#1e293b','#0f172a','#ea580c'],
                ['Emerald & Gold',        '#064e3b','#f59e0b','#0891b2','#1c2e26','#f0fdf4','#064e3b','#022c22','#f59e0b'],
                ['Midnight & Cyan',       '#111827','#06b6d4','#a855f7','#1f2937','#f9fafb','#111827','#030712','#06b6d4'],
            ];
            @endphp
            @foreach($presets as [$name,$sec,$pri,$acc,$txt,$bg,$anav,$apri])
            <button type="button"
                    onclick="applyPreset('{{$sec}}','{{$pri}}','{{$acc}}','{{$txt}}','{{$bg}}','{{$sec}}','{{$anav}}','{{$apri}}')"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-xl border border-gray-100 bg-gray-50 hover:border-purple-300 hover:bg-purple-50 transition-all text-left group">
                <span class="flex gap-1 flex-shrink-0">
                    <span class="w-5 h-5 rounded-md shadow-sm border border-white/50" style="background:{{$sec}}"></span>
                    <span class="w-5 h-5 rounded-md shadow-sm border border-white/50" style="background:{{$pri}}"></span>
                    <span class="w-5 h-5 rounded-md shadow-sm border border-white/50" style="background:{{$acc}}"></span>
                </span>
                <span class="text-xs font-semibold text-gray-700 group-hover:text-purple-700 truncate">{{ $name }}</span>
                <i class="fas fa-arrow-right text-gray-300 group-hover:text-purple-400 text-xs ml-auto flex-shrink-0 transition-colors"></i>
            </button>
            @endforeach
        </div>
    </div>
</div>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     TAB: GRADING SCALE
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-grading" class="settings-panel hidden">
<form method="POST" action="{{ route('admin.settings.update-grading') }}">
@csrf

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#f0fdf4,#f7fee7)">
            <div class="w-8 h-8 rounded-xl bg-green-600 flex items-center justify-center">
                <i class="fas fa-graduation-cap text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-green-900">Grading Scale</h3>
                <p class="text-xs text-green-700">Choose the Ghana Standard or define your own custom scale</p>
            </div>
        </div>
        <div class="p-6 space-y-5">
            <div class="grid grid-cols-2 gap-3">
                <label class="cursor-pointer relative">
                    <input type="radio" name="grading_preset" value="ghana_standard"
                           {{ $gradingPreset === 'ghana_standard' ? 'checked' : '' }}
                           onchange="toggleGradingEditor()" class="sr-only peer">
                    <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-green-500 peer-checked:bg-green-50 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-green-100 flex items-center justify-center">
                                <i class="fas fa-flag text-green-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Ghana Standard</p>
                                <p class="text-xs text-gray-500">A1–F national scale</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute top-3 right-3 w-4 h-4 rounded-full border-2 border-gray-300 peer-checked:border-green-500 peer-checked:bg-green-500 hidden peer-checked:flex items-center justify-center">
                        <i class="fas fa-check text-white" style="font-size:8px"></i>
                    </div>
                </label>
                <label class="cursor-pointer relative">
                    <input type="radio" name="grading_preset" value="custom"
                           {{ $gradingPreset === 'custom' ? 'checked' : '' }}
                           onchange="toggleGradingEditor()" class="sr-only peer">
                    <div class="p-4 rounded-xl border-2 border-gray-200 peer-checked:border-blue-500 peer-checked:bg-blue-50 transition-all">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center">
                                <i class="fas fa-sliders-h text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Custom Scale</p>
                                <p class="text-xs text-gray-500">Define your own grades</p>
                            </div>
                        </div>
                    </div>
                </label>
            </div>

            {{-- Ghana standard preview --}}
            <div id="grading-preview" class="{{ $gradingPreset === 'custom' ? 'hidden' : '' }}">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide mb-2">Ghana Standard Scale</p>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="px-4 py-3 text-left font-semibold">Grade</th>
                            <th class="px-4 py-3 text-left font-semibold">Min Score</th>
                            <th class="px-4 py-3 text-left font-semibold">Points</th>
                            <th class="px-4 py-3 text-left font-semibold">Description</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach([['A1','80%','4.0','DISTINCTION'],['A2','70%','3.7','UPPER DIVISION'],['A3','60%','3.3','LOWER DIVISION'],['B1','50%','3.0','CREDIT'],['B2','40%','2.0','PASS'],['F','0%','0.0','FAIL']] as $row)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-bold text-gray-900">{{ $row[0] }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $row[1] }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $row[2] }}</td>
                                <td class="px-4 py-3"><span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">{{ $row[3] }}</span></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Custom editor --}}
            <div id="grading-editor" class="{{ $gradingPreset === 'custom' ? '' : 'hidden' }}">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Custom Scale <span class="text-gray-400 font-normal normal-case">(highest to lowest)</span></p>
                    <button type="button" onclick="addGradeRow()"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 border border-blue-200 transition-colors">
                        <i class="fas fa-plus text-xs"></i> Add Row
                    </button>
                </div>
                <div class="overflow-x-auto rounded-xl border border-gray-200">
                    <table class="w-full text-sm">
                        <thead><tr class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <th class="px-3 py-3 text-left font-semibold">Grade</th>
                            <th class="px-3 py-3 text-left font-semibold">Min %</th>
                            <th class="px-3 py-3 text-left font-semibold">Points</th>
                            <th class="px-3 py-3 text-left font-semibold">Description</th>
                            <th class="px-3 py-3 w-10"></th>
                        </tr></thead>
                        <tbody id="grading-rows" class="divide-y divide-gray-100"></tbody>
                    </table>
                </div>
                <textarea name="grading_scale_json" id="grading_scale_json" class="sr-only">{{ $gradingScaleJson }}</textarea>
            </div>
        </div>
    </div>

    <div class="self-start space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-700 mb-3">About Grading</h4>
            <p class="text-xs text-gray-500 leading-relaxed">The grading scale controls how exam scores are converted to letter grades and grade points on student report cards.</p>
            <div class="mt-3 p-3 bg-yellow-50 rounded-xl border border-yellow-200">
                <p class="text-xs text-yellow-800 font-semibold"><i class="fas fa-lightbulb mr-1"></i>Tip</p>
                <p class="text-xs text-yellow-700 mt-1">The Ghana Standard scale is recommended for schools following the national curriculum. Switch to Custom only if your institution uses a different marking system.</p>
            </div>
        </div>
        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5"
                style="background:linear-gradient(135deg,#16a34a,#15803d)">
            <i class="fas fa-save"></i> Save Grading Scale
        </button>
    </div>
</div>
</form>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     TAB: SBA WEIGHTS & SCORE SETTINGS
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-sba" class="settings-panel hidden">
<form method="POST" action="{{ route('admin.settings.update-sba') }}">
@csrf
@php
    $sbaWeights = \App\Services\ReportCardService::getSbaSubWeights();
    $sbaLabels  = \App\Services\ReportCardService::getSbaSubLabels();
    $totalSubW  = \App\Services\ReportCardService::getTotalSubWeight();
    $quizPct    = (float)\App\Models\Setting::get('quiz_percentage', 50);
    $examPct    = (float)\App\Models\Setting::get('exam_percentage', 50);
@endphp
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 space-y-5">

        {{-- Class Score vs Exam weighting --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#eff6ff,#f0f9ff)">
                <div class="w-8 h-8 rounded-xl bg-blue-600 flex items-center justify-center">
                    <i class="fas fa-percent text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-blue-900">Class Score vs Exam Weighting</h3>
                    <p class="text-xs text-blue-600">How the final grade is split between class assessment and the exam</p>
                </div>
            </div>
            <div class="p-6 grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                        Class Score % <span class="text-gray-400 normal-case font-normal">(SBA contribution)</span>
                    </label>
                    <input type="number" name="quiz_percentage" value="{{ old('quiz_percentage', $quizPct) }}"
                           min="0" max="100" step="1"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wide mb-2">
                        Exam Score % <span class="text-gray-400 normal-case font-normal">(exam contribution)</span>
                    </label>
                    <input type="number" name="exam_percentage" value="{{ old('exam_percentage', $examPct) }}"
                           min="0" max="100" step="1"
                           class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>
                <div class="col-span-2">
                    <p class="text-xs text-gray-400">
                        <i class="fas fa-info-circle text-blue-400 mr-1"></i>
                        These two values must add up to 100. Example: Class Score 50% + Exam 50%.
                        Current total: <strong id="pct-total">{{ $quizPct + $examPct }}</strong>%
                    </p>
                </div>
            </div>
        </div>

        {{-- SBA sub-component weights --}}
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100" style="background:linear-gradient(135deg,#fdf4ff,#f5f3ff)">
                <div class="w-8 h-8 rounded-xl bg-purple-600 flex items-center justify-center">
                    <i class="fas fa-calculator text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-purple-900">SBA Sub-Score Components</h3>
                    <p class="text-xs text-purple-600">Set the max mark for each component. Total sub-weight becomes the denominator for the Class Score formula.</p>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @foreach([
                    ['test1',     'Test 1'],
                    ['groupwork', 'Group Work'],
                    ['test2',     'Test 2'],
                    ['project',   'Project Work'],
                ] as [$key, $defaultLabel])
                <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Component Name</label>
                        <input type="text" name="sba_{{ $key }}_label"
                               value="{{ old('sba_'.$key.'_label', $sbaLabels[$key]) }}"
                               placeholder="{{ $defaultLabel }}"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-400">
                    </div>
                    <div class="w-32 shrink-0">
                        <label class="block text-xs font-bold text-gray-600 mb-1.5">Max Mark (weight)</label>
                        <input type="number" name="sba_{{ $key }}_weight"
                               value="{{ old('sba_'.$key.'_weight', $sbaWeights[$key]) }}"
                               min="0" max="200" step="0.5"
                               class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-purple-400 text-center">
                    </div>
                </div>
                @endforeach

                <div class="flex items-center justify-between px-4 py-3 bg-indigo-50 rounded-xl border border-indigo-100">
                    <span class="text-xs font-bold text-indigo-700">Total sub-weight (denominator for Class Score)</span>
                    <span class="text-base font-extrabold text-indigo-900">{{ $totalSubW }}</span>
                </div>

                <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                    <p class="text-xs text-amber-800 leading-relaxed">
                        <i class="fas fa-lightbulb mr-1 text-amber-500"></i>
                        <strong>Formula:</strong> Class Score = (sum of student's sub-scores ÷ total sub-weight) × Class Score %.
                        E.g. student scores 36/60 total, Class Score% = 50 → Class Score = 36/60 × 50 = 30.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="self-start space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-700 mb-2">Current Summary</h4>
            <div class="space-y-2 text-xs">
                @foreach($sbaLabels as $k => $lbl)
                <div class="flex justify-between">
                    <span class="text-gray-600 font-semibold">{{ $lbl }}</span>
                    <span class="font-bold text-gray-800">max {{ $sbaWeights[$k] }}</span>
                </div>
                @endforeach
                <div class="border-t border-gray-100 pt-2 flex justify-between">
                    <span class="font-bold text-gray-700">Total sub-weight</span>
                    <span class="font-extrabold text-indigo-700">{{ $totalSubW }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-gray-700">Class Score %</span>
                    <span class="font-extrabold text-blue-700">{{ $quizPct }}%</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-bold text-gray-700">Exam %</span>
                    <span class="font-extrabold text-purple-700">{{ $examPct }}%</span>
                </div>
            </div>
        </div>
        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5"
                style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">
            <i class="fas fa-save"></i> Save SBA Settings
        </button>
    </div>
</div>
</form>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     TAB: ACADEMIC EXPECTATIONS
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-expectations" class="settings-panel hidden">
    <form method="POST" action="{{ route('admin.settings.update-academic-expectations') }}">
        @csrf
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden mb-5">
            <div class="p-5 border-b border-gray-100 bg-gray-50/50">
                <h3 class="text-sm font-bold text-gray-800">Teacher Work Expectations</h3>
                <p class="text-xs text-gray-500 mt-1">Set the target number of classworks, homeworks, and tests expected from each teacher per subject.</p>
            </div>
            <div class="p-5 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Classworks Expected</label>
                        <input type="number" name="expected_classworks" value="{{ \App\Models\Setting::get('expected_classworks', 4) }}" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Homeworks Expected</label>
                        <input type="number" name="expected_homeworks" value="{{ \App\Models\Setting::get('expected_homeworks', 4) }}" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-[11px] font-bold text-gray-500 uppercase tracking-wider mb-2">Monthly Tests Expected</label>
                        <input type="number" name="expected_tests" value="{{ \App\Models\Setting::get('expected_tests', 1) }}" min="0" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-500 transition-colors">
                    </div>
                </div>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" class="px-5 py-2.5 bg-gray-900 hover:bg-black text-white text-sm font-bold rounded-xl transition-all shadow-sm hover:shadow active:scale-95 flex items-center gap-2">
                <i class="fas fa-save text-xs"></i> Save Expectations
            </button>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TAB: TEACHER PERMISSIONS
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-permissions" class="settings-panel hidden">
<form method="POST" action="{{ route('admin.settings.update-permissions') }}">
@csrf
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100"
             style="background:linear-gradient(135deg,#fdf4ff,#f5f3ff)">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#a855f7">
                <i class="fas fa-chalkboard-user text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-purple-900">Teacher Portal Permissions</h3>
                <p class="text-xs text-purple-600">Toggle which features teachers can access. Changes take effect immediately.</p>
            </div>
        </div>
        <div class="p-6 space-y-3">
            @php
            $perms = [
                ['lecturer_can_attendance',   'attendance',   'fa-calendar-check', '#10b981','#d1fae5', 'Record Attendance',     'Allow teachers to mark students present or absent'],
                ['lecturer_can_exams',        'exams',        'fa-file-signature', '#3b82f6','#dbeafe', 'Enter Exam Scores',      'Allow teachers to submit SBA and exam scores'],
                ['lecturer_can_reports',      'reports',      'fa-chart-bar',      '#f59e0b','#fef3c7', 'View Student Reports',   'Allow teachers to access report cards and summaries'],
                ['lecturer_can_notifications','notifications','fa-bell',           '#f43f5e','#ffe4e6', 'Send Notifications',     'Allow teachers to send messages and alerts to students'],
                ['lecturer_can_edit_profile', 'edit_profile', 'fa-user-edit',      '#64748b','#f1f5f9', 'Edit Own Profile',       'Allow teachers to update their photo, details and password'],
            ];
            @endphp
            @foreach($perms as [$key, $perm, $icon, $color, $bg, $label, $desc])
            @php $on = old($key, $lecturerPermissions[$perm] ?? true); @endphp
            <label id="perm-label-{{ $perm }}"
                   class="flex items-center gap-5 p-4 rounded-2xl border-2 cursor-pointer select-none transition-all {{ $on ? 'border-purple-200 bg-purple-50' : 'border-gray-100 bg-gray-50' }}">
                <input type="checkbox" name="{{ $key }}" value="1"
                       {{ $on ? 'checked' : '' }}
                       class="sr-only peer"
                       onchange="updatePermCard(this,'{{ $perm }}')">
                {{-- Icon --}}
                <div id="perm-icon-{{ $perm }}"
                     class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 transition-all"
                     style="background:{{ $on ? $bg : '#f1f5f9' }}">
                    <i id="perm-icon-i-{{ $perm }}"
                       class="fas {{ $icon }} text-sm transition-colors"
                       style="color:{{ $on ? $color : '#94a3b8' }}"></i>
                </div>
                {{-- Text --}}
                <div class="flex-1">
                    <p class="text-sm font-bold text-gray-900">{{ $label }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $desc }}</p>
                </div>
                {{-- Toggle pill --}}
                <div id="perm-toggle-{{ $perm }}"
                     class="w-12 h-6 rounded-full relative flex items-center flex-shrink-0 transition-colors"
                     style="background:{{ $on ? '#a855f7' : '#e2e8f0' }}">
                    <div id="perm-dot-{{ $perm }}"
                         class="w-5 h-5 bg-white rounded-full shadow absolute transition-all"
                         style="{{ $on ? 'left:calc(100% - 22px)' : 'left:2px' }}"></div>
                </div>
            </label>
            @endforeach

            <div class="flex items-start gap-3 mt-4 p-3 bg-amber-50 rounded-xl border border-amber-200">
                <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                <p class="text-xs text-amber-700 leading-relaxed">Changes take effect immediately. Teachers who are currently logged in will be blocked on their next request to a disabled feature. The Dashboard is always accessible.</p>
            </div>
        </div>
    </div>
    <div class="self-start space-y-4">
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
            <h4 class="text-sm font-bold text-gray-700 mb-3">Quick Actions</h4>
            <div class="space-y-2">
                <button type="button" onclick="setAllPerms(true)"
                        class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-green-700 bg-green-50 hover:bg-green-100 border border-green-200 transition-colors">
                    <i class="fas fa-unlock"></i> Enable All Permissions
                </button>
                <button type="button" onclick="setAllPerms(false)"
                        class="w-full flex items-center gap-2 px-4 py-2.5 rounded-xl text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors">
                    <i class="fas fa-lock"></i> Disable All Permissions
                </button>
            </div>
        </div>
        <button type="submit"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl text-sm font-bold text-white shadow-lg transition-all hover:shadow-xl hover:-translate-y-0.5"
                style="background:linear-gradient(135deg,#a855f7,#7c3aed)">
            <i class="fas fa-save"></i> Save Permissions
        </button>
    </div>
</div>
</form>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TAB: LOGIN BACKGROUND
═══════════════════════════════════════════════════════════════ --}}
<div id="panel-login-bg" class="settings-panel hidden">
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="flex items-center gap-3 px-6 py-4 border-b border-gray-100"
             style="background:linear-gradient(135deg,#faf5ff,#f5f3ff)">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center" style="background:#9333ea">
                <i class="fas fa-image text-white text-sm"></i>
            </div>
            <div>
                <h3 class="text-sm font-bold text-purple-900">Admin Login Page Background</h3>
                <p class="text-xs text-purple-600">Customise the background image shown on your admin login screen</p>
            </div>
        </div>
        <div class="p-6">
            @if($admin->login_background_url)
            @php
            $bgUrl = filter_var($admin->login_background_url, FILTER_VALIDATE_URL)
                ? $admin->login_background_url
                : Storage::disk('public')->url($admin->login_background_url);
            @endphp
            <div class="mb-5 rounded-2xl overflow-hidden border border-gray-200 relative group shadow-sm" style="height:220px">
                <img src="{{ $bgUrl }}" alt="Login background" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <span class="text-white text-sm font-bold px-4 py-2 bg-black/30 rounded-xl backdrop-blur-sm">Current Background</span>
                </div>
                <div class="absolute top-3 right-3">
                    <span class="px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full shadow-sm">Active</span>
                </div>
            </div>
            @else
            <div class="mb-5 flex items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50" style="height:160px">
                <div class="text-center">
                    <i class="fas fa-image text-gray-300 text-3xl block mb-2"></i>
                    <p class="text-sm font-semibold text-gray-400">No background image set</p>
                    <p class="text-xs text-gray-400 mt-1">A default background is used on the login page</p>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update-background') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <label class="flex items-center gap-4 px-5 py-4 border-2 border-dashed border-gray-200 rounded-2xl cursor-pointer hover:border-purple-400 hover:bg-purple-50 transition-all">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-cloud-upload-alt text-purple-600"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-bold text-gray-700" id="bg-label-text">
                            {{ $admin->login_background_url ? 'Upload a new background image' : 'Choose a background image' }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">JPEG, PNG, WebP — max 10 MB. Recommended: 1920×1080px</p>
                    </div>
                    <input type="file" name="login_background" accept="image/*" class="sr-only"
                           onchange="document.getElementById('bg-label-text').textContent = this.files[0]?.name ?? 'Choose a background image'">
                </label>
                <div class="flex gap-3">
                    <button type="submit"
                            class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold text-white shadow-sm transition-all hover:-translate-y-0.5"
                            style="background:linear-gradient(135deg,#9333ea,#7c3aed)">
                        <i class="fas fa-upload"></i>
                        {{ $admin->login_background_url ? 'Replace Background' : 'Upload Background' }}
                    </button>
                    @if($admin->login_background_url)
                    <button type="submit" name="remove_background" value="1"
                            class="px-5 py-3 rounded-xl text-sm font-bold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors"
                            onclick="return confirm('Remove the login background image?')">
                        <i class="fas fa-trash"></i>
                    </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="self-start bg-white rounded-2xl border border-gray-200 shadow-sm p-5">
        <h4 class="text-sm font-bold text-gray-700 mb-3">Tips for Best Results</h4>
        <div class="space-y-3">
            @foreach(['Use a high-resolution image (1920×1080px or larger) for a sharp result on all screens.','Dark or blurred images work best as they allow the login form to remain readable.','Avoid images with text or important focal points near the right side, as the login card overlays that area.','JPG images are recommended for photos to keep file sizes small.'] as $tip)
            <div class="flex items-start gap-2.5">
                <div class="w-5 h-5 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <i class="fas fa-check text-purple-600" style="font-size:9px"></i>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed">{{ $tip }}</p>
            </div>
            @endforeach
        </div>
    </div>
</div>
</div>


{{-- ═══════════════════════════════════════════════════════════════
     SCRIPTS
═══════════════════════════════════════════════════════════════ --}}
<style>
.settings-tab { color:#6b7280; }
.settings-tab.active {
    background:#ffffff;
    color:#111827;
    box-shadow:0 1px 4px rgba(0,0,0,.10),0 0 0 1px rgba(0,0,0,.04);
}
.settings-tab:hover:not(.active) { color:#374151; }
</style>

<script>
/* ── Tab switching ───────────────────────────────────────────────── */
var TABS = ['branding','theme','grading','sba','expectations','permissions','login-bg'];
function showTab(id) {
    TABS.forEach(function(t) {
        var panel = document.getElementById('panel-' + t);
        var btn   = document.getElementById('tab-'   + t);
        if (!panel || !btn) return;
        if (t === id) {
            panel.classList.remove('hidden');
            btn.classList.add('active');
        } else {
            panel.classList.add('hidden');
            btn.classList.remove('active');
        }
    });
    try { localStorage.setItem('settingsTab', id); } catch(e) {}
}
// Restore last tab or default to branding
(function() {
    var saved = '';
    try { saved = localStorage.getItem('settingsTab') || ''; } catch(e) {}
    // If URL has a hash, use that
    var hash = (window.location.hash || '').replace('#', '');
    showTab(TABS.indexOf(hash) !== -1 ? hash : (TABS.indexOf(saved) !== -1 ? saved : 'branding'));
})();

/* ── Grading scale editor ────────────────────────────────────────── */
function toggleGradingEditor() {
    var isCustom = document.querySelector('input[name="grading_preset"]:checked')?.value === 'custom';
    document.getElementById('grading-editor').classList.toggle('hidden', !isCustom);
    document.getElementById('grading-preview').classList.toggle('hidden', isCustom);
}
function escH(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function getRows() {
    try { return JSON.parse(document.getElementById('grading_scale_json')?.value)||[]; } catch(e) { return []; }
}
function saveRows(rows) {
    document.getElementById('grading_scale_json').value = JSON.stringify(rows);
    renderRows(rows);
}
function renderRows(rows) {
    var tb = document.getElementById('grading-rows');
    if (!tb) return;
    tb.innerHTML = '';
    rows.forEach(function(row, i) {
        var tr = document.createElement('tr');
        tr.className = 'hover:bg-gray-50';
        tr.innerHTML =
            '<td class="px-3 py-2"><input type="text" value="'+escH(row.grade)+'" class="w-20 px-2 py-1.5 border border-gray-200 rounded-lg text-xs font-mono focus:outline-none focus:ring-1 focus:ring-green-400" onchange="updateRow('+i+',\'grade\',this.value)"></td>'+
            '<td class="px-3 py-2"><input type="number" min="0" max="100" value="'+row.min+'" class="w-20 px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-green-400" onchange="updateRow('+i+',\'min\',parseFloat(this.value))"></td>'+
            '<td class="px-3 py-2"><input type="number" min="0" max="4" step="0.1" value="'+row.point+'" class="w-20 px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-green-400" onchange="updateRow('+i+',\'point\',parseFloat(this.value))"></td>'+
            '<td class="px-3 py-2"><input type="text" value="'+escH(row.description??'')+'" class="w-full px-2 py-1.5 border border-gray-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-green-400" onchange="updateRow('+i+',\'description\',this.value)"></td>'+
            '<td class="px-3 py-2 text-center"><button type="button" onclick="removeRow('+i+')" class="w-7 h-7 rounded-lg flex items-center justify-center mx-auto text-red-400 hover:text-white hover:bg-red-500 transition-colors"><i class="fas fa-times text-xs"></i></button></td>';
        tb.appendChild(tr);
    });
}
window.updateRow = function(i, f, v) { var r=getRows(); r[i][f]=v; document.getElementById('grading_scale_json').value=JSON.stringify(r); };
window.removeRow = function(i) { var r=getRows(); r.splice(i,1); saveRows(r); };
window.addGradeRow = function() { var r=getRows(); r.push({grade:'',min:0,point:0.0,description:''}); saveRows(r); };
(function() { var r=getRows(); if(r.length) renderRows(r); })();

/* ── Permission toggle cards ─────────────────────────────────────── */
var permColors = {
    attendance:    {bg:'#d1fae5',icon:'#10b981'},
    exams:         {bg:'#dbeafe',icon:'#3b82f6'},
    reports:       {bg:'#fef3c7',icon:'#f59e0b'},
    notifications: {bg:'#ffe4e6',icon:'#f43f5e'},
    edit_profile:  {bg:'#f1f5f9',icon:'#64748b'},
};
window.updatePermCard = function(cb, key) {
    var on=cb.checked, c=permColors[key]||{bg:'#f1f5f9',icon:'#94a3b8'};
    var lbl=document.getElementById('perm-label-'+key);
    var ico=document.getElementById('perm-icon-'+key);
    var ici=document.getElementById('perm-icon-i-'+key);
    var tog=document.getElementById('perm-toggle-'+key);
    var dot=document.getElementById('perm-dot-'+key);
    if(lbl){lbl.classList.toggle('border-purple-200',on);lbl.classList.toggle('bg-purple-50',on);lbl.classList.toggle('border-gray-100',!on);lbl.classList.toggle('bg-gray-50',!on);}
    if(ico) ico.style.background = on ? c.bg : '#f1f5f9';
    if(ici) ici.style.color      = on ? c.icon : '#94a3b8';
    if(tog) tog.style.background = on ? '#a855f7' : '#e2e8f0';
    if(dot) dot.style.left       = on ? 'calc(100% - 22px)' : '2px';
};
window.setAllPerms = function(state) {
    ['attendance','exams','reports','notifications','edit_profile'].forEach(function(key) {
        var cb = document.querySelector('input[name="lecturer_can_'+key+'"]') ||
                 document.querySelector('input[name="lecturer_can_edit_profile"]');
        var realCb = document.querySelector('[id^="perm-label-'+key+'"] input');
        if (!realCb) return;
        realCb.checked = state;
        updatePermCard(realCb, key);
    });
};

/* ── Theme colour picker ─────────────────────────────────────────── */
window.syncHex = function(field, hex) {
    var t=document.getElementById(field+'_hex'); if(t) t.value=hex;
};
window.syncPicker = function(field, hex) {
    if(!/^#[0-9A-Fa-f]{6}$/.test(hex)) return;
    var p=document.getElementById(field); if(p) p.value=hex;
    var keyMap = {theme_primary:'primary',theme_secondary:'secondary',theme_accent:'accent',
                  theme_text:'text',theme_bg:'bg',theme_navBg:'secondary',
                  theme_adminNav:'adminNav',theme_adminPrimary:'adminPrimary'};
    if(keyMap[field]) updatePreview(keyMap[field], hex);
};
window.updatePreview = function(key, hex) {
    var el=document.getElementById('prev-'+key); if(el) el.style.background=hex;
};
window.applyPreset = function(sec,pri,acc,txt,bg,nav,anav,apri) {
    var map = {theme_secondary:sec,theme_primary:pri,theme_accent:acc,
               theme_text:txt,theme_bg:bg,theme_navBg:nav,
               theme_adminNav:anav,theme_adminPrimary:apri};
    var previewMap = {theme_secondary:'secondary',theme_primary:'primary',theme_accent:'accent',
                      theme_text:'text',theme_bg:'bg',theme_navBg:'secondary',
                      theme_adminNav:'adminNav',theme_adminPrimary:'adminPrimary'};
    Object.keys(map).forEach(function(f) {
        var p=document.getElementById(f); if(p) p.value=map[f];
        var h=document.getElementById(f+'_hex'); if(h) h.value=map[f];
        if(previewMap[f]) updatePreview(previewMap[f], map[f]);
    });
    // Smooth flash to indicate preset applied
    var bar=document.querySelector('#prev-secondary')?.parentElement;
    if(bar){bar.style.transition='opacity .15s';bar.style.opacity='0.5';setTimeout(function(){bar.style.opacity='1';},150);}
};
</script>

@endsection
