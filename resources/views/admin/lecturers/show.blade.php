extends('layouts.app')
@section('title', $lecturer->full_name)
@section('subtitle', 'Teacher profile and course assignments')

@section('content'?'admin.lecturers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 font-medium flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Teachers
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.lecturers.edit', $lecturer) }}"
               class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-colors">
                <i class="fas fa-pen"></i> Edit Profile
            </a>
            <form method="POST" action="{{ route('admin.lecturers.destroy', $lecturer) }}"
                  onsubmit="return confirm('Remove {{ addslashes($lecturer->full_name) }} permanently?')">
                @csrf @method('DELETE'?'d M Y') }}
                </div>
                @endif
                @if($lecturer->gender)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-venus-mars text-[var(--brand)] w-4 text-center flex-shrink-0"></i>
                    {{ ucfirst($lecturer->gender) }}
                </div>
                @endif
                @if($lecturer->nationality)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-flag text-[var(--brand)] w-4 text-center flex-shrink-0"></i>
                    {{ $lecturer->nationality }}
                </div>
                @endif
                @if($lecturer->address)
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2 sm:col-span-2 lg:col-span-1">
                    <i class="fas fa-map-marker-alt text-[var(--brand)] w-4 text-center flex-shrink-0"></i>
                    <span class="truncate">{{ $lecturer->address }}</span>
                </div>
                @endif
                <div class="flex items-center gap-2 text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2">
                    <i class="fas fa-calendar-plus text-[var(--brand)] w-4 text-center flex-shrink-0"></i>
                    Joined {{ $lecturer->created_at->format('M Y'?'s' : '' }}
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
                            <span class="w-5 h-5 rounded-full bg-[var(--primary-pale)]0 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">
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
                                <p class="text-sm font-semibold text-gray-800 truncate">{{ $assignment->course->name ?? '–' }}</p>
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
