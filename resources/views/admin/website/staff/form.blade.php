@extends('layouts.app')
@section('title', $member ? 'Edit Staff Member' : 'Add Staff Member')

@section('content')
<form method="POST" action="{{ $member ? route('admin.website.staff.update',$member) : route('admin.website.staff.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-5">
  @csrf @if($member) @method('PUT') @endif
  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
  @endif
  <div class="card p-6 space-y-4">
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Name *</label><input type="text" name="name" value="{{ old('name',$member?->name) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required></div>
      <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Position *</label><input type="text" name="position" value="{{ old('position',$member?->position) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required></div>
    </div>
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Bio</label><textarea name="bio" rows="4" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm">{{ old('bio',$member?->bio) }}</textarea></div>
    <div class="grid grid-cols-2 gap-4">
      <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Email</label><input type="email" name="email" value="{{ old('email',$member?->email) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm"></div>
      <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Order</label><input type="number" name="order" value="{{ old('order',$member?->order ?? 0) }}" min="0" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm"></div>
    </div>
    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Photo</label>
      @if($member?->photo_url)<img src="{{ $member->photo_url }}" class="w-16 h-16 rounded-full mb-2 object-cover">@endif
      <input type="file" name="photo" accept="image/*" class="text-sm text-gray-600">
    </div>
  </div>
  <div class="flex gap-4">
    <button type="submit" class="btn-gold px-8 py-3 rounded-xl text-sm font-bold"><i class="fas fa-save mr-2"></i>{{ $member ? 'Update' : 'Add Member' }}</button>
    <a href="{{ route('admin.website.staff.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">Cancel</a>
  </div>
</form>
@endsection
