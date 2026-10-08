@extends('staff-portal.layout')
@section('title','Teacher Work Logs')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Teacher Work Logs</h2>
</div>
<form class="flex flex-wrap gap-3 mb-6">
    <select name="lecturer_id" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Teachers</option>
        @foreach($lecturers as $l)
        <option value="{{ $l->id }}" {{ request('lecturer_id')==$l->id?'selected':'' }}>{{ $l->full_name }}</option>
        @endforeach
    </select>
    <select name="program_id" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Programs</option>
        @foreach($programs as $p)
        <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
        @endforeach
    </select>
</form>
<div class="space-y-3">
    @forelse($logs as $log)
    <div class="card p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                    <span class="font-bold text-slate-800 text-sm">{{ $log->lecturer->full_name ?? '—' }}</span>
                    @if($log->week_number)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700">Week {{ $log->week_number }}</span>
                    @endif
                </div>
                <p class="text-sm font-semibold text-slate-700 mb-1">{{ $log->title }}</p>
                @if($log->topic_covered)
                <p class="text-xs text-slate-500 mb-1"><span class="font-semibold">Topic:</span> {{ $log->topic_covered }}</p>
                @endif
                @if($log->notes)
                <p class="text-xs text-slate-500">{{ Str::limit($log->notes, 150) }}</p>
                @endif
                @if($log->admin_comment)
                <div class="mt-2 px-3 py-2 rounded-lg bg-amber-50 border border-amber-200 text-xs text-amber-800">
                    <i class="fas fa-comment-alt mr-1"></i><strong>Admin feedback:</strong> {{ $log->admin_comment }}
                </div>
                @endif
            </div>
            <div class="text-right text-xs text-slate-400 shrink-0">
                <div>{{ $log->created_at->format('M d, Y') }}</div>
                <div class="mt-1">{{ $log->course->name ?? '' }}</div>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-12 text-center text-slate-400">
        <i class="fas fa-clipboard-list text-3xl mb-3 block"></i>
        No work log entries found.
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $logs->links() }}</div>
@endsection
