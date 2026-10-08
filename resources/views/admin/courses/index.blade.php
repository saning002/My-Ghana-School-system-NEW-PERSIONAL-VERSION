@extends('layouts.app')
@section('title', 'Courses')
@section('subtitle', 'Manage all courses')

@section('content')

<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">All Courses</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $totalCourses }} course{{ $totalCourses !== 1 ? 's' : '' }} across {{ $programs->count() }} program{{ $programs->count() !== 1 ? 's' : '' }}</p>
    </div>
    <a href="{{ route('admin.courses.create') }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700 shadow-sm transition-all">
        <i class="fas fa-plus"></i> Add Course
    </a>
</div>

@if(session('success'))
    <div class="mb-6 px-4 py-3 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm font-medium flex items-center gap-2">
        <i class="fas fa-check-circle text-green-500"></i> {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="mb-6 px-4 py-3 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm font-medium flex items-center gap-2">
        <i class="fas fa-exclamation-circle text-red-500"></i> {{ session('error') }}
    </div>
@endif

@if($programs->isEmpty())
    <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center">
        <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-book-open text-2xl text-gray-400"></i>
        </div>
        <p class="text-gray-600 font-medium text-lg mb-2">No programs found</p>
        <p class="text-gray-500 text-sm mb-6">Create programs first, then add courses to them.</p>
    </div>
@else
    <div class="space-y-4">
        @foreach($programs as $program)
        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden shadow-sm">

            {{-- Program header (click to toggle) --}}
            <button type="button"
                onclick="toggleProgram({{ $program->id }})"
                class="w-full flex items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors text-left">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0"
                         style="background: rgba(212,160,23,0.12);">
                        <i class="fas fa-graduation-cap text-sm" style="color:#D4A017;"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $program->name }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">
                            {{ $program->courses->count() }} course{{ $program->courses->count() !== 1 ? 's' : '' }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('admin.courses.create') }}?program_id={{ $program->id }}"
                       onclick="event.stopPropagation()"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-50 text-yellow-700 rounded-lg text-xs font-semibold hover:bg-yellow-100 transition-colors">
                        <i class="fas fa-plus text-[10px]"></i> Add Course
                    </a>
                    <i id="chevron-{{ $program->id }}"
                       class="fas fa-chevron-down text-gray-400 transition-transform duration-200 text-sm"></i>
                </div>
            </button>

            {{-- Courses list (hidden by default, toggle on click) --}}
            <div id="program-courses-{{ $program->id }}" class="hidden">
                @if($program->courses->isEmpty())
                    <div class="px-6 py-8 text-center border-t border-gray-50">
                        <p class="text-gray-400 text-sm">No courses added to this program yet.</p>
                        <a href="{{ route('admin.courses.create') }}?program_id={{ $program->id }}"
                           class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 bg-yellow-600 text-white rounded-lg text-xs font-semibold hover:bg-yellow-700 transition-colors">
                            <i class="fas fa-plus"></i> Add First Course
                        </a>
                    </div>
                @else
                    <div class="border-t border-gray-100">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Course</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Code</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Description</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Enrolled</th>
                                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($program->courses as $course)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-semibold text-gray-900">{{ $course->name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="inline-block px-2.5 py-1 rounded-lg text-xs font-bold bg-yellow-100 text-yellow-800">
                                            {{ $course->code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        <span class="text-gray-500 text-xs line-clamp-1">{{ $course->description ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-600">
                                            <i class="fas fa-users text-yellow-500"></i>
                                            {{ $course->enrollments_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.courses.show', $course) }}"
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-gray-50 text-gray-600 hover:bg-gray-100 transition-colors"
                                               title="View">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <a href="{{ route('admin.courses.edit', $course) }}"
                                               class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors"
                                               title="Edit">
                                                <i class="fas fa-pen text-xs"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}"
                                                  onsubmit="return confirm('Delete {{ addslashes($course->name) }}? This cannot be undone.')">
                                                @csrf @method('DELETE')
                                                <button type="submit"
                                                        class="w-8 h-8 flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors"
                                                        title="Delete">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

        </div>
        @endforeach
    </div>
@endif

<script>
function toggleProgram(programId) {
    const panel = document.getElementById('program-courses-' + programId);
    const chevron = document.getElementById('chevron-' + programId);
    const isHidden = panel.classList.contains('hidden');

    panel.classList.toggle('hidden', !isHidden);
    chevron.classList.toggle('rotate-180', isHidden);
}

// Auto-expand a program if it has courses and there's only one program,
// or if the URL has a hash matching the program
document.addEventListener('DOMContentLoaded', function () {
    const programs = document.querySelectorAll('[id^="program-courses-"]');
    if (programs.length === 1) {
        const id = programs[0].id.replace('program-courses-', '');
        toggleProgram(id);
    }
});
</script>

@endsection
