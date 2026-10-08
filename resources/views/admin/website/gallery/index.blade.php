@extends('layouts.app')
@section('title','Gallery')
@section('subtitle','Manage website photo gallery')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
  <div class="lg:col-span-2 card p-6">
    <h3 class="font-bold text-gray-800 mb-4 text-sm">Upload Photos</h3>
    <form method="POST" action="{{ route('admin.website.gallery.upload') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Images (multiple allowed)</label>
        <input type="file" name="images[]" multiple accept="image/*" class="text-sm text-gray-600 w-full" required>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Category</label>
          <select name="category_id" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm">
            <option value="">No Category</option>
            @foreach($categories as $cat)<option value="{{ $cat->id }}">{{ $cat->name }}</option>@endforeach
          </select>
        </div>
        <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Title / Caption</label><input type="text" name="title" class="w-full px-3 py-2.5 border border-gray-200 rounded-xl text-sm" placeholder="Optional"></div>
      </div>
      <button type="submit" class="btn-gold px-6 py-2.5 rounded-xl text-sm font-bold"><i class="fas fa-upload mr-2"></i>Upload</button>
    </form>
  </div>
  <div class="card p-6">
    <h3 class="font-bold text-gray-800 mb-4 text-sm">Categories</h3>
    <form method="POST" action="{{ route('admin.website.gallery.category.store') }}" class="flex gap-2 mb-4">
      @csrf
      <input type="text" name="name" placeholder="New category name" class="flex-1 px-3 py-2 border border-gray-200 rounded-xl text-sm" required>
      <button type="submit" class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-blue-600 hover:bg-blue-700"><i class="fas fa-plus"></i></button>
    </form>
    <div class="space-y-2">
      @foreach($categories as $cat)
      <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0 text-sm">
        <span class="font-medium text-gray-700">{{ $cat->name }}</span>
        <span class="text-xs text-gray-400">{{ $cat->images_count }} photos</span>
      </div>
      @endforeach
    </div>
  </div>
</div>

<div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
  @forelse($images as $img)
  <div class="relative rounded-xl overflow-hidden aspect-square group shadow-sm">
    <img src="{{ $img->image_url }}" alt="{{ $img->title }}" class="w-full h-full object-cover">
    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
      <form method="POST" action="{{ route('admin.website.gallery.destroy',$img) }}" onsubmit="return confirm('Delete?')">
        @csrf @method('DELETE')
        <button class="w-8 h-8 rounded-full bg-red-500 text-white flex items-center justify-center text-xs"><i class="fas fa-trash"></i></button>
      </form>
    </div>
    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 opacity-0 group-hover:opacity-100 transition-opacity">
      <p class="text-white text-xs truncate">{{ $img->title }}</p>
    </div>
  </div>
  @empty
  <div class="col-span-full py-16 text-center text-gray-400">
    <i class="fas fa-images text-3xl block mb-3"></i><p>No photos yet.</p>
  </div>
  @endforelse
</div>
<div class="mt-6">{{ $images->links() }}</div>
@endsection
