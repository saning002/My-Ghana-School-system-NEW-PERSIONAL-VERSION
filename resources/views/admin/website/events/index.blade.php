@extends('layouts.app')
@section('title','Events')
@section('subtitle','Manage website events')

@section('content')
<div class="flex justify-end mb-6">
  <a href="{{ route('admin.website.events.create') }}" class="btn-gold flex items-center gap-2 px-5 py-2.5 rounded-2xl text-sm font-bold"><i class="fas fa-plus text-xs"></i> New Event</a>
</div>
<div class="card overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
      <tr><th class="px-5 py-3 text-left font-semibold">Title</th><th class="px-5 py-3 text-left font-semibold">Date</th><th class="px-5 py-3 text-left font-semibold">Location</th><th class="px-5 py-3 text-left font-semibold">Status</th><th class="px-5 py-3 text-center font-semibold">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
      @forelse($events as $event)
      <tr class="hover:bg-gray-50">
        <td class="px-5 py-4 font-semibold text-gray-800">{{ $event->title }}</td>
        <td class="px-5 py-4 text-gray-600">{{ $event->date->format('M j, Y') }}</td>
        <td class="px-5 py-4 text-gray-500 text-xs">{{ $event->location ?: '—' }}</td>
        <td class="px-5 py-4">
          <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $event->is_published ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $event->is_published ? 'Published' : 'Hidden' }}</span>
        </td>
        <td class="px-5 py-4 text-center">
          <div class="flex items-center justify-center gap-2">
            <a href="{{ route('admin.website.events.edit',$event) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100"><i class="fas fa-edit mr-1"></i>Edit</a>
            <form method="POST" action="{{ route('admin.website.events.destroy',$event) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100"><i class="fas fa-trash"></i></button></form>
          </div>
        </td>
      </tr>
      @empty<tr><td colspan="5" class="py-10 text-center text-gray-400">No events yet.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-4 border-t border-gray-100">{{ $events->links() }}</div>
</div>
@endsection
