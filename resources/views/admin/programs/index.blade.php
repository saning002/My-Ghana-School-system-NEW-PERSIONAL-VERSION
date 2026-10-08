@extends('layouts.app')
@section('title','Programs')
@section('subtitle','Manage academic programs')

@section('content')

<style>
    .program-card {
        background: white;
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .program-card:hover {
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        transform: translateY(-4px);
        border-color: rgba(212, 160, 23, 0.2);
    }
    
    .program-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #D4A017, #fbbf24);
    }
</style>

<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Academic Programs</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $programs->total() }} program{{ $programs->total() !== 1 ? 's' : '' }} in the system</p>
    </div>
    <a href="{{ route('admin.programs.create') }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl text-sm font-semibold hover:from-yellow-700 hover:to-yellow-800 shadow-lg hover:shadow-xl transition-all">
        <i class="fas fa-plus"></i> Add Program
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($programs as $program)
    <div class="program-card p-6">
        <div class="flex items-start justify-between mb-4">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center flex-shrink-0 bg-gradient-to-br from-yellow-100 to-yellow-50">
                <i class="fas fa-graduation-cap text-lg text-yellow-700"></i>
            </div>
            <div class="flex gap-1.5">
                <a href="{{ route('admin.programs.edit',$program) }}"
                   class="w-9 h-9 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors">
                    <i class="fas fa-pen text-xs"></i>
                </a>
                <form method="POST" action="{{ route('admin.programs.destroy',$program) }}" onsubmit="return confirm('Delete this program?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-9 h-9 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
        <h3 class="text-base font-bold text-gray-900 mb-2 line-clamp-2">{{ $program->name }}</h3>
        <p class="text-xs text-gray-500 mb-4">
            <i class="fas fa-layers mr-1" style="color: #D4A017;"></i>
            Level {{ $program->sequence }} · {{ $program->duration }} months
        </p>

        <div class="grid grid-cols-2 gap-2 mb-4">
            <div class="rounded-lg p-3 text-center bg-gradient-to-br from-yellow-50 to-yellow-100/50 border border-yellow-200/50">
                <p class="text-xl font-bold text-yellow-900">{{ $program->students_count ?? 0 }}</p>
                <p class="text-xs text-yellow-700 mt-0.5 font-semibold">Enrolled</p>
            </div>
            <div class="rounded-lg p-3 text-center bg-gradient-to-br from-blue-50 to-blue-100/50 border border-blue-200/50">
                <p class="text-xl font-bold text-blue-900">{{ $program->courses->count() }}</p>
                <p class="text-xs text-blue-700 mt-0.5 font-semibold">Courses</p>
            </div>
        </div>

        <a href="{{ route('admin.programs.show',$program) }}"
           class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg text-xs font-semibold transition-colors bg-yellow-50 text-yellow-700 hover:bg-yellow-100">
            View Details <i class="fas fa-arrow-right text-[10px]"></i>
        </a>
    </div>
    @empty
    <div class="col-span-full card p-16 text-center bg-gradient-to-br from-gray-50 to-gray-100/50">
        <div class="w-20 h-20 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-graduation-cap text-2xl"></i>
        </div>
        <p class="text-gray-600 font-medium text-lg mb-2">No programs created yet</p>
        <p class="text-gray-500 text-sm mb-6">Create programs to organize your academic offerings</p>
        <a href="{{ route('admin.programs.create') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 text-white rounded-xl text-sm font-semibold hover:from-yellow-700 hover:to-yellow-800">
            <i class="fas fa-plus"></i> Create First Program
        </a>
    </div>
    @endforelse
</div>

@if($programs->hasPages())
<div class="mt-8 px-4">{{ $programs->links('pagination::tailwind') }}</div>
@endif
@endsection
