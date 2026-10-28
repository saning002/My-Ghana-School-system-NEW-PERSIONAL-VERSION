extends('layouts.app')
@section('title','Testimonials')
@section('subtitle','Manage parent testimonials shown on the home page')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

  {{-- Add form --}}
  <div class="card p-6 self-start">
    <h3 class="font-bold text-gray-800 text-sm mb-4">Add Testimonial</h3>
    <form method="POST" action="{{ route('admin.website.testimonials.store'?'parent_name') }}" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm" required>
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
          <option value="{{ $r }}" {{ old('rating',5)==$r?'selected':''?'admin.website.testimonials.toggle',$t) }}">
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
