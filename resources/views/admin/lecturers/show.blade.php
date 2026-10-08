@extends('layouts.app')
@section('title', $lecturer->full_name)
@section('subtitle', 'Teacher profile and course assignments')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- ── Back + Actions ── --}}
    <div class="flex items-center justify-between">
        <a href="{{ route('admin.lecturers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Teachers
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.lecturers.edit', $lecturer) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                <i class="fas fa-pen"></i> Edit Profile
            </a>
            <form method="POST" action="{{ route('admin.lecturers.destroy', $lecturer) }}"
                  onsubmit="return confirm('Remove {{ addslashes($lecturer->full_name) }} permanently?')">
                @csrf @method('DELETE')
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl text-sm font-semibold transition-colors border border-red-200">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </form>
        </div>
    </div>

    {{-- ── Profile Card ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-600 to-yellow-700 h-24 relative">
            <div class="absolute -bottom-10 left-6">
                @if($lecturer->photo_url)
                    <img src="{{ $lecturer->photo_url }}" alt="{{ $lecturer->full_name }}"
                         class="w-20 h-20 rounded-2xl object-cover border-4 border-white shadow-md">
                @else
                    <div class="w-20 h-20 rounded-2xl bg-white flex items-center justify-center border-4 border-white shadow-md">
                        <span class="text-3xl font-bold text-yellow-600">{{ strtoupper(substr($lecturer->full_name, 0, 1)) }}</span>
                    </div>
                @endif
            </div>
        </div>
        <div class="pt-14 px-6 pb-6">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $lecturer->full_name }}</h2>
                    @if($lecturer->qualification)
                        <p class="text-sm text-blue-600 font-semibold mt-0.5">{{ $lecturer->qualification }}</p>
                    @endif
                </div>
                <span class="text-xs px-3 py-1 bg-yellow-50 text-yellow-700 border border-yellow-200 rounded-full font-semibold">
                    <i class="fas fa-chalkboard-user mr-1"></i> Teacher
                </span>
            </div>

            @if($lecturer->bio)
                <p class="mt-3 text-sm text-gray-600 leading-relaxed border-l-4 border-yellow-300 pl-3">
                    {{ $lecturer->bio }}
                </p>
            @endif

            {{-- Personal details grid --}}
            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @if($lecturer->email)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-envelope text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">{{ $lecturer->email }}</span>
                </div>
                @endif
                @if($lecturer->phone)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-phone text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    {{ $lecturer->phone }}
                </div>
                @endif
                @if($lecturer->date_of_birth)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-birthday-cake text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    {{ $lecturer->date_of_birth->format('d M Y') }}
                </div>
                @endif
                @if($lecturer->gender)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-venus-mars text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    {{ ucfirst($lecturer->gender) }}
                </div>
                @endif
                @if($lecturer->nationality)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-flag text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    {{ $lecturer->nationality }}
                </div>
                @endif
                @if($lecturer->address)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2 sm:col-span-2 lg:col-span-1">
                    <i class="fas fa-map-marker-alt text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">{{ $lecturer->address }}</span>
                </div>
                @endif
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-calendar-plus text-yellow-500 w-4 text-center flex-shrink-0"></i>
                    Joined {{ $lecturer->created_at->format('M Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Course Assignments ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-blue-100/40 flex items-center justify-between">
            <h3 class="text-sm font-bold text-blue-900 flex items-center gap-2">
                <i class="fas fa-book-open text-blue-600"></i> Course Assignments
            </h3>
            @php $totalCourses = $assignments->flatten()->count(); @endphp
            <span class="text-xs bg-blue-100 text-blue-700 px-3 py-1 rounded-full font-semibold">
                {{ $totalCourses }} course{{ $totalCourses !== 1 ? 's' : '' }}
            </span>
        </div>

        @if($assignments->isEmpty())
            <div class="px-6 py-12 text-center">
                <i class="fas fa-book-open text-gray-300 text-3xl mb-3 block"></i>
                <p class="text-gray-500 text-sm font-medium">No courses assigned yet.</p>
                <a href="{{ route('admin.lecturers.edit', $lecturer) }}"
                   class="mt-3 inline-flex items-center gap-1 text-blue-600 hover:underline text-xs font-semibold">
                    <i class="fas fa-plus"></i> Assign Courses
                </a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($assignments as $programId => $programAssignments)
                @php $program = $programs->get($programId); @endphp
                <div class="px-6 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        @if($program)
                            <span class="w-5 h-5 rounded-full bg-yellow-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                                {{ $program->sequence }}
                            </span>
                            <p class="text-sm font-bold text-gray-800">{{ $program->name }}</p>
                        @else
                            <p class="text-sm font-bold text-gray-500">Program #{{ $programId }}</p>
                        @endif
                        <span class="ml-auto text-xs text-gray-400">{{ $programAssignments->count() }} course{{ $programAssignments->count() !== 1 ? 's' : '' }}</span>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($programAssignments as $assignment)
                        <div class="flex items-center gap-3 p-3 bg-blue-50/60 rounded-xl border border-blue-100">
                            <div class="w-7 h-7 rounded-lg bg-blue-600 text-white flex items-center justify-center flex-shrink-0 text-[10px] font-bold">
                                <i class="fas fa-book"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $assignment->course->name ?? '—' }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $assignment->course->code ?? '' }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
