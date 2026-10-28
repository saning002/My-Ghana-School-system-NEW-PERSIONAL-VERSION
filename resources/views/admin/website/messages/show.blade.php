@extends('layouts.app')
@section('title','Message from '.$message->name)

@section('content')
<div class="max-w-2xl">
  <div class="card p-8">
    <div class="flex items-start justify-between mb-6">
      <div>
        <h2 class="text-xl font-bold text-gray-900">{{ $message->subject }}</h2>
        <p class="text-sm text-gray-500 mt-1">From <strong>{{ $message->name }}</strong> &bull; {{ $message->email }}@if($message->phone) &bull; {{ $message->phone }}@endif</p>
        <p class="text-xs text-gray-400 mt-1">Received {{ $message->created_at->format('F j, Y \a\t g:i A') }}</p>
      </div>
      <form method="POST" action="{{ route('admin.website.messages.destroy',$message) }}" onsubmit="return confirm('Delete this message?')">
        @csrf @method('DELETE')
        <button class="px-4 py-2 rounded-xl text-sm font-semibold text-red-700 bg-red-50 hover:bg-red-100"><i class="fas fa-trash mr-1"></i>Delete</button>
      </form>
    </div>
    <div class="bg-gray-50 rounded-2xl p-6 text-sm text-gray-700 leading-relaxed whitespace-pre-wrap">{{ $message->message }}</div>
    <div class="flex gap-4 mt-6">
      <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject }}" class="btn-gold px-6 py-2.5 rounded-xl text-sm font-bold"><i class="fas fa-reply mr-2"></i>Reply by Email</a>
      <a href="{{ route('admin.website.messages.index') }}" class="px-6 py-2.5 rounded-xl text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200"><i class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>
  </div>
</div>
@endsection
