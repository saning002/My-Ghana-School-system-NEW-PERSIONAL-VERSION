@extends('staff-portal.layout')
@section('title','Academic Sessions')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Academic Sessions</h2>
</div>
<div class="space-y-4">
    @forelse($sessions as $session)
    <div class="card p-5">
        <div class="flex items-center justify-between mb-3">
            <div>
                <h3 class="font-bold text-slate-800">{{ $session->year }}</h3>
                @if($session->is_active)
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 mt-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                </span>
                @endif
            </div>
            <span class="text-xs text-slate-400">{{ $session->periods->count() }} period(s)</span>
        </div>
        @if($session->periods->count())
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-2">
            @foreach($session->periods as $period)
            <div class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-100 text-center">
                <p class="text-xs font-bold text-slate-700">{{ $period->name }}</p>
                <p class="text-[10px] text-slate-400 mt-0.5">
                    {{ $period->start_date ? \Carbon\Carbon::parse($period->start_date)->format('M d') : '' }}
                    – {{ $period->end_date ? \Carbon\Carbon::parse($period->end_date)->format('M d') : '' }}
                </p>
            </div>
            @endforeach
        </div>
        @endif
    </div>
    @empty
    <div class="card p-12 text-center text-slate-400">
        <i class="fas fa-layer-group text-3xl mb-3 block"></i>No academic sessions found.
    </div>
    @endforelse
</div>
@endsection
