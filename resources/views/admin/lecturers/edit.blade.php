extends('layouts.app')
@section('title', 'Edit Teacher')
@section('subtitle', 'Update teacher details and course assignments')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('admin.lecturers.show', $lecturer) }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fas fa-arrow-left mr-1"></i> Back to Profile
        </a>
    </div>

    <form method="POST" action="{{ route('admin.lecturers.update', $lecturer) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf @method('PUT'?'photo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Full Name --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $lecturer->full_name) }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] @error('full_name') border-red-400 @enderror">
                    @error('full_name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $lecturer->email) }}" required
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] @error('email') border-red-400 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- New Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">New Password</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                           placeholder="Leave blank to keep current">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $lecturer->phone) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                </div>

                {{-- Date of Birth --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Date of Birth</label>
                    <input type="date" name="date_of_birth"
                           value="{{ old('date_of_birth', $lecturer->date_of_birth?->format('Y-m-d')) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                </div>

                {{-- Gender --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Gender</label>
                    <select name="gender" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] bg-white">
                        <option value="">– Select –</option>
                        <option value="male"   {{ old('gender', $lecturer->gender) === 'male'   ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $lecturer->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other"  {{ old('gender', $lecturer->gender) === 'other'  ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                {{-- Nationality --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $lecturer->nationality) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                </div>

                {{-- Qualification --}}
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Qualification / Title</label>
                    <input type="text" name="qualification" value="{{ old('qualification', $lecturer->qualification) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]"
                           placeholder="e.g. M.Div, Rev., Dr., B.Th">
                </div>

                {{-- Address --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Address</label>
                    <input type="text" name="address" value="{{ old('address', $lecturer->address) }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)]">
                </div>

                {{-- Bio --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Short Bio</label>
                    <textarea name="bio" rows="3"
                              class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[var(--brand)] resize-none">{{ old('bio'?'assignments'))
                                            ? isset(old('assignments'?'admin.lecturers.show', $lecturer) }}"
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
