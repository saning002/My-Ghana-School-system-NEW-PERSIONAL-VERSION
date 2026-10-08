@extends('layouts.app')
@section('title','Website Programs')
@section('subtitle','Programs shown on the public /academics page — add, edit, reorder, toggle visibility')

@section('content')
<div class="space-y-5">

@if(session('success'))
<div class="rounded-2xl bg-emerald-50 border border-emerald-200 px-5 py-3 text-sm font-semibold text-emerald-800 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i> {{ session('success') }}
</div>
@endif
@if($errors->has('error'))
<div class="rounded-2xl bg-red-50 border border-red-200 px-5 py-3 text-sm font-semibold text-red-800 flex items-center gap-2">
    <i class="fas fa-exclamation-circle text-red-500"></i> {{ $errors->first('error') }}
</div>
@endif

{{-- Header bar --}}
<div class="flex flex-wrap items-center justify-between gap-3">
    <div>
        <h2 class="text-sm font-extrabold text-slate-800">Academic Programs</h2>
        <p class="text-xs text-slate-500 mt-0.5">
            {{ $programs->count() }} program(s) configured ·
            <a href="{{ route('website.academics') }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">
                <i class="fas fa-external-link-alt text-[9px] mr-0.5"></i>View on website
            </a>
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        {{-- Sync from system button (only show if there are unsynced programs) --}}
        @if($systemPrograms->count() > 0)
        <form method="POST" action="{{ route('admin.website.programs.sync') }}">
            @csrf
            <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-2xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700 shadow active:scale-95 transition-all">
                <i class="fas fa-sync-alt text-xs"></i>
                Import {{ $systemPrograms->count() }} from System
            </button>
        </form>
        @endif
        <a href="{{ route('admin.website.programs.create') }}"
            class="btn-gold inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold shadow active:scale-95">
            <i class="fas fa-plus text-xs"></i> Add New Program
        </a>
    </div>
</div>

{{-- System programs not yet imported --}}
@if($systemPrograms->count() > 0)
<div class="rounded-2xl border border-amber-200 bg-amber-50 p-4">
    <div class="flex items-start gap-3 mb-3">
        <i class="fas fa-exclamation-triangle text-amber-500 text-lg mt-0.5 shrink-0"></i>
        <div>
            <p class="text-sm font-bold text-amber-900">{{ $systemPrograms->count() }} system program(s) not yet on the website</p>
            <p class="text-xs text-amber-700 mt-0.5">
                The following programs exist in your school system but haven't been set up as website cards yet.
                Click <strong>Import from System</strong> above to add them automatically, then edit each one to add a full description, image, curriculum details, and feature tags.
            </p>
        </div>
    </div>
    <div class="flex flex-wrap gap-2">
        @foreach($systemPrograms as $sp)
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-100 border border-amber-300 text-amber-800 text-xs font-bold">
            <i class="fas fa-graduation-cap text-[9px]"></i> {{ $sp->name }}
        </span>
        @endforeach
    </div>
</div>
@else
<div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 flex items-center gap-2">
    <i class="fas fa-check-circle text-emerald-500"></i>
    <p class="text-xs font-semibold text-emerald-700">All system programs are represented on the website.</p>
</div>
@endif

@if($programs->isEmpty())
<div class="card p-14 text-center">
    <i class="fas fa-graduation-cap text-5xl text-slate-200 mb-4 block"></i>
    <p class="text-slate-500 font-semibold text-sm mb-2">No programs added yet.</p>
    <p class="text-xs text-slate-400 mb-5">The academics page will show an empty-state message until you add programs.<br>Use <strong>Import from System</strong> to pull in your existing programs automatically, or add one manually.</p>
    <div class="flex gap-3 justify-center flex-wrap">
        @if($systemPrograms->count() > 0)
        <form method="POST" action="{{ route('admin.website.programs.sync') }}">
            @csrf
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold bg-emerald-600 text-white hover:bg-emerald-700">
                <i class="fas fa-sync-alt text-xs"></i> Import from System
            </button>
        </form>
        @endif
        <a href="{{ route('admin.website.programs.create') }}" class="btn-gold inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold">
            <i class="fas fa-plus text-xs"></i> Add Manually
        </a>
    </div>
</div>
@else

{{-- Program cards grid --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
    @foreach($programs as $prog)
    <div class="card overflow-hidden border {{ $prog->is_active ? 'border-slate-200' : 'border-slate-200 opacity-60' }} hover:shadow-md transition-shadow">

        {{-- Card header --}}
        <div class="flex items-stretch">
            {{-- Sidebar / image --}}
            <div class="w-28 flex-shrink-0 bg-gradient-to-br from-slate-800 to-slate-700 flex flex-col items-center justify-center p-3 relative">
                @if(!empty($prog->image_path))
                    <img src="{{ filter_var($prog->image_path, FILTER_VALIDATE_URL) ? $prog->image_path : \Illuminate\Support\Facades\Storage::url($prog->image_path) }}"
                         alt="{{ $prog->name }}"
                         class="w-full h-20 object-cover rounded-lg mb-2">
                @else
                    <span class="text-4xl mb-2">{{ $prog->icon ?? '🎓' }}</span>
                @endif
                <span class="text-white text-[10px] font-bold text-center leading-tight">{{ $prog->name }}</span>
                @if($prog->age_range && $prog->age_range !== 'All Ages')
                <span class="mt-1 text-[9px] text-yellow-300 font-semibold">{{ $prog->age_range }}</span>
                @endif
                {{-- Order badge --}}
                <span class="absolute top-1.5 left-1.5 w-5 h-5 rounded-full bg-white/20 text-white text-[9px] font-bold flex items-center justify-center">
                    {{ $prog->order }}
                </span>
            </div>

            {{-- Content --}}
            <div class="flex-1 p-4 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <div class="min-w-0">
                        <h3 class="text-sm font-extrabold text-slate-800 truncate">{{ $prog->name }}</h3>
                        <p class="text-[11px] text-slate-400 mt-0.5">
                            @if($prog->level)<span class="mr-2">{{ ucfirst(str_replace('_',' ',$prog->level)) }}</span>@endif
                            @if($prog->age_range && $prog->age_range !== 'All Ages')
                            <span class="text-amber-600 font-semibold">{{ $prog->age_range }}</span>
                            @endif
                        </p>
                    </div>
                    <span class="shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $prog->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                        {{ $prog->is_active ? 'Visible' : 'Hidden' }}
                    </span>
                </div>

                <p class="text-xs text-slate-600 line-clamp-2 mb-2">{{ Str::limit($prog->description, 120) }}</p>

                {{-- Feature tags --}}
                @if(!empty($prog->features) && count((array)$prog->features) > 0)
                <div class="flex flex-wrap gap-1 mb-2">
                    @foreach(array_slice((array)$prog->features, 0, 4) as $feat)
                    <span class="text-[9px] font-bold bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded-full">✓ {{ $feat }}</span>
                    @endforeach
                    @if(count((array)$prog->features) > 4)
                    <span class="text-[9px] text-slate-400">+{{ count((array)$prog->features) - 4 }} more</span>
                    @endif
                </div>
                @endif

                {{-- Info pills --}}
                <div class="flex flex-wrap gap-2 text-[10px] text-slate-500 font-semibold">
                    @if($prog->curriculum)
                    <span class="flex items-center gap-1"><i class="fas fa-book-open text-[8px] text-blue-400"></i> Curriculum set</span>
                    @endif
                    @if($prog->schedule)
                    <span class="flex items-center gap-1"><i class="fas fa-clock text-[8px] text-green-400"></i> Schedule set</span>
                    @endif
                    @if($prog->highlights)
                    <span class="flex items-center gap-1"><i class="fas fa-star text-[8px] text-amber-400"></i> Highlights set</span>
                    @endif
                    @if(!$prog->curriculum && !$prog->description)
                    <span class="flex items-center gap-1 text-red-400"><i class="fas fa-exclamation-circle text-[8px]"></i> Needs full details</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Action bar --}}
        <div class="flex items-center gap-2 px-4 py-2.5 bg-slate-50 border-t border-slate-100">
            <a href="{{ route('admin.website.programs.edit', $prog) }}"
               class="flex-1 flex items-center justify-center gap-1.5 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-600 text-xs font-bold hover:border-yellow-400 hover:text-yellow-700 hover:bg-yellow-50 transition-colors">
                <i class="fas fa-pen text-[9px]"></i> Edit Card Text & Details
            </a>

            {{-- Toggle visibility --}}
            <form method="POST" action="{{ route('admin.website.programs.update', $prog) }}">
                @csrf @method('PUT')
                <input type="hidden" name="name"        value="{{ $prog->name }}">
                <input type="hidden" name="description" value="{{ $prog->description }}">
                <input type="hidden" name="is_active"   value="{{ $prog->is_active ? 0 : 1 }}">
                <button type="submit"
                    class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-bold transition-colors {{ $prog->is_active ? 'bg-white border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-600 hover:border-red-300' : 'bg-white border-slate-200 text-slate-500 hover:bg-emerald-50 hover:text-emerald-600 hover:border-emerald-300' }}"
                    title="{{ $prog->is_active ? 'Hide from website' : 'Show on website' }}">
                    <i class="fas {{ $prog->is_active ? 'fa-eye-slash' : 'fa-eye' }} text-[9px]"></i>
                    {{ $prog->is_active ? 'Hide' : 'Show' }}
                </button>
            </form>

            <form method="POST" action="{{ route('admin.website.programs.destroy', $prog) }}"
                  onsubmit="return confirm('Delete {{ addslashes($prog->name) }}? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="flex items-center justify-center gap-1.5 px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-slate-400 text-xs font-bold hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition-colors">
                    <i class="fas fa-trash text-[9px]"></i>
                </button>
            </form>
        </div>
    </div>
    @endforeach
</div>

{{-- Link to site settings academics section --}}
<div class="rounded-2xl border border-indigo-200 bg-indigo-50 p-4 flex items-start gap-3">
    <i class="fas fa-cog text-indigo-500 text-lg mt-0.5 shrink-0"></i>
    <div>
        <p class="text-sm font-bold text-indigo-800 mb-1">Customize the Academics Page Text</p>
        <p class="text-xs text-indigo-600 mb-2">
            Change the hero title, section heading, card section labels (Curriculum, Highlights, Schedule), button texts, and the bottom enrollment banner from Website Settings → Academics Tab.
        </p>
        <a href="{{ route('admin.website.site-settings') }}"
           class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-700 bg-indigo-100 hover:bg-indigo-200 px-3 py-1.5 rounded-lg transition-colors">
            <i class="fas fa-external-link-alt text-[9px]"></i> Open Site Settings → Academics Tab
        </a>
    </div>
</div>

@endif
</div>
@endsection
