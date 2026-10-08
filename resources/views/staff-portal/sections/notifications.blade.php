@extends('staff-portal.layout')
@section('title','Notifications')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Student Notifications</h2>
</div>
<div class="space-y-3">
    @forelse($notifications as $n)
    <div class="card p-4 flex items-start gap-4">
        <div class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-bell text-blue-500 text-sm"></i>
        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-slate-800 text-sm">{{ $n->title ?? $n->subject ?? 'Notification' }}</p>
            <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($n->message ?? $n->body ?? '', 120) }}</p>
            <p class="text-[10px] text-slate-400 mt-1">
                To: {{ $n->student->user->full_name ?? 'All Students' }} &bull; {{ $n->created_at->format('M d, Y') }}
            </p>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center text-slate-400">
        <i class="fas fa-bell text-3xl mb-3 block"></i>No notifications found.
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $notifications->links() }}</div>
@endsection
