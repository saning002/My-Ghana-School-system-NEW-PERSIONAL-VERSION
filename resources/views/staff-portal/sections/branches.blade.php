@extends('staff-portal.layout')
@section('title','Branches')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">School Branches</h2>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($branches as $branch)
    <div class="card p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center flex-shrink-0">
                <i class="fas fa-code-branch text-indigo-600"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-sm">{{ $branch->name }}</h3>
                @if($branch->code)
                <span class="text-xs text-slate-400 font-mono">{{ $branch->code }}</span>
                @endif
            </div>
        </div>
        @if($branch->location)
        <p class="text-xs text-slate-500 mb-2"><i class="fas fa-map-marker-alt mr-1 text-slate-400"></i>{{ $branch->location }}</p>
        @endif
        <div class="flex gap-4 text-xs text-slate-500 border-t border-slate-100 pt-3 mt-2">
            <span><i class="fas fa-user-graduate mr-1 text-blue-400"></i>{{ $branch->students_count ?? 0 }} Students</span>
            <span><i class="fas fa-users mr-1 text-indigo-400"></i>{{ $branch->users_count ?? 0 }} Staff</span>
        </div>
    </div>
    @empty
    <div class="col-span-full card p-12 text-center text-slate-400">
        <i class="fas fa-code-branch text-3xl mb-3 block"></i>No branches found.
    </div>
    @endforelse
</div>
@endsection
