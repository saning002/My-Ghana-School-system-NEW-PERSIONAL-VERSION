@extends('layouts.app')
@section('title','Add Student')
@section('subtitle','Student Admission Form — Royal Fame International School')

@section('content')
<div class="max-w-4xl mx-auto" x-data="studentForm()">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="rounded-3xl overflow-hidden mb-5 border border-slate-200 shadow-sm">
        <div class="bg-gradient-to-r from-slate-800 to-slate-700 px-6 py-5 text-white flex items-center justify-between">
            <div>
                <h2 class="text-lg font-extrabold tracking-tight">Student Admission Form</h2>
                <p class="text-slate-300 text-xs mt-0.5">Complete all required sections — matches the official admission form</p>
            </div>
            <div class="hidden sm:flex items-center gap-3 text-xs text-slate-300">
                <span class="rounded-full bg-white/10 px-3 py-1 font-semibold">Application No: <span class="font-mono text-yellow-300">AUTO</span></span>
                <span class="rounded-full bg-white/10 px-3 py-1 font-semibold">Date: {{ now()->format('d M Y') }}</span>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION A — PUPIL'S PERSONAL INFORMATION
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-800 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">A</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION A — PUPIL'S PERSONAL INFORMATION</h3>
            </div>
            <div class="p-5 space-y-5">

                {{-- Photo --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="field-label">Passport Photo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center bg-slate-50 overflow-hidden shrink-0" id="photo-preview">
                                <i class="fas fa-user text-slate-300 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="photo" accept="image/*" onchange="previewPhoto(this)"
                                    class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 transition-colors">
                                <label class="flex items-center gap-2 mt-2 text-xs text-slate-600 cursor-pointer">
                                    <input type="checkbox" name="passport_photo_attached" value="1" class="rounded accent-yellow-500">
                                    Passport photo physically attached to form
                                </label>
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Background / Portal Photo</label>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-xl border-2 border-dashed border-slate-200 flex items-center justify-center bg-slate-50 overflow-hidden shrink-0" id="background-photo-preview">
                                <i class="fas fa-image text-slate-300 text-2xl"></i>
                            </div>
                            <div class="flex-1">
                                <input type="file" name="background_photo" accept="image/*" onchange="previewBackgroundPhoto(this)"
                                    class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-slate-100 file:text-slate-600 hover:file:bg-slate-200 transition-colors">
                                <p class="text-[11px] text-slate-400 mt-1">Full-body or scenic photo for student portal</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="field-label">Surname *</label>
                        <input type="text" name="surname" value="{{ old('surname') }}" required
                            class="field-input" placeholder="Family name">
                    </div>
                    <div>
                        <label class="field-label">First Name *</label>
                        <input type="text" name="full_name" value="{{ old('full_name') }}" required
                            class="field-input" placeholder="First name">
                    </div>
                    <div>
                        <label class="field-label">Other Name(s)</label>
                        <input type="text" name="other_names" value="{{ old('other_names') }}"
                            class="field-input" placeholder="Middle names">
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="field-label">Gender *</label>
                        <div class="flex gap-4 mt-2">
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
                                <input type="radio" name="gender" value="male" {{ old('gender')==='male'?'checked':'' }} class="accent-yellow-500"> Male
                            </label>
                            <label class="flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
                                <input type="radio" name="gender" value="female" {{ old('gender')==='female'?'checked':'' }} class="accent-yellow-500"> Female
                            </label>
                        </div>
                    </div>
                    <div>
                        <label class="field-label">Date of Birth</label>
                        <input type="date" name="date_of_birth" value="{{ old('date_of_birth') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Age</label>
                        <input type="number" name="age" value="{{ old('age') }}" min="1" max="100"
                            class="field-input" placeholder="Age">
                    </div>
                    <div>
                        <label class="field-label">Nationality</label>
                        <input type="text" name="nationality" value="{{ old('nationality','Ghanaian') }}" class="field-input">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Place of Birth</label>
                        <input type="text" name="place_of_birth" value="{{ old('place_of_birth') }}" class="field-input" placeholder="Town / City of birth">
                    </div>
                    <div>
                        <label class="field-label">Religion <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                        <input type="text" name="religion" value="{{ old('religion') }}" class="field-input" placeholder="e.g. Christianity, Islam">
                    </div>
                    <div>
                        <label class="field-label">Home Address / Residence</label>
                        <input type="text" name="address" value="{{ old('address') }}" class="field-input" placeholder="Full home address">
                    </div>
                    <div>
                        <label class="field-label">Phone</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="field-input" placeholder="+233 00 000 0000">
                    </div>
                    <div>
                        <label class="field-label">First Language Spoken</label>
                        <input type="text" name="first_language" value="{{ old('first_language') }}" class="field-input" placeholder="e.g. Twi, Fante, Ewe">
                    </div>
                    <div>
                        <label class="field-label">Other Language Spoken</label>
                        <input type="text" name="other_language" value="{{ old('other_language') }}" class="field-input" placeholder="e.g. English, French">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Class / Program Applying For *</label>
                        <select name="program_id" required x-model="selectedProgram" @change="loadCourses(); generateStudentId()"
                            class="field-input">
                            <option value="">— Select Program —</option>
                            @foreach($programs as $program)
                            <option value="{{ $program->id }}" {{ old('program_id')==$program->id?'selected':'' }}>{{ $program->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Proposed Date of Admission *</label>
                        <input type="date" name="admission_date" value="{{ old('admission_date', date('Y-m-d')) }}" required class="field-input">
                    </div>
                </div>

                {{-- Courses (dynamic) --}}
                <div x-show="courses.length > 0" x-cloak>
                    <div class="flex items-center justify-between mb-2">
                        <label class="field-label mb-0">Courses / Subjects</label>
                        <button type="button" @click="toggleAll()"
                            class="text-[11px] text-yellow-700 font-bold px-3 py-1 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-200 transition-colors">
                            <span x-text="allSelected ? 'Deselect All' : 'Select All'"></span>
                        </button>
                    </div>
                    <div class="border border-slate-200 rounded-xl overflow-hidden divide-y divide-slate-100 bg-white">
                        <template x-for="course in courses" :key="course.id">
                            <label class="flex items-center gap-3 px-4 py-3 cursor-pointer hover:bg-slate-50">
                                <input type="checkbox" :name="'courses[]'" :value="course.id" x-model="selectedCourses"
                                    class="w-4 h-4 accent-yellow-500 rounded">
                                <div>
                                    <p class="text-sm font-semibold text-slate-800" x-text="course.name"></p>
                                    <p class="text-xs text-slate-400 font-mono" x-text="course.code"></p>
                                </div>
                            </label>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION B — PREVIOUS SCHOOL DETAILS
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">B</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION B — PREVIOUS SCHOOL DETAILS</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Name of Previous School</label>
                        <input type="text" name="prev_school_name" value="{{ old('prev_school_name') }}" class="field-input" placeholder="e.g. Accra Academy">
                    </div>
                    <div>
                        <label class="field-label">Address of Previous School</label>
                        <input type="text" name="prev_school_address" value="{{ old('prev_school_address') }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Last Class Completed</label>
                        <input type="text" name="last_class_completed" value="{{ old('last_class_completed') }}" class="field-input" placeholder="e.g. Class 6, JHS 3, SHS 2">
                    </div>
                    <div>
                        <label class="field-label">Reason for Leaving</label>
                        <input type="text" name="reason_for_leaving" value="{{ old('reason_for_leaving') }}" class="field-input" placeholder="e.g. Graduated, Relocated">
                    </div>
                </div>
                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
                        <input type="checkbox" name="prev_reports_attached" value="1" {{ old('prev_reports_attached')?'checked':'' }} class="rounded accent-yellow-500">
                        Previous Academic Reports Attached
                    </label>
                    <label class="flex items-center gap-2 text-sm font-medium text-slate-700 cursor-pointer">
                        <input type="checkbox" name="transfer_letter_attached" value="1" {{ old('transfer_letter_attached')?'checked':'' }} class="rounded accent-yellow-500">
                        Transfer Letter Attached
                    </label>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION C — FATHER'S INFORMATION
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">C</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION C — FATHER'S INFORMATION</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Full Name</label>
                    <input type="text" name="father_name" value="{{ old('father_name') }}" class="field-input" placeholder="Father's full name">
                </div>
                <div>
                    <label class="field-label">Occupation</label>
                    <input type="text" name="father_occupation" value="{{ old('father_occupation') }}" class="field-input" placeholder="e.g. Accountant, Trader">
                </div>
                <div>
                    <label class="field-label">Employer / Business Name</label>
                    <input type="text" name="father_employer" value="{{ old('father_employer') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Residential Address</label>
                    <input type="text" name="father_address" value="{{ old('father_address') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Telephone Number</label>
                    <input type="text" name="father_phone" value="{{ old('father_phone') }}" class="field-input" placeholder="+233 00 000 0000">
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION D — MOTHER'S INFORMATION
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">D</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION D — MOTHER'S INFORMATION</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Full Name</label>
                    <input type="text" name="mother_name" value="{{ old('mother_name') }}" class="field-input" placeholder="Mother's full name">
                </div>
                <div>
                    <label class="field-label">Occupation</label>
                    <input type="text" name="mother_occupation" value="{{ old('mother_occupation') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Employer / Business Name</label>
                    <input type="text" name="mother_employer" value="{{ old('mother_employer') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Residential Address</label>
                    <input type="text" name="mother_address" value="{{ old('mother_address') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Telephone Number</label>
                    <input type="text" name="mother_phone" value="{{ old('mother_phone') }}" class="field-input" placeholder="+233 00 000 0000">
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION E — GUARDIAN INFORMATION (IF APPLICABLE)
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">E</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION E — GUARDIAN INFORMATION <span class="font-normal text-slate-300 text-xs">(if applicable)</span></h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="field-label">Full Name</label>
                    <input type="text" name="guardian_name" value="{{ old('guardian_name') }}" class="field-input" placeholder="Guardian's full name">
                </div>
                <div>
                    <label class="field-label">Relationship to Child</label>
                    <input type="text" name="guardian_relationship" value="{{ old('guardian_relationship') }}" class="field-input" placeholder="e.g. Uncle, Aunt, Grandparent">
                </div>
                <div>
                    <label class="field-label">Occupation</label>
                    <input type="text" name="guardian_occupation" value="{{ old('guardian_occupation') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Residential Address</label>
                    <input type="text" name="guardian_address" value="{{ old('guardian_address') }}" class="field-input">
                </div>
                <div>
                    <label class="field-label">Telephone Number</label>
                    <input type="text" name="guardian_phone" value="{{ old('guardian_phone') }}" class="field-input" placeholder="+233 00 000 0000">
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION G — MEDICAL INFORMATION
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-red-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">G</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION G — MEDICAL INFORMATION</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="field-label">Known Medical Condition</label>
                    <input type="text" name="medical_condition" value="{{ old('medical_condition') }}" class="field-input" placeholder="e.g. Asthma, Diabetes — or None">
                </div>
                <div>
                    <label class="field-label">Blood Group <span class="text-slate-400 font-normal text-xs">(optional)</span></label>
                    <select name="blood_group" class="field-input">
                        <option value="">— Unknown —</option>
                        @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group')===$bg?'selected':'' }}>{{ $bg }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label">Allergies</label>
                    <input type="text" name="allergies" value="{{ old('allergies') }}" class="field-input" placeholder="e.g. Penicillin, Peanuts — or None">
                </div>
                <div class="sm:col-span-2">
                    <label class="field-label">Special Educational Needs</label>
                    <textarea name="special_educational_needs" rows="2" class="field-input resize-none" placeholder="Any learning difficulties, physical disabilities or special support needs">{{ old('special_educational_needs') }}</textarea>
                </div>
                <div>
                    <label class="field-label">Name of Family Doctor / Hospital</label>
                    <input type="text" name="family_doctor" value="{{ old('family_doctor') }}" class="field-input" placeholder="e.g. Dr. Mensah / Kasoa Polyclinic">
                </div>
                <div>
                    <label class="field-label">Doctor's Telephone Number</label>
                    <input type="text" name="doctor_phone" value="{{ old('doctor_phone') }}" class="field-input" placeholder="+233 00 000 0000">
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION H — PERSONS AUTHORIZED TO PICK UP THE CHILD
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">H</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION H — PERSONS AUTHORIZED TO PICK UP THE CHILD</h3>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="field-label">Name</label>
                    <input type="text" name="pickup_name" value="{{ old('pickup_name') }}" class="field-input" placeholder="Authorized person's name">
                </div>
                <div>
                    <label class="field-label">Relationship</label>
                    <input type="text" name="pickup_relationship" value="{{ old('pickup_relationship') }}" class="field-input" placeholder="e.g. Mother, Uncle">
                </div>
                <div>
                    <label class="field-label">Telephone Number</label>
                    <input type="text" name="pickup_phone" value="{{ old('pickup_phone') }}" class="field-input" placeholder="+233 00 000 0000">
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION I — SCHOOL CONTRIBUTION STATUS
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">I</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION I — SCHOOL CONTRIBUTION STATUS</h3>
            </div>
            <div class="p-5">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    @foreach([
                        ['admission_fee_paid',  'Admission Fee Paid'],
                        ['furniture_fee_paid',  'Furniture Support Paid'],
                        ['toiletries_paid',     'Toiletries Paid / Provided'],
                        ['uniform_paid',        'Uniforms Paid'],
                    ] as [$name, $label])
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <p class="text-xs font-bold text-slate-600 mb-2">{{ $label }}</p>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-1.5 text-sm font-medium text-slate-700 cursor-pointer">
                                <input type="radio" name="{{ $name }}" value="1" {{ old($name)==='1'?'checked':'' }} class="accent-emerald-500"> Yes
                            </label>
                            <label class="flex items-center gap-1.5 text-sm font-medium text-slate-700 cursor-pointer">
                                <input type="radio" name="{{ $name }}" value="0" {{ old($name,'0')==='0'?'checked':'' }} class="accent-red-400"> No
                            </label>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SECTION J — CONSENT AND DECLARATION
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-slate-800 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">J</span>
                <h3 class="text-sm font-extrabold tracking-wide">SECTION J — CONSENT AND DECLARATION</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-xs text-slate-600 leading-relaxed space-y-1.5">
                    <p>I / We certify that the information provided in this form is true and accurate to the best of my/our knowledge.</p>
                    <p>I / We agree to cooperate with the school and abide by the rules, regulations, and policies of Royal Fame International School.</p>
                    <p>I / We authorize the school to seek emergency medical attention for my/our child when necessary.</p>
                    <p>I / We accept to pay any amount that my/our ward is expected to pay as in tuition / feeding, exams fees, and any other expenses.</p>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Parent / Guardian Name</label>
                        <input type="text" name="consent_guardian_name" value="{{ old('consent_guardian_name') }}" class="field-input" placeholder="Signing parent or guardian name">
                    </div>
                    <div>
                        <label class="field-label">Date of Signing</label>
                        <input type="date" name="consent_date" value="{{ old('consent_date', date('Y-m-d')) }}" class="field-input">
                    </div>
                </div>
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" name="consent_signed" value="1" {{ old('consent_signed')?'checked':'' }}
                        class="mt-0.5 rounded accent-yellow-500 w-4 h-4 shrink-0">
                    <span class="text-sm font-semibold text-slate-700">I confirm the parent/guardian has signed the consent declaration on the physical form.</span>
                </label>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════════════
             SYSTEM / PORTAL DETAILS (for admin use)
        ══════════════════════════════════════════════════════════════════ --}}
        <div class="card overflow-hidden">
            <div class="section-header bg-indigo-700 text-white px-5 py-3 flex items-center gap-3">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-yellow-400 text-slate-900 text-xs font-extrabold shrink-0">
                    <i class="fas fa-cog text-[10px]"></i>
                </span>
                <h3 class="text-sm font-extrabold tracking-wide">FOR OFFICIAL USE — SYSTEM &amp; PORTAL DETAILS</h3>
            </div>
            <div class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Student ID</label>
                        <input type="text" name="student_id" id="student-id-field" value="{{ old('student_id') }}" readonly
                            class="field-input bg-slate-50 text-slate-500 cursor-not-allowed"
                            placeholder="Auto-generated on save">
                        <p class="text-[11px] text-slate-400 mt-1">
                            <i class="fas fa-info-circle text-indigo-400 mr-1"></i>
                            Preview: <strong class="text-indigo-600 font-mono" id="id-preview">{{ \App\Models\Setting::get('school_id_prefix','RFIS') }}/{{ date('Y') }}/####</strong>
                        </p>
                    </div>
                    <div>
                        <label class="field-label">School Branch *</label>
                        <div x-data="branchCombo({{ json_encode($branches->map(fn($b)=>['id'=>$b->id,'name'=>$b->name,'code'=>$b->code ?? ''])) }}, '{{ old('church_branch_id') }}', '{{ old('branch_new') }}')"
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
                                class="field-input"
                                placeholder="Search or type new branch name…"
                                autocomplete="off" required>
                            <input type="hidden" name="church_branch_id" :value="selectedId" id="branch-id-hidden">
                            <input type="hidden" name="branch_new"       :value="selectedId ? '' : query.trim()">
                            <div x-show="open && filtered.length > 0" x-cloak
                                class="absolute z-50 mt-1 w-full bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                                <template x-for="(item, idx) in filtered" :key="item.id ?? item.name">
                                    <div @mousedown.prevent="select(item)"
                                        :class="idx===highlight ? 'bg-yellow-50 text-yellow-800' : 'text-slate-700'"
                                        class="px-4 py-2.5 text-sm cursor-pointer hover:bg-yellow-50 flex items-center gap-2">
                                        <i class="fas fa-church text-xs opacity-50"></i>
                                        <span x-text="item.name"></span>
                                        <span x-show="!item.id" class="ml-auto text-xs text-slate-400 italic">New branch</span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="field-label">Login Email *</label>
                        <input type="email" name="email" value="{{ old('email') }}" class="field-input" placeholder="student@email.com — blank to auto-generate">
                        <p class="text-[11px] text-slate-400 mt-1">Leave blank to auto-assign a system email.</p>
                    </div>
                    <div>
                        <label class="field-label">Login Password *</label>
                        <input type="password" name="password" required minlength="6" class="field-input" placeholder="Min. 6 characters">
                    </div>
                    <div>
                        <label class="field-label">Student Portal Password</label>
                        <input type="password" name="portal_password" class="field-input" placeholder="Optional — for student portal">
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="field-label">Enrollment Year</label>
                        <input type="text" name="enrollment_year" value="{{ old('enrollment_year', date('Y')) }}" class="field-input">
                    </div>
                    <div>
                        <label class="field-label">Study Mode</label>
                        <select name="study_mode" class="field-input">
                            <option value="full_time" {{ old('study_mode')==='full_time'?'selected':'' }}>Full Time</option>
                            <option value="part_time" {{ old('study_mode')==='part_time'?'selected':'' }}>Part Time</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Qualifications</label>
                        <select name="qualifications" class="field-input">
                            <option value="">Select</option>
                            @foreach(['WASSCE','Diploma','BECE','Degree','HND','Masters','PhD','Other'] as $q)
                            <option value="{{ $q }}" {{ old('qualifications')===$q?'selected':'' }}>{{ $q }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Marital Status</label>
                        <select name="marital_status" class="field-input">
                            <option value="">Select</option>
                            @foreach(['single','married','divorced','widowed'] as $ms)
                            <option value="{{ $ms }}" {{ old('marital_status')===$ms?'selected':'' }}>{{ ucfirst($ms) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="field-label">Native Town</label>
                        <input type="text" name="native_town" value="{{ old('native_town') }}" class="field-input" placeholder="e.g. Kasoa, Central Region">
                    </div>
                    <div>
                        <label class="field-label">Profession</label>
                        <input type="text" name="profession" value="{{ old('profession') }}" class="field-input" placeholder="e.g. Teacher, Student">
                    </div>
                    <div>
                        <label class="field-label">Signal</label>
                        <input type="text" name="signal" value="{{ old('signal','H+R=P') }}" class="field-input">
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Errors ───────────────────────────────────────────────────────── --}}
        @if($errors->any())
        <div class="rounded-xl bg-red-50 border border-red-200 p-4">
            <p class="text-sm font-bold text-red-700 mb-2"><i class="fas fa-circle-exclamation mr-1"></i> Please fix the following errors:</p>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                <li class="text-xs text-red-600">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        {{-- ── Submit ───────────────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row gap-3 pb-6">
            <button type="submit"
                class="btn-gold flex-1 sm:flex-none px-8 py-3.5 rounded-2xl text-[14px] font-bold shadow-[0_4px_14px_0_rgba(212,160,23,0.39)] active:scale-95 flex items-center justify-center gap-2">
                <i class="fas fa-user-plus text-sm"></i> Register Student
            </button>
            <a href="{{ route('admin.students.index') }}"
               class="flex-1 sm:flex-none px-8 py-3.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-2xl text-[14px] font-bold transition-colors text-center active:scale-95">
                Cancel
            </a>
        </div>
    </form>
</div>

@push('styles')
<style>
.field-label  { display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:.04em; }
.field-input  { width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:12px; font-size:14px; background:#f8fafc; transition:all .15s; outline:none; }
.field-input:focus { border-color:#eab308; box-shadow:0 0 0 3px rgba(234,179,8,.12); background:#fff; }
.section-header { letter-spacing:.03em; }
</style>
@endpush

@push('scripts')
<script>
function branchCombo(branches, oldId, oldNew) {
    return {
        branches,
        query: oldNew || (oldId ? (branches.find(b=>b.id==oldId)?.name||'') : ''),
        selectedId: oldId||null,
        filtered: [],
        open: false,
        highlight: 0,
        init() { this.filtered = this.branches; },
        filterList() {
            const q = this.query.toLowerCase().trim();
            this.selectedId = null;
            this.highlight  = 0;
            if (!q) { this.filtered = this.branches; return; }
            const matched = this.branches.filter(b=>b.name.toLowerCase().includes(q));
            const exact   = this.branches.find(b=>b.name.toLowerCase()===q);
            if (!exact && q.length>=2) matched.push({id:null,name:this.query.trim()});
            this.filtered = matched;
        },
        select(item) {
            this.query      = item.name;
            this.selectedId = item.id??null;
            this.open       = false;
            const h = document.getElementById('branch-id-hidden');
            if(h){ h.value=this.selectedId||''; }
            generateStudentId();
        },
        selectHighlighted() { if(this.filtered[this.highlight]) this.select(this.filtered[this.highlight]); },
        delayClose() { setTimeout(()=>this.open=false,120); },
    };
}

function generateStudentId() {
    const b = document.getElementById('branch-id-hidden');
    const p = document.querySelector('select[name="program_id"]');
    const f = document.getElementById('student-id-field');
    if(!b||!p||!f||!b.value||!p.value) return;
    fetch(`/admin/students/next-id?branch_id=${b.value}&program_id=${p.value}`)
        .then(r=>r.json()).then(d=>{ if(d.id) f.value=d.id; }).catch(()=>{});
}

function previewPhoto(input) {
    if(input.files&&input.files[0]){
        const r=new FileReader();
        r.onload=e=>{document.getElementById('photo-preview').innerHTML='<img src="'+e.target.result+'" class="w-full h-full object-cover">';};
        r.readAsDataURL(input.files[0]);
    }
}

function previewBackgroundPhoto(input) {
    if(input.files&&input.files[0]){
        if(input.files[0].size>5*1024*1024){alert('Max 5MB for background photo.');input.value='';return;}
        const r=new FileReader();
        r.onload=e=>{document.getElementById('background-photo-preview').innerHTML='<img src="'+e.target.result+'" class="w-full h-full object-cover">';};
        r.readAsDataURL(input.files[0]);
    }
}

function studentForm() {
    return {
        selectedProgram: '{{ old('program_id','') }}',
        courses: [],
        selectedCourses: [],
        get allSelected() { return this.courses.length>0&&this.selectedCourses.length===this.courses.length; },
        loadCourses() {
            if(!this.selectedProgram){this.courses=[];this.selectedCourses=[];return;}
            fetch(`/admin/api/programs/${this.selectedProgram}/courses`)
                .then(r=>r.json())
                .then(data=>{ this.courses=data; this.selectedCourses=data.map(c=>c.id); });
        },
        toggleAll() {
            this.selectedCourses = this.allSelected ? [] : this.courses.map(c=>c.id);
        },
        init() { if(this.selectedProgram) this.loadCourses(); }
    }
}
</script>
@endpush
@endsection
