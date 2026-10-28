@extends('layouts.app')
@section('title','Staff Members')

@section('content')
<div class="flex justify-end mb-6">
  <a href="{{ route('admin.website.staff.create') }}" class="btn-gold flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold"><i class="fas fa-plus text-xs"></i> Add Member</a>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
  @forelse($staff as $member)
  <div class="card p-5 flex items-center gap-4">
    <div class="w-14 h-14 rounded-2xl overflow-hidden bg-gray-100 flex items-center justify-center text-xl font-bold text-gray-500 flex-shrink-0">
      @if($member->photo_url)<img src="{{ $member->photo_url }}" class="w-full h-full object-cover" alt="{{ $member->name }}">
      @else{{ strtoupper(substr($member->name,0,1)) }}@endif
    </div>
    <div class="flex-1 min-w-0">
      <p class="font-bold text-gray-800 truncate">{{ $member->name }}</p>
      <p class="text-xs text-gray-500 truncate">{{ $member->position }}</p>
    </div>
    <div class="flex gap-2">
      <a href="{{ route('admin.website.staff.edit',$member) }}" class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 text-xs"><i class="fas fa-edit"></i></a>
      <form method="POST" action="{{ route('admin.website.staff.destroy',$member) }}" onsubmit="return confirm('Remove?')">
        @csrf @method('DELETE')
        <button class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-500 hover:bg-red-100 text-xs"><i class="fas fa-trash"></i></button>
      </form>
    </div>
  </div>
  @empty
  <div class="col-span-full py-16 text-center text-gray-400">
    <i class="fas fa-users text-3xl block mb-3"></i><p>No staff members yet. <a href="{{ route('admin.website.staff.create') }}" class="text-blue-600 font-semibold">Add one</a></p>
  </div>
  @endforelse
</div>
@endsection
