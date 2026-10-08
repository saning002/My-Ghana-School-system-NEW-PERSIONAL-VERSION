@extends('staff-portal.layout')
@section('title','Programs')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Programs</h2>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($programs as $program)
    <div class="card p-5">
        <h3 class="font-bold text-slate-800 mb-2">{{ $program->name }}</h3>
        <div class="flex gap-4 text-xs text-slate-500">
            <span><i class="fas fa-users mr-1 text-blue-400"></i>{{ $program->students_count ?? 0 }} Students</span>
            <span><i class="fas fa-book mr-1 text-indigo-400"></i>{{ $program->courses_count ?? 0 }} Courses</span>
        </div>
        @if($program->duration)
        <p class="text-xs text-slate-400 mt-2">Duration: {{ $program->duration }}</p>
        @endif
    </div>
    @empty
    <div class="col-span-full card p-12 text-center text-slate-400">
        <i class="fas fa-graduation-cap text-3xl mb-3 block"></i>No programs found.
    </div>
    @endforelse
</div>
@endsection
