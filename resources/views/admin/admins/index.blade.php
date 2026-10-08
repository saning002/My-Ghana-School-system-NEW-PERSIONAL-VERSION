@extends('layouts.app')
@section('title', 'System Administrators')
@section('subtitle', 'Manage global and branch-scoped administrators')

@section('content')

<style>
    .admin-table tr:hover {
        background-color: rgba(212, 160, 23, 0.03);
    }
</style>

<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">System Administrators</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $admins->total() }} admin{{ $admins->total() !== 1 ? 's' : '' }} managing the system</p>
    </div>
    <a href="{{ route('admin.admins.create') }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700 shadow-sm transition-all">
        <i class="fas fa-plus"></i> Add Administrator
    </a>
</div>

<div class="card overflow-hidden shadow-sm border border-gray-100">
    <div class="px-6 py-5 bg-yellow-50 border-b border-yellow-200/50">
        <h3 class="text-base font-bold text-yellow-900">
            <i class="fas fa-users-cog mr-2"></i>All Administrators
        </h3>
    </div>
    <div class="table-wrap">
        <table class="w-full text-left border-collapse admin-table">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">Name & Email</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">Phone</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">Assigned Branch</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider">Privileges</th>
                    <th class="px-6 py-4 text-xs font-bold text-gray-700 uppercase tracking-wider text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($admins as $admin)
                <tr class="transition-colors duration-200">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm"
                                 style="background: {{ $admin->isSuperAdmin() ? '#D4A017' : '#64748b' }}">
                                {{ strtoupper(substr($admin->full_name, 0, 1)) }}
                            </div>
                            <div>
                                <h4 class="font-semibold text-gray-900 text-sm flex items-center gap-2">
                                    {{ $admin->full_name }}
                                    @if(auth()->id() === $admin->id)
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-bold rounded-md border border-blue-200">You</span>
                                    @endif
                                </h4>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $admin->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-600">
                        <i class="fas fa-phone text-yellow-600 mr-2"></i>{{ $admin->phone ?? '—' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($admin->branch)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-lg border border-yellow-200">
                            <i class="fas fa-building text-[10px]"></i>
                            {{ $admin->branch->name }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-100 text-blue-800 text-xs font-semibold rounded-lg border border-blue-200">
                            <i class="fas fa-globe text-[10px]"></i>
                            Global Admin
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($admin->isSuperAdmin())
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-100 text-amber-900 text-xs font-bold rounded-lg border border-amber-200">
                            <i class="fas fa-crown text-[10px]"></i>
                            Super Admin
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 text-gray-800 text-xs font-semibold rounded-lg border border-gray-200">
                            <i class="fas fa-user-lock text-[10px]"></i>
                            Standard Admin
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <a href="{{ route('admin.admins.edit', $admin) }}"
                               class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors" title="Edit">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            
                            @if(auth()->id() !== $admin->id)
                            <form method="POST" action="{{ route('admin.admins.destroy', $admin) }}" onsubmit="return confirm('Remove this administrator?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Delete">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                            @else
                            <button disabled class="w-9 h-9 rounded-lg bg-gray-50 text-gray-300 flex items-center justify-center cursor-not-allowed" title="Cannot delete yourself">
                                <i class="fas fa-trash text-xs"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-16 text-center">
                        <div class="w-20 h-20 rounded-full bg-gray-100 text-gray-400 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users-cog text-2xl"></i>
                        </div>
                        <p class="text-gray-600 font-medium text-lg mb-2">No administrators found</p>
                        <p class="text-gray-500 text-sm mb-6">Get started by adding your first administrator</p>
                        <a href="{{ route('admin.admins.create') }}"
                           class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700">
                            <i class="fas fa-plus"></i> Add Administrator
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($admins->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">{{ $admins->links('pagination::tailwind') }}</div>
    @endif
</div>

@endsection
