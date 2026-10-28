@extends('layouts.app')
@section('title', $post ? 'Edit Post' : 'New Post')

@section('content')
<form method="POST" action="{{ $post ? route('admin.website.blog.update',$post) : route('admin.website.blog.store') }}" enctype="multipart/form-data" class="max-w-3xl space-y-5">
  @csrf
  @if($post) @method('PUT') @endif
  @if($errors->any())
  <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">@foreach($errors->all() as $e)<div>• {{ $e }}</div>@endforeach</div>
  @endif

  <div class="card p-6 space-y-4">
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Title *</label><input type="text" name="title" value="{{ old('title',$post?->title) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required></div>
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Slug (auto-generated if blank)</label><input type="text" name="slug" value="{{ old('slug',$post?->slug) }}" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm font-mono text-xs" placeholder="my-post-slug"></div>
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Excerpt</label><textarea name="excerpt" rows="2" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm">{{ old('excerpt',$post?->excerpt) }}</textarea></div>
    <div><label class="block text-xs font-bold text-gray-600 uppercase mb-1">Content *</label><textarea name="content" rows="12" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm" required>{{ old('content',$post?->content) }}</textarea></div>
    <div>
      <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Featured Image</label>
      @if($post?->featured_image_url)<img src="{{ $post->featured_image_url }}" class="h-24 rounded-xl mb-2 object-cover">@endif
      <input type="file" name="featured_image" accept="image/*" class="text-sm text-gray-600">
    </div>
    <label class="flex items-center gap-3 cursor-pointer">
      <input type="checkbox" name="published" value="1" {{ old('published',$post?->published) ? 'checked' : '' }} class="rounded">
      <span class="text-sm font-semibold text-gray-700">Publish this post</span>
    </label>
  </div>

  <div class="flex gap-4">
    <button type="submit" class="btn-gold px-8 py-3 rounded-xl text-sm font-bold"><i class="fas fa-save mr-2"></i>{{ $post ? 'Update Post' : 'Create Post' }}</button>
    <a href="{{ route('admin.website.blog.index') }}" class="px-6 py-3 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200">Cancel</a>
  </div>
</form>
@endsection
