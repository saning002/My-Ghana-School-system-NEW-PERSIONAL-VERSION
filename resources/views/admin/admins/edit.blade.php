@extends('layouts.app')
@section('title', 'Edit Administrator')
@section('subtitle', 'Update administration account details')

@section('content')
<div class="mb-6">
    <a href="{{ route('admin.admins.index') }}" class="flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
        <i class="fas fa-arrow-left"></i> Back to Administrators
    </a>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50">
            <h2 class="text-lg font-bold text-gray-800">Edit Administrator</h2>
            <p class="text-sm text-gray-500 mt-1">Update profile, branch settings, and privileges for {{ $admin->full_name }}</p>
        </div>
        <form method="POST" action="{{ route('admin.admins.update', $admin) }}" class="px-8 py-6 space-y-5">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="col-span-full">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Full Name *</label>
                    <input type="text" name="full_name" value="{{ old('full_name', $admin->full_name) }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="e.g. John Doe">
                    @error('full_name')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Email Address *</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="e.g. admin@college.edu">
                    @error('email')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Phone Number</label>
                    <input type="tel" name="phone" value="{{ old('phone', $admin->phone) }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="e.g. +233 123 456 7890">
                    @error('phone')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Ministry Branch (Optional)</label>
                    <select name="church_branch_id"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent bg-white">
                        <option value="">Global / No Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ old('church_branch_id', $admin->church_branch_id) == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }} ({{ $branch->code ? strtoupper($branch->code) : $branch->getCodeOrDerived() }})
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-1">Leave empty to grant global administrative access across all branches.</p>
                    @error('church_branch_id')
                        <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center pt-6">
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="hidden" name="is_super_admin" value="0">
                        @if(auth()->id() === $admin->id)
                        {{-- Prevent unchecking super admin status on oneself --}}
                        <input type="hidden" name="is_super_admin" value="1">
                        <input type="checkbox" checked disabled
                               class="w-5 h-5 border border-gray-300 rounded-md text-gray-400 focus:ring-offset-0 focus:outline-none cursor-not-allowed">
                        @else
                        <input type="checkbox" name="is_super_admin" value="1" {{ old('is_super_admin', $admin->is_super_admin) ? 'checked' : '' }}
                               class="w-5 h-5 border border-gray-300 rounded-md text-primary-600 focus:ring-primary-500 focus:ring-offset-0 focus:outline-none accent-primary-500">
                        @endif
                        <div>
                            <span class="block text-sm font-bold text-gray-800">Grant Super Admin Privileges</span>
                            <span class="block text-xs text-gray-400">Allows managing branches and other administrators.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="border-t border-gray-100 pt-5">
                <h3 class="text-sm font-bold text-gray-800 mb-3">Reset Password (Optional)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">New Password</label>
                        <input type="password" name="password"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Min. 6 characters">
                        @error('password')
                            <p class="text-red-600 text-xs mt-2">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Confirm New Password</label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                            placeholder="Confirm new password">
                    </div>
                </div>
                <p class="text-xs text-gray-400 mt-2">Leave blank if you do not want to reset or change the password.</p>
            </div>

            <div class="flex items-center gap-3 pt-3">
                <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm btn-gold">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
                <a href="{{ route('admin.admins.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
