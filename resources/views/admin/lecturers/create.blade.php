extends('layouts.app')
@section('title', 'Add Teacher')
@section('subtitle', 'Register a new teacher with personal info and course assignments')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.lecturers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> Back to Teachers
        </a>
    </div>

    <form method="POST" action="{{ route('admin.lecturers.store'?'photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Full Name --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] @error('full_name') border-red-400 @enderror"
                           placeholder="e.g. Rev. Dr. James Mensah">
                    @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] @error('email') border-red-400 @enderror"
                           placeholder="teacher@college.edu">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password *</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                           placeholder="Min. 6 characters">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                           placeholder="+233 24 000 0000">
                </div>

                {{-- Date of Birth --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                </div>

                {{-- Gender --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] bg-white">
                        <option value="">– Select –</option>
                        <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender') === 'other'  ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Nationality --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                           placeholder="e.g. Ghanaian">
                </div>

                {{-- Qualification --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Qualification / Title</label>
                    <input type="text" name="qualification" value="{{ old('qualification') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                           placeholder="e.g. M.Div, Rev., Dr., B.Th">
                </div>

                {{-- Address --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address'?'bio'?'assignments')) && isset(old('assignments')[$course->id]) ? 'checked' : ''?'admin.lecturers.index') }}"
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
