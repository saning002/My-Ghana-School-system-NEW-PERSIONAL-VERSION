@extends('layouts.app')
@section('title', 'Edit Student')
@section('subtitle', 'Update student information')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card overflow-hidden border-0 shadow-[0_10px_40px_-10px_rgba(0,0,0,0.1)]">
        <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-[18px] font-extrabold text-slate-800 tracking-tight">{{ $student->user->full_name }}</h2>
            <p class="text-[13px] text-slate-500 mt-1 font-medium">Student ID: <span class="font-mono text-slate-700 font-bold">{{ $student->student_id }}</span></p>
        </div>

        @if ($errors->any())
            <div class="px-8 py-4 bg-red-50 border-b border-red-100">
                <div class="flex items-start gap-3">
                    <i class="fas fa-exclamation-circle text-red-500 text-lg mt-0.5 flex-shrink-0"></i>
                    <div class="flex-1">
                        <h3 class="font-bold text-red-700 text-sm mb-2">Please fix the errors below:</h3>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li class="text-red-600 text-sm">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.students.update', $student) }}" enctype="multipart/form-data" class="px-8 py-8 space-y-6">
            @csrf @method('PUT')

            {{-- Photo --}}
            <div>
                <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Student Photo</label>
                <div class="flex items-center gap-5">
                    <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden flex-shrink-0 bg-slate-50 flex items-center justify-center shadow-inner relative group" id="photo-preview">
                        @if($student->photo)
                            <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors z-10"></div>
                                <img src="{{ filter_var($student->photo, FILTER_VALIDATE_URL) ? $student->photo : route('admin.students.photo', $student) }}" class="w-full h-full object-cover relative z-0">
                        @else
                            <i class="fas fa-user text-slate-300 text-3xl"></i>
                        @endif
                    </div>
                    <div class="flex-1">
                        <input type="file" name="photo" accept="image/*"
                            class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('photo') ? 'border-red-500 bg-red-50' : '' }}"
                            onchange="previewPhoto(this)">
                        <p class="text-[12px] text-slate-400 mt-2 font-medium">Leave blank to keep current photo.</p>
                        @error('photo')
                            <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

                {{-- Background Photo --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Background Photo</label>
                    <div class="flex items-center gap-5">
                        <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-200 overflow-hidden flex-shrink-0 bg-slate-50 flex items-center justify-center shadow-inner relative group" id="background-photo-preview">
                            @if($student->background_photo)
                                <div class="absolute inset-0 bg-black/20 group-hover:bg-transparent transition-colors z-10"></div>
                                <img src="{{ filter_var($student->background_photo, FILTER_VALIDATE_URL) ? $student->background_photo : route('admin.students.photo', $student) . '?type=background' }}" class="w-full h-full object-cover relative z-0">
                            @else
                                <i class="fas fa-image text-slate-300 text-3xl"></i>
                            @endif
                        </div>
                        <div class="flex-1">
                            <input type="file" name="background_photo" accept="image/*"
                                class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('background_photo') ? 'border-red-500 bg-red-50' : '' }}"
                                onchange="previewBackgroundPhoto(this)">
                            <p class="text-[12px] text-slate-400 mt-2 font-medium">Leave blank to keep current background photo.</p>
                            @error('background_photo')
                                <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $student->user->full_name) }}" required
                        class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('full_name') ? 'border-red-500 bg-red-50' : '' }}">
                    @error('full_name')
                        <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $student->user->email) }}" required
                        class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('email') ? 'border-red-500 bg-red-50' : '' }}">
                    @error('email')
                        <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Portal Password</label>
                    <input type="password" name="portal_password"
                        class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('portal_password') ? 'border-red-500 bg-red-50' : '' }}"
                        placeholder="Leave blank to keep current password">
                    <p class="text-[11px] text-slate-500 mt-1">For student portal login. Leave blank to keep current.</p>
                    @error('portal_password')
                        <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone', $student->user->phone) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Date of Birth</label>
                    <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $student->user->date_of_birth?->format('Y-m-d')) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Gender</label>
                    <select name="gender" class="input-premium w-full px-4 py-3 text-[14px]">
                        <option value="">Select</option>
                        <option value="male" {{ old('gender', $student->user->gender) === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender', $student->user->gender) === 'female' ? 'selected' : '' }}>Female</option>
                        <option value="other" {{ old('gender', $student->user->gender) === 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Marital Status</label>
                    <select name="marital_status" class="input-premium w-full px-4 py-3 text-[14px]">
                        <option value="">Select</option>
                        <option value="single"   {{ old('marital_status', $student->user->marital_status) === 'single'   ? 'selected' : '' }}>Single</option>
                        <option value="married"  {{ old('marital_status', $student->user->marital_status) === 'married'  ? 'selected' : '' }}>Married</option>
                        <option value="divorced" {{ old('marital_status', $student->user->marital_status) === 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed"  {{ old('marital_status', $student->user->marital_status) === 'widowed'  ? 'selected' : '' }}>Widowed</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Nationality</label>
                    <input type="text" name="nationality" value="{{ old('nationality', $student->user->nationality) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Native Town</label>
                    <input type="text" name="native_town" value="{{ old('native_town', $student->native_town) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Qualifications</label>
                    <select name="qualifications" class="input-premium w-full px-4 py-3 text-[14px]">
                        <option value="">Select</option>
                        @foreach(['WASSCE','Diploma','BECE','Degree','HND','Masters','PhD','Other'] as $q)
                        <option value="{{ $q }}" {{ old('qualifications', $student->qualifications) === $q ? 'selected' : '' }}>{{ $q }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Enrollment Year</label>
                    <input type="text" name="enrollment_year" value="{{ old('enrollment_year', $student->enrollment_year) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Study Mode</label>
                    <select name="study_mode" class="input-premium w-full px-4 py-3 text-[14px]">
                        <option value="full_time" {{ old('study_mode', $student->study_mode) === 'full_time' ? 'selected' : '' }}>Full Time</option>
                        <option value="part_time" {{ old('study_mode', $student->study_mode) === 'part_time' ? 'selected' : '' }}>Part Time</option>
                    </select>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Profession</label>
                    <input type="text" name="profession" value="{{ old('profession', $student->profession) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Program *</label>
                    <select name="program_id" required class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('program_id') ? 'border-red-500 bg-red-50' : '' }}">
                        <option value="">Select a program</option>
                        @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id', $student->program_id) == $program->id ? 'selected' : '' }}>{{ $program->name }}</option>
                        @endforeach
                    </select>
                    @error('program_id')
                        <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Ministry Branch *</label>
                    <div x-data="branchCombo({{ json_encode($branches->map(fn($b)=>['id'=>$b->id,'name'=>$b->name])) }}, '{{ old('church_branch_id', $student->church_branch_id) }}', '{{ old('branch_new') }}')"
                         class="relative">
                        <input type="text"
                            x-model="query"
                            @focus="open=true"
                            @input="open=true; filterList()"
                            @keydown.escape="open=false"
                            @keydown.arrow-down.prevent="highlight=Math.min(highlight+1,filtered.length-1)"
                            @keydown.arrow-up.prevent="highlight=Math.max(highlight-1,0)"
                            @keydown.enter.prevent="selectHighlighted()"
                            @blur="delayClose()"
                            class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('church_branch_id') ? 'border-red-500 bg-red-50' : '' }}"
                            placeholder="Search or type new branch name…"
                            autocomplete="off">
                        <input type="hidden" name="church_branch_id" :value="selectedId">
                        <input type="hidden" name="branch_new"       :value="selectedId ? '' : query.trim()">
                        <div x-show="open && filtered.length > 0" x-cloak
                            class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-52 overflow-y-auto">
                            <template x-for="(item, idx) in filtered" :key="item.id ?? item.name">
                                <div @mousedown.prevent="select(item)"
                                    :class="idx===highlight ? 'bg-yellow-50 text-yellow-700' : 'text-slate-700'"
                                    class="px-4 py-2.5 text-[14px] font-semibold cursor-pointer hover:bg-yellow-50 flex items-center gap-3">
                                    <i class="fas fa-church text-xs opacity-50"></i>
                                    <span x-text="item.name"></span>
                                    <span x-show="!item.id" class="ml-auto text-[11px] font-bold uppercase tracking-wider text-slate-400">New branch</span>
                                </div>
                            </template>
                        </div>
                        <p x-show="query.trim() && !selectedId" x-cloak class="text-[12px] font-semibold text-yellow-600 mt-2">
                            <i class="fas fa-plus-circle mr-1"></i>A new branch "<span x-text="query.trim()"></span>" will be created.
                        </p>
                        @error('church_branch_id')
                            <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Status *</label>
                    <select name="status" required class="input-premium w-full px-4 py-3 text-[14px] {{ $errors->has('status') ? 'border-red-500 bg-red-50' : '' }}">
                        <option value="">Select a status</option>
                        <option value="active"        {{ old('status', $student->status) === 'active'        ? 'selected' : '' }}>Active</option>
                        <option value="graduated"     {{ old('status', $student->status) === 'graduated'     ? 'selected' : '' }}>Graduated</option>
                        <option value="suspended"     {{ old('status', $student->status) === 'suspended'     ? 'selected' : '' }}>Suspended</option>
                        <option value="manifestation" {{ old('status', $student->status) === 'manifestation' ? 'selected' : '' }}>Manifestation</option>
                        <option value="withdrawn"     {{ old('status', $student->status) === 'withdrawn'     ? 'selected' : '' }}>Withdrawn</option>
                    </select>
                    @error('status')
                        <p class="text-red-600 text-sm mt-1 flex items-center gap-1"><i class="fas fa-info-circle"></i>{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Results Block Status</label>
                    <label class="flex items-center gap-3 input-premium px-4 py-3 cursor-pointer select-none border-red-100 bg-red-50/20 hover:bg-red-50/40 transition-colors">
                        <input type="checkbox" name="results_blocked" value="1" {{ old('results_blocked', $student->results_blocked) ? 'checked' : '' }} class="w-4 h-4 rounded text-red-600 focus:ring-red-500 border-slate-300">
                        <span class="text-[14px] font-semibold text-red-700 flex items-center gap-1.5"><i class="fas fa-lock text-red-500 text-xs"></i> Block Student Results</span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Address</label>
                    <textarea name="address" rows="2"
                        class="input-premium w-full px-4 py-3 text-[14px] resize-none">{{ old('address', $student->user->address) }}</textarea>
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Signal</label>
                    <input type="text" name="signal" value="{{ old('signal', $student->signal) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]"
                        placeholder="e.g. H+R=P">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Position</label>
                    <input type="text" name="position" value="{{ old('position', $student->position) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]"
                        placeholder="e.g. Student">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Date Issued</label>
                    <input type="date" name="date_issued" value="{{ old('date_issued', $student->date_issued?->format('Y-m-d')) }}"
                        class="input-premium w-full px-4 py-3 text-[14px]">
                </div>
            </div>

            {{-- Course Enrollments --}}
            <div id="course-enrollment-panel">
                <div class="border border-slate-100 rounded-2xl overflow-hidden">
                    <div class="px-6 py-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-[13px] font-bold text-slate-700 uppercase tracking-wider">Course Enrollments</h3>
                            <p class="text-[12px] text-slate-400 mt-0.5">Tick to enroll, untick to remove from course</p>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="toggleAllCourses(true)"
                                class="px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-[11px] font-bold transition-colors">
                                Select All
                            </button>
                            <button type="button" onclick="toggleAllCourses(false)"
                                class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 rounded-lg text-[11px] font-bold transition-colors">
                                Deselect All
                            </button>
                        </div>
                    </div>
                    <div class="p-6" id="courses-checkbox-grid">
                        @php
                            $currentProgramId = old('program_id', $student->program_id);
                            $programForCourses = $programs->firstWhere('id', $currentProgramId);
                            $programCourses = $programForCourses?->courses ?? collect();
                        @endphp
                        @if($programCourses->isEmpty())
                            <p class="text-[13px] text-slate-400 italic text-center py-4">No courses found for this program.</p>
                        @else
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach($programCourses as $course)
                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-slate-100 hover:border-primary-200 hover:bg-primary-50/30 cursor-pointer transition-all group">
                                        <input type="checkbox"
                                            name="course_ids[]"
                                            value="{{ $course->id }}"
                                            {{ in_array($course->id, $enrolledCourseIds) ? 'checked' : '' }}
                                            class="mt-0.5 w-4 h-4 rounded accent-primary-600 cursor-pointer flex-shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <span class="text-[13px] font-semibold text-slate-700 group-hover:text-primary-700 transition-colors block leading-tight">
                                                {{ $course->name }}
                                            </span>
                                            @if($course->code)
                                                <span class="text-[11px] text-slate-400 font-mono">{{ $course->code }}</span>
                                            @endif
                                        </div>
                                        <span class="{{ in_array($course->id, $enrolledCourseIds) ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-400' }} text-[10px] font-bold px-2 py-0.5 rounded-full flex-shrink-0 mt-0.5 course-badge">
                                            {{ in_array($course->id, $enrolledCourseIds) ? 'Enrolled' : 'Not enrolled' }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3 pt-6 border-t border-slate-100">
                <button type="submit" class="btn-gold flex-1 sm:flex-none px-8 py-3.5 rounded-2xl text-[14px] font-bold shadow-[0_4px_14px_0_rgba(212,160,23,0.39)] shrink-0 active:scale-95 flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
                <a href="{{ route('admin.students.index') }}" class="flex-1 sm:flex-none px-8 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-[14px] font-bold transition-colors text-center active:scale-95">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAllCourses(checked) {
    document.querySelectorAll('#courses-checkbox-grid input[type="checkbox"]').forEach(function(cb) {
        cb.checked = checked;
        const badge = cb.closest('label').querySelector('.course-badge');
        if (badge) {
            if (checked) {
                badge.className = badge.className.replace('bg-slate-100 text-slate-400', 'bg-emerald-100 text-emerald-700');
                badge.textContent = 'Enrolled';
            } else {
                badge.className = badge.className.replace('bg-emerald-100 text-emerald-700', 'bg-slate-100 text-slate-400');
                badge.textContent = 'Not enrolled';
            }
        }
    });
}

// Update badge label when individual checkbox is toggled
document.addEventListener('change', function(e) {
    if (e.target.matches('#courses-checkbox-grid input[type="checkbox"]')) {
        const badge = e.target.closest('label').querySelector('.course-badge');
        if (badge) {
            if (e.target.checked) {
                badge.className = badge.className.replace('bg-slate-100 text-slate-400', 'bg-emerald-100 text-emerald-700');
                badge.textContent = 'Enrolled';
            } else {
                badge.className = badge.className.replace('bg-emerald-100 text-emerald-700', 'bg-slate-100 text-slate-400');
                badge.textContent = 'Not enrolled';
            }
        }
    }
});

function branchCombo(branches, oldId, oldNew) {
    return {
        branches,
        query: oldNew || (oldId ? (branches.find(b => b.id == oldId)?.name || '') : ''),
        selectedId: oldId || null,
        filtered: [],
        open: false,
        highlight: 0,
        init() { this.filtered = this.branches; },
        filterList() {
            const q = this.query.toLowerCase().trim();
            this.selectedId = null;
            this.highlight = 0;
            if (!q) { this.filtered = this.branches; return; }
            const matched = this.branches.filter(b => b.name.toLowerCase().includes(q));
            const exact = this.branches.find(b => b.name.toLowerCase() === q);
            if (!exact && q.length >= 2) matched.push({ id: null, name: this.query.trim() });
            this.filtered = matched;
        },
        select(item) {
            this.query      = item.name;
            this.selectedId = item.id ?? null;
            this.open       = false;
        },
        selectHighlighted() {
            if (this.filtered[this.highlight]) this.select(this.filtered[this.highlight]);
        },
        delayClose() { setTimeout(() => this.open = false, 120); },
    };
}

function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Photo must not exceed 2MB. Current file: ' + (file.size / (1024 * 1024)).toFixed(2) + 'MB');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('photo-preview');
            preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
        };
        reader.readAsDataURL(file);
    }
}

function previewBackgroundPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        
        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select a valid image file');
            input.value = '';
            return;
        }
        
        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Background photo must not exceed 5MB. Current file: ' + (file.size / (1024 * 1024)).toFixed(2) + 'MB');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('background-photo-preview');
            preview.innerHTML = '<img src="' + e.target.result + '" class="w-full h-full object-cover">';
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endsection
