@extends('layouts.app')
@section('title','Contact Messages')

@section('content')
<div class="card overflow-hidden">
  <table class="w-full text-sm">
    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
      <tr><th class="px-5 py-3 text-left font-semibold">From</th><th class="px-5 py-3 text-left font-semibold">Subject</th><th class="px-5 py-3 text-left font-semibold">Date</th><th class="px-5 py-3 text-center font-semibold">Actions</th></tr>
    </thead>
    <tbody class="divide-y divide-gray-50">
      @forelse($messages as $msg)
      <tr class="hover:bg-gray-50 {{ !$msg->is_read ? 'font-semibold' : '' }}">
        <td class="px-5 py-4">
          <div class="flex items-center gap-2">
            @if(!$msg->is_read)<span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>@endif
            <div><p class="text-gray-800 font-semibold">{{ $msg->name }}</p><p class="text-xs text-gray-400">{{ $msg->email }}</p></div>
          </div>
        </td>
        <td class="px-5 py-4 text-gray-600">{{ $msg->subject }}</td>
        <td class="px-5 py-4 text-gray-400 text-xs">{{ $msg->created_at->format('M j, Y') }}</td>
        <td class="px-5 py-4 text-center">
          <div class="flex items-center justify-center gap-2">
            <a href="{{ route('admin.website.messages.show',$msg) }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100"><i class="fas fa-eye mr-1"></i>Read</a>
            <form method="POST" action="{{ route('admin.website.messages.destroy',$msg) }}" onsubmit="return confirm('Delete?')">@csrf @method('DELETE')<button class="px-3 py-1.5 rounded-lg text-xs font-semibold text-red-700 bg-red-50 hover:bg-red-100"><i class="fas fa-trash"></i></button></form>
          </div>
        </td>
      </tr>
      @empty<tr><td colspan="4" class="py-12 text-center text-gray-400">No messages yet.</td></tr>
      @endforelse
    </tbody>
  </table>
  <div class="px-5 py-4 border-t">{{ $messages->links() }}</div>
</div>
@endsection
