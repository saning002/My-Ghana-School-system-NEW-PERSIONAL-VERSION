@extends('layouts.app')
@section('title', 'Teachers')
@section('subtitle', 'Manage teachers, their personal info, and course assignments')

@section('content')

<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Teachers</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $lecturers->total() }} teacher{{ $lecturers->total() !== 1 ? 's' : '' }} in the system</p>
    </div>
    <a href="{{ route('admin.lecturers.create') }}"
       class="inline-flex items-center gap-2 px-5 py-2.5 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700 shadow-sm transition-all">
        <i class="fas fa-plus"></i> Add Teacher
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($lecturers as $lecturer)
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
        {{-- Card header --}}
        <div class="px-5 pt-5 pb-4 flex items-start gap-4">
            {{-- Photo --}}
            <div class="flex-shrink-0">
                @if($lecturer->photo_url)
                    <img src="{{ $lecturer->photo_url }}" alt="{{ $lecturer->full_name }}"
                         class="w-16 h-16 rounded-2xl object-cover border-2 border-yellow-100 shadow-sm">
                @else
                    <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-bold text-xl text-white bg-yellow-600 shadow-sm select-none">
                        {{ strtoupper(substr($lecturer->full_name, 0, 1)) }}
                    </div>
                @endif
            </div>
            {{-- Info --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 truncate">{{ $lecturer->full_name }}</p>
                <p class="text-xs text-gray-500 truncate mt-0.5">{{ $lecturer->email }}</p>
                @if($lecturer->qualification)
                    <span class="inline-block mt-1.5 text-[10px] font-semibold bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">
                        {{ $lecturer->qualification }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Stats bar --}}
        <div class="px-5 pb-4 flex gap-4 text-xs text-gray-500">
            @if($lecturer->phone)
                <span class="flex items-center gap-1"><i class="fas fa-phone text-yellow-500"></i>{{ $lecturer->phone }}</span>
            @endif
            <span class="flex items-center gap-1 ml-auto">
                <i class="fas fa-book-open text-yellow-500"></i>
                {{ $lecturer->course_assignments_count }} course{{ $lecturer->course_assignments_count !== 1 ? 's' : '' }}
            </span>
        </div>

        {{-- Actions --}}
        <div class="px-5 pb-4 flex gap-2">
            <a href="{{ route('admin.lecturers.show', $lecturer) }}"
               class="flex-1 text-center px-3 py-2 bg-yellow-50 text-yellow-800 hover:bg-yellow-100 rounded-xl text-xs font-semibold transition-colors">
                <i class="fas fa-eye mr-1"></i> View
            </a>
            <a href="{{ route('admin.lecturers.edit', $lecturer) }}"
               class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-xl text-xs font-semibold transition-colors">
                <i class="fas fa-pen mr-1"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.lecturers.destroy', $lecturer) }}"
                  onsubmit="return confirm('Remove {{ addslashes($lecturer->full_name) }}?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="px-3 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl text-xs font-semibold transition-colors">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-20 text-center">
        <div class="w-20 h-20 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-chalkboard-user text-3xl"></i>
        </div>
        <p class="text-gray-600 font-semibold text-lg mb-1">No teachers yet</p>
        <p class="text-gray-400 text-sm mb-5">Add your first teacher to get started.</p>
        <a href="{{ route('admin.lecturers.create') }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700">
            <i class="fas fa-plus"></i> Add Teacher
        </a>
    </div>
    @endforelse
</div>

@if($lecturers->hasPages())
<div class="mt-6">{{ $lecturers->links('pagination::tailwind') }}</div>
@endif

@endsection
