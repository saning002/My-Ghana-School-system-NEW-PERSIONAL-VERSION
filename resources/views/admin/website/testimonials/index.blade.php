@extends('layouts.app')
@section('title','Testimonials')
@section('subtitle','Manage parent testimonials shown on the home page')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- Add form --}}
  <div class="card p-6 self-start">
    <h3 class="font-bold text-gray-800 text-sm mb-4">Add Testimonial</h3>
    <form method="POST" action="{{ route('admin.website.testimonials.store') }}" class="space-y-3">
      @csrf
      @if($errors->any())
      <div class="bg-red-50 rounded-xl px-4 py-3 text-xs text-red-700">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
      @endif
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Parent Name *</label>
        <input type="text" name="parent_name" value="{{ old('parent_name') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm" required>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Child Name</label>
        <input type="text" name="child_name" value="{{ old('child_name') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm">
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Message *</label>
        <textarea name="message" rows="4" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm" required>{{ old('message') }}</textarea>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Rating (1–5)</label>
        <select name="rating" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm">
          @foreach([5,4,3,2,1] as $r)
          <option value="{{ $r }}" {{ old('rating',5)==$r?'selected':'' }}>{{ $r }} ★</option>
          @endforeach
        </select>
      </div>
      <label class="flex items-center gap-2 cursor-pointer">
        <input type="checkbox" name="is_active" value="1" checked class="rounded">
        <span class="text-sm text-gray-700">Active</span>
      </label>
      <button type="submit" class="btn-gold w-full py-2.5 rounded-xl text-sm font-bold">
        <i class="fas fa-plus mr-2"></i>Add Testimonial
      </button>
    </form>
  </div>

  {{-- List --}}
  <div class="lg:col-span-2 space-y-3">
    @forelse($testimonials as $t)
    <div class="card p-5 flex items-start gap-4">
      <div class="w-10 h-10 rounded-full bg-yellow-100 flex items-center justify-center font-bold text-yellow-700 flex-shrink-0 text-sm">
        {{ strtoupper(substr($t->parent_name,0,1)) }}
      </div>
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
          <p class="font-bold text-gray-800 text-sm">{{ $t->parent_name }}</p>
          @if($t->child_name)<p class="text-xs text-gray-400">Parent of {{ $t->child_name }}</p>@endif
        </div>
        <div class="text-yellow-400 text-xs mb-1">@for($i=0;$i<$t->rating;$i++)★@endfor</div>
        <p class="text-sm text-gray-600 line-clamp-3">{{ $t->message }}</p>
      </div>
      <div class="flex flex-col gap-2 flex-shrink-0">
        <form method="POST" action="{{ route('admin.website.testimonials.toggle',$t) }}">
          @csrf @method('PATCH')
          <button class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $t->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
            {{ $t->is_active ? 'Active' : 'Hidden' }}
          </button>
        </form>
        <form method="POST" action="{{ route('admin.website.testimonials.destroy',$t) }}" onsubmit="return confirm('Delete?')">
          @csrf @method('DELETE')
          <button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 w-full">
            <i class="fas fa-trash"></i>
          </button>
        </form>
      </div>
    </div>
    @empty
    <div class="card p-12 text-center text-gray-400">
      <i class="fas fa-quote-left text-3xl block mb-3"></i>
      <p>No testimonials yet. Add the first one.</p>
    </div>
    @endforelse
    <div>{{ $testimonials->links() }}</div>
  </div>
</div>
@endsection
