@extends('layouts.app')

@section('title', 'Teacher Profile')
@section('subtitle', 'Update your professional details and security credentials')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="{
    photoPreview: null,
    previewImage(event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (e) => { this.photoPreview = e.target.result; };
            reader.readAsDataURL(file);
        }
    }
}">
    {{-- Header Banner --}}
    <div class="card border border-amber-200/60 bg-gradient-to-r from-amber-50 via-amber-50/40 to-white p-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Account Settings</p>
                <h2 class="text-2xl font-extrabold text-slate-900 mt-1">Teacher Profile</h2>
                <p class="text-xs text-slate-600 mt-0.5">Keep your contact information, credentials, and security password up to date.</p>
            </div>
            <div class="inline-flex items-center gap-2 rounded-full border border-amber-300/60 bg-white px-3.5 py-1.5 text-xs font-bold text-amber-800 shadow-sm shrink-0">
                <i class="fas fa-user-circle text-amber-500"></i> Profile Management
            </div>
        </div>
    </div>

    {{-- Main Profile Form --}}
    <form method="POST" action="{{ route('lecturer.profile.update') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Avatar & Summary --}}
            <div class="lg:col-span-1 space-y-6">
                <div class="card p-6 border border-slate-200 bg-white text-center space-y-4">
                    <div class="relative w-28 h-28 mx-auto">
                        <template x-if="photoPreview">
                            <img :src="photoPreview" alt="Profile Preview" class="w-28 h-28 rounded-full object-cover border-4 border-amber-400 shadow-md">
                        </template>
                        <template x-if="!photoPreview">
                            @if($lecturer->photo)
                                <img src="{{ $lecturer->photo_url ?? $lecturer->photo }}" alt="Profile Photo" class="w-28 h-28 rounded-full object-cover border-4 border-amber-400 shadow-md">
                            @else
                                <div class="w-28 h-28 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center font-extrabold text-3xl border-4 border-amber-300 shadow-inner">
                                    {{ strtoupper(substr($lecturer->full_name ?? 'T', 0, 1)) }}
                                </div>
                            @endif
                        </template>
                        <label for="photo" class="absolute bottom-0 right-0 w-8 h-8 rounded-full bg-slate-900 hover:bg-amber-600 text-white flex items-center justify-center cursor-pointer shadow-lg transition-colors">
                            <i class="fas fa-camera text-xs"></i>
                            <input type="file" name="photo" id="photo" accept="image/*" @change="previewImage" class="hidden">
                        </label>
                    </div>

                    <div>
                        <h3 class="text-lg font-extrabold text-slate-900 leading-tight">{{ $lecturer->full_name }}</h3>
                        <p class="text-xs font-semibold text-amber-700 mt-0.5">{{ $lecturer->qualification ?? 'Educator' }}</p>
                        <p class="text-xs text-slate-400 mt-1 truncate">{{ auth()->user()->email }}</p>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-center gap-2">
                        <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
                            Active Teacher
                        </span>
                    </div>
                </div>
            </div>

            {{-- Right Column: Form Fields --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Personal Information --}}
                <div class="card p-6 border border-slate-200 bg-white space-y-4">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Personal Information</h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="full_name" class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', $lecturer->full_name) }}" required class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all">
                        </div>

                        <div>
                            <label for="phone" class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                            <input type="text" name="phone" id="phone" value="{{ old('phone', $lecturer->phone) }}" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all" placeholder="+233 ...">
                        </div>
                    </div>

                    <div>
                        <label for="qualification" class="block text-xs font-bold text-slate-700 mb-1">Qualification / Title</label>
                        <input type="text" name="qualification" id="qualification" value="{{ old('qualification', $lecturer->qualification) }}" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all" placeholder="e.g. M.Ed Curriculum & Teaching">
                    </div>

                    <div>
                        <label for="address" class="block text-xs font-bold text-slate-700 mb-1">Residential Address</label>
                        <textarea name="address" id="address" rows="2" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all">{{ old('address', $lecturer->address) }}</textarea>
                    </div>

                    <div>
                        <label for="bio" class="block text-xs font-bold text-slate-700 mb-1">Professional Bio</label>
                        <textarea name="bio" id="bio" rows="3" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all" placeholder="A short bio about your academic background and teaching experience...">{{ old('bio', $lecturer->bio) }}</textarea>
                    </div>
                </div>

                {{-- Password Security --}}
                <div class="card p-6 border border-slate-200 bg-white space-y-4">
                    <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Security & Password</h3>
                    <p class="text-xs text-slate-500">Leave blank if you do not wish to change your password.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 mb-1">New Password</label>
                            <input type="password" name="password" id="password" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all" placeholder="••••••••">
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-xs font-bold text-slate-700 mb-1">Confirm New Password</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="w-full rounded-xl border-slate-200 bg-slate-50/50 p-3 text-sm focus:border-amber-500 focus:ring-amber-500 focus:bg-white transition-all" placeholder="••••••••">
                        </div>
                    </div>
                </div>

                {{-- Action Bar --}}
                <div class="flex justify-end">
                    <button type="submit" class="btn-gold inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-sm font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                        <i class="fas fa-save"></i> Save Profile Changes
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
