@extends('layouts.app')
@section('title', $program ? 'Edit Program Card' : 'Add Program Card')
@section('subtitle', 'This card will appear on the public website Academics page')

@section('content')
<div class="max-w-3xl space-y-5">

<form method="POST"
      action="{{ $program ? route('admin.website.programs.update',$program) : route('admin.website.programs.store') }}"
      enctype="multipart/form-data"
      class="space-y-5">
  @csrf @if($program) @method('PUT') @endif

  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">
    @foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach
  </div>
  @endif

  {{-- Basic Info --}}
  <div class="card p-6 space-y-4">
    <h3 class="text-sm font-extrabold text-slate-800 border-b border-slate-100 pb-3">
      <i class="fas fa-graduation-cap text-indigo-500 mr-1.5"></i>Program Identity
      <span class="text-[11px] font-normal text-slate-400 ml-2">— shown as the program title on the card</span>
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Program Name *</label>
        <input type="text" name="name" value="{{ old('name',$program?->name) }}" required
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
            placeholder="e.g. Bachelor of Theology">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Level <span class="font-normal text-gray-400">(optional sub-label)</span></label>
        <input type="text" name="level" value="{{ old('level',$program?->level) }}" placeholder="e.g. undergraduate, diploma, postgraduate"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Age Range / Intake</label>
        <input type="text" name="age_range" value="{{ old('age_range',$program?->age_range) }}" placeholder="e.g. 18+ or All Ages or Fresh to 3rd Year"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
        <p class="text-xs text-gray-400 mt-1">Shown as a badge on the card sidebar. Leave blank to hide it.</p>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Display Order</label>
        <input type="number" name="order" value="{{ old('order',$program?->order ?? 0) }}" min="0"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
        <p class="text-xs text-gray-400 mt-1">Lower number = shown first on the page.</p>
      </div>
    </div>
  </div>

  {{-- Descriptions --}}
  <div class="card p-6 space-y-4">
    <h3 class="text-sm font-extrabold text-slate-800 border-b border-slate-100 pb-3">
      <i class="fas fa-align-left text-teal-500 mr-1.5"></i>Card Text & Descriptions
    </h3>

    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Program Description * <span class="font-normal text-gray-400">— main paragraph shown at the top of the card</span></label>
      <textarea name="description" rows="4" required
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
          placeholder="A brief overview of this program, who it's for, what students will gain...">{{ old('description',$program?->description) }}</textarea>
    </div>

    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">
        Curriculum Overview
        <span class="font-normal text-gray-400">— shown under the "{{ config('app.name') }}" section label (customizable in Site Settings)</span>
      </label>
      <textarea name="curriculum" rows="3"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
          placeholder="Subjects, courses, modules covered in this program...">{{ old('curriculum',$program?->curriculum) }}</textarea>
      <p class="text-xs text-gray-400 mt-1">Leave blank to hide this section on the card.</p>
    </div>

    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">
        Program Highlights
        <span class="font-normal text-gray-400">— shown under the "Highlights" section label</span>
      </label>
      <textarea name="highlights" rows="3"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
          placeholder="Key achievements, unique features, industry connections, career outcomes...">{{ old('highlights',$program?->highlights) }}</textarea>
      <p class="text-xs text-gray-400 mt-1">Leave blank to hide this section on the card.</p>
    </div>

    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">
        Schedule / Timetable
        <span class="font-normal text-gray-400">— shown under the "Schedule" section label</span>
      </label>
      <textarea name="schedule" rows="2"
          class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
          placeholder="e.g. Monday – Friday, 8:00 AM – 4:00 PM. Weekday and weekend options available.">{{ old('schedule',$program?->schedule) }}</textarea>
      <p class="text-xs text-gray-400 mt-1">Leave blank to hide this section on the card.</p>
    </div>
  </div>

  {{-- Feature Tags --}}
  <div class="card p-6 space-y-3">
    <h3 class="text-sm font-extrabold text-slate-800 border-b border-slate-100 pb-3">
      <i class="fas fa-tags text-amber-500 mr-1.5"></i>Feature Tags
      <span class="text-[11px] font-normal text-slate-400 ml-2">— shown as tick-mark badges on the card</span>
    </h3>
    <textarea name="features_text" rows="5"
        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-mono focus:outline-none focus:ring-2 focus:ring-yellow-400"
        placeholder="One feature per line, e.g.:&#10;Accredited Program&#10;Small Class Sizes&#10;Qualified Lecturers&#10;Industry Attachments&#10;On-campus Library">{{ old('features_text', is_array($program?->features) ? implode("\n",$program->features) : '') }}</textarea>
    <p class="text-xs text-gray-400">Each line becomes a green ✓ tag badge displayed on the program card.</p>
  </div>

  {{-- Appearance --}}
  <div class="card p-6 space-y-4">
    <h3 class="text-sm font-extrabold text-slate-800 border-b border-slate-100 pb-3">
      <i class="fas fa-palette text-purple-500 mr-1.5"></i>Card Appearance
    </h3>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Icon (emoji) <span class="font-normal text-gray-400">— shown when no image is set</span></label>
        <input type="text" name="icon" value="{{ old('icon',$program?->icon ?? '🎓') }}"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
            placeholder="🎓 or 📚 or 🏫">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Accent Color</label>
        <input type="text" name="color" value="{{ old('color',$program?->color) }}" placeholder="#3b82f6 or indigo"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
      </div>
    </div>

    {{-- Program image --}}
    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Program Card Image <span class="font-normal text-gray-400">— replaces the icon with a photo in the sidebar</span></label>
      @if($program && $program->image_path)
      <div class="mb-2 flex items-center gap-3">
        <img src="{{ filter_var($program->image_path, FILTER_VALIDATE_URL) ? $program->image_path : Storage::url($program->image_path) }}"
             alt="Current" class="h-20 w-32 object-cover rounded-xl border border-gray-200">
        <div>
          <p class="text-xs text-gray-500">Current image</p>
          <label class="flex items-center gap-1.5 text-xs text-red-500 cursor-pointer mt-1">
            <input type="checkbox" name="remove_image" value="1" class="rounded"> Remove image
          </label>
        </div>
      </div>
      @endif
      <input type="file" name="program_image" accept="image/*"
          class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100">
      <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 3 MB. Displayed as a thumbnail in the card sidebar.</p>
    </div>

    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="is_active" value="1"
          {{ old('is_active', $program?->is_active ?? true) ? 'checked' : '' }}
          class="rounded accent-emerald-500">
      <span class="text-sm font-semibold text-gray-700">Active — visible on the public website</span>
    </label>
  </div>

  <div class="flex gap-4 pb-6">
    <button type="submit" class="btn-gold px-8 py-3 rounded-xl text-sm font-bold shadow active:scale-95 inline-flex items-center gap-2">
      <i class="fas fa-save text-xs"></i> {{ $program ? 'Update Program Card' : 'Add Program Card' }}
    </button>
    <a href="{{ route('admin.website.programs.index') }}"
       class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 transition-colors">
      Cancel
    </a>
  </div>
</form>
</div>
@endsection
