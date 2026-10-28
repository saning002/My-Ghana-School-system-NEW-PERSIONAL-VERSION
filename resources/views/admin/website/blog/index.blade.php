@extends('layouts.app')
@section('title','Blog Posts')
@section('subtitle','Manage website blog posts')

@section('content')
<div class="flex items-center justify-between mb-6">
  <div></div>
  <a href="{{ route('admin.website.blog.create') }}" class="btn-gold flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold"><i class="fas fa-plus text-xs"></i> New Post</a>
</div>
<div class="card overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
      <tr><th class="px-5 py-3 text-left font-semibold">Title</th><th class="px-5 py-3 text-left font-semibold">Status</th><th class="px-5 py-3 text-left font-semibold">Date</th><th class="px-5 py-3 text-center font-semibold">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
      @forelse($posts as $post)
      <tr class="hover:bg-gray-50">
        <td class="px-5 py-4 font-semibold text-gray-800">{{ $post->title }}</td>
        <td class="px-5 py-4">
          <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $post->published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $post->published ? 'Published' : 'Draft' }}</span>
        </td>
        <td class="px-5 py-4 text-gray-400 text-xs">{{ $post->created_at->format('M j, Y') }}</td>
        <td class="px-5 py-4 text-center">
          <div class="flex items-center justify-center gap-2">
            <a href="{{ route('admin.website.blog.edit', $post) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 transition-colors"><i class="fas fa-edit mr-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.website.blog.destroy', $post) }}" onsubmit="return confirm('Delete this post?')">
              @csrf @method('DELETE')
              <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100 transition-colors"><i class="fas fa-trash"></i></button>
            </form>
          </div>
        </td>
      </tr>
      @empty
      <tr><td colspan="4" class="px-5 py-10 text-center text-gray-400">No posts yet. <a href="{{ route('admin.website.blog.create') }}" class="text-blue-600 font-semibold">Create one</a></td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-4 border-t border-gray-100">{{ $posts->links() }}</div>
</div>
@endsection
