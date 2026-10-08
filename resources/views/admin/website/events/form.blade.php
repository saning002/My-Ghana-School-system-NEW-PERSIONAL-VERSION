@extends('layouts.app')
@section('title', $event ? 'Edit Event' : 'New Event')

@section('content')
<form method="POST" action="{{ $event ? route('admin.website.events.update',$event) : route('admin.website.events.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-5">
  @csrf @if($event) @method('PUT') @endif
  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
  @endif
  <div class="card p-6 space-y-4">
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Title *</label><input type="text" name="title" value="{{ old('title',$event?->title) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required></div>
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Description *</label><textarea name="description" rows="5" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required>{{ old('description',$event?->description) }}</textarea></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Date *</label><input type="date" name="date" value="{{ old('date',$event?->date?->format('Y-m-d')) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required></div>
      <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Time</label><input type="time" name="time" value="{{ old('time',$event?->time) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm"></div>
    </div>
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Location</label><input type="text" name="location" value="{{ old('location',$event?->location) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm"></div>
    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Event Image</label>
      @if($event?->image_url)<img src="{{ $event->image_url }}" class="h-24 rounded-xl mb-2 object-cover">@endif
      <input type="file" name="image" accept="image/*" class="text-sm text-gray-600">
    </div>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="is_published" value="1" {{ old('is_published',$event?->is_published) ? 'checked' : '' }} class="rounded">
      <span class="text-sm font-semibold text-gray-700">Published</span>
    </label>
  </div>
  <div class="flex gap-4">
    <button type="submit" class="btn-gold px-8 py-3 rounded-xl text-sm font-bold"><i class="fas fa-save mr-2"></i>{{ $event ? 'Update' : 'Create' }}</button>
    <a href="{{ route('admin.website.events.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">Cancel</a>
  </div>
</form>
@endsection
