@extends('staff-portal.layout')
@section('title', 'My Profile')

@section('content')
<div class="max-w-2xl mx-auto space-y-5">

    {{-- Header --}}
    <div class="card p-5 bg-gradient-to-r from-amber-50 to-white border border-amber-200/60">
        <p class="text-xs font-bold uppercase tracking-wider text-amber-700">Account Settings</p>
        <h2 class="text-xl font-extrabold text-slate-900 mt-1">My Profile</h2>
        <p class="text-xs text-slate-500 mt-0.5">Update your name, phone and password.</p>
    </div>

    <form method="POST" action="{{ route('staff-portal.profile.update') }}">
        @csrf

        {{-- Avatar & Info --}}
        <div class="card p-6 border border-slate-200 bg-white flex items-center gap-5 mb-5">
            <div class="w-16 h-16 rounded-full flex items-center justify-center font-extrabold text-2xl text-white flex-shrink-0"
                 style="background:linear-gradient(135deg,{{ session('staff_portal_role_color_from','#4f46e5') }},{{ session('staff_portal_role_color_to','#6d28d9') }})">
                {{ strtoupper(substr($staffUser->full_name ?? '?', 0, 1)) }}
            </div>
            <div>
                <p class="font-extrabold text-slate-900">{{ $staffUser->full_name }}</p>
                <p class="text-xs text-slate-500">{{ $staffUser->email }}</p>
                <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-100 text-indigo-800">
                    {{ $staffUser->role_label }}
                </span>
            </div>
        </div>

        {{-- Personal Info --}}
        <div class="card p-6 border border-slate-200 bg-white space-y-4 mb-5">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Personal Information</h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $staffUser->full_name) }}" required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Phone Number</label>
                    <input type="text" name="phone" value="{{ old('phone', $staffUser->phone) }}"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all" placeholder="+233 ...">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Email Address</label>
                <input type="email" value="{{ $staffUser->email }}" disabled
                    class="w-full rounded-xl border border-slate-200 bg-slate-100 px-3 py-2.5 text-sm text-slate-400 cursor-not-allowed">
                <p class="text-[11px] text-slate-400 mt-1">Contact the administrator to change your email.</p>
            </div>
        </div>

        {{-- Password --}}
        <div class="card p-6 border border-slate-200 bg-white space-y-4 mb-5">
            <h3 class="text-sm font-extrabold uppercase tracking-wider text-slate-800 pb-2 border-b border-slate-100">Change Password</h3>
            <p class="text-xs text-slate-500">Leave blank to keep your current password.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">New Password</label>
                    <input type="password" name="password"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all" placeholder="••••••••">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Confirm Password</label>
                    <input type="password" name="password_confirmation"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:bg-white transition-all" placeholder="••••••••">
                </div>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit"
                class="inline-flex items-center gap-2 px-8 py-3 rounded-2xl text-sm font-bold text-white shadow-md transition-all active:scale-95"
                style="background:linear-gradient(135deg,#4f46e5,#6d28d9)">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>
@endsection
