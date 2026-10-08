@extends('staff-portal.layout')
@section('title','Lecturers')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Lecturers</h2>
</div>
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
    @forelse($lecturers as $lecturer)
    <div class="card p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-full bg-indigo-100 flex items-center justify-center font-bold text-indigo-700 text-lg flex-shrink-0">
            {{ strtoupper(substr($lecturer->full_name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="font-bold text-slate-800 text-sm truncate">{{ $lecturer->full_name }}</p>
            <p class="text-xs text-slate-500 truncate">{{ $lecturer->email }}</p>
            @if($lecturer->phone)
            <p class="text-xs text-slate-400">{{ $lecturer->phone }}</p>
            @endif
        </div>
    </div>
    @empty
    <div class="col-span-full card p-12 text-center text-slate-400">
        <i class="fas fa-chalkboard-teacher text-3xl mb-3 block"></i>No lecturers found.
    </div>
    @endforelse
</div>
<div class="mt-4">{{ $lecturers->links() }}</div>
@endsection
