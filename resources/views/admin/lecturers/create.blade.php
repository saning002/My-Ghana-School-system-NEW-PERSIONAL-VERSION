@extends('layouts.app')
@section('title', 'Add Teacher')
@section('subtitle', 'Register a new teacher with personal info and course assignments')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.lecturers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> Back to Teachers
        </a>
    </div>

    <form method="POST" action="{{ route('admin.lecturers.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        {{-- ── Personal Information ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-yellow-50">
                <h3 class="text-sm font-bold text-yellow-900 flex items-center gap-2">
                    <i class="fas fa-user-tie text-yellow-600"></i> Personal Information
                </h3>
            </div>
            <div class="px-6 py-6 grid grid-cols-1 md:grid-cols-2 gap-5">

                {{-- Photo --}}
                <div class="md:col-span-2 flex items-start gap-6">
                    <div id="photo-preview" class="w-24 h-24 rounded-2xl bg-gray-100 flex items-center justify-center overflow-hidden border-2 border-dashed border-gray-300 flex-shrink-0">
                        <i class="fas fa-camera text-gray-400 text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">
                            Profile Photo
                        </label>
                        <input type="file" name="photo" accept="image/*" id="photo-input"
                               onchange="previewPhoto(this)"
                               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 transition-colors">
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG or WebP · max 3 MB</p>
                        @error('photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Full Name --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 @error('full_name') border-red-400 @enderror"
                           placeholder="e.g. Rev. Dr. James Mensah">
                    @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 @error('email') border-red-400 @enderror"
                           placeholder="teacher@college.edu">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Min. 6 characters">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="+233 24 000 0000">
                </div>

                {{-- Date of Birth --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400">
                </div>

                {{-- Gender --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 bg-white">
                        <option value="">— Select —</option>
                        <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Nationality --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="e.g. Ghanaian">
                </div>

                {{-- Qualification --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Qualification / Title</label>
                    <input type="text" name="qualification" value="{{ old('qualification') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="e.g. M.Div, Rev., Dr., B.Th">
                </div>

                {{-- Address --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400"
                           placeholder="Residential address">
                </div>

                {{-- Bio --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Short Bio</label>
                    <textarea name="bio" rows="3"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"
                              placeholder="Brief description about this teacher…">{{ old('bio') }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── Course Assignments ── --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-blue-50">
                <h3 class="text-sm font-bold text-blue-900 flex items-center gap-2">
                    <i class="fas fa-book-open text-blue-600"></i> Course Assignments
                    <span class="text-xs font-normal text-blue-500 ml-1">— Tick the courses this teacher handles</span>
                </h3>
            </div>
            <div class="px-6 py-6 space-y-6">
                @forelse($programs as $program)
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-5 h-5 rounded-full bg-yellow-500 text-white text-[10px] font-bold flex items-center justify-center flex-shrink-0">
                            {{ $program->sequence }}
                        </span>
                        <p class="text-sm font-bold text-gray-800">{{ $program->name }}</p>
                        <button type="button" onclick="toggleProgram({{ $program->id }}, true)"
                                class="ml-auto text-[10px] font-semibold text-blue-600 hover:underline">All</button>
                        <button type="button" onclick="toggleProgram({{ $program->id }}, false)"
                                class="text-[10px] font-semibold text-gray-400 hover:underline">None</button>
                    </div>
                    @if($program->courses->isEmpty())
                        <p class="text-xs text-gray-400 italic ml-7">No courses in this program yet.</p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 ml-7" id="program-{{ $program->id }}">
                            @foreach($program->courses as $course)
                            <label class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 hover:border-blue-200 hover:bg-blue-50/40 cursor-pointer transition-colors">
                                <input type="checkbox"
                                       name="assignments[{{ $course->id }}]"
                                       value="1"
                                       data-program="{{ $program->id }}"
                                       class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-400"
                                       {{ is_array(old('assignments')) && isset(old('assignments')[$course->id]) ? 'checked' : '' }}>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $course->name }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ $course->code }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    @endif
                </div>
                @empty
                <p class="text-sm text-gray-400 text-center py-6">No programs configured yet. Add programs and courses first.</p>
                @endforelse
            </div>
        </div>

        {{-- ── Submit ── --}}
        <div class="flex items-center gap-3">
            <button type="submit"
                    class="px-7 py-3 bg-yellow-600 hover:bg-yellow-700 text-white rounded-xl text-sm font-semibold shadow-sm transition-all">
                <i class="fas fa-user-plus mr-2"></i> Add Teacher
            </button>
            <a href="{{ route('admin.lecturers.index') }}"
               class="px-7 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors">
                Cancel
            </a>
        </div>

    </form>
</div>

<script>
function previewPhoto(input) {
    const preview = document.getElementById('photo-preview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function toggleProgram(programId, check) {
    document.querySelectorAll(`#program-${programId} input[type="checkbox"]`)
        .forEach(cb => { cb.checked = check; });
}
</script>
@endsection
