@extends('layouts.app')
@section('title', 'Branch Admins')
@section('subtitle', 'Manage admins for ' . $branch->name)

@section('content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-5">
        <a href="{{ route('admin.branches.index') }}" class="flex items-center gap-1 text-sm font-semibold text-blue-600 hover:text-blue-700">
            <i class="fas fa-arrow-left"></i> Back to Branches
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ $branch->name }}</h2>
                <p class="text-sm text-gray-500 mt-1">{{ $branch->location ?? 'No location set' }}</p>
            </div>
            <span class="px-4 py-2 bg-amber-50 text-amber-700 rounded-xl text-sm font-semibold">
                Code: {{ $branch->code ? strtoupper($branch->code) : $branch->getCodeOrDerived() }}
            </span>
        </div>
    </div>
</div>

<div class="flex items-center justify-between mb-5">
    <p class="text-xs text-gray-500">{{ $admins->total() }} admin{{ $admins->total() !== 1 ? 's' : '' }}</p>
    <a href="{{ route('admin.branches.admins.create', $branch) }}"
       class="flex items-center gap-2 px-4 py-2.5 text-white rounded-xl text-sm font-semibold shadow-sm"
       style="background:#D4A017">
        <i class="fas fa-plus text-xs"></i> Add Admin
    </a>
</div>

<div class="grid grid-cols-1 gap-4">
    @forelse($admins as $admin)
    <div class="card p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold"
                         style="background:linear-gradient(135deg,#D4A017,#b8860b)">
                        {{ strtoupper(substr($admin->full_name, 0, 1)) }}
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">{{ $admin->full_name }}</h3>
                        <p class="text-xs text-gray-500">{{ $admin->email }}</p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-2">
                    <i class="fas fa-phone text-xs text-gray-400 mr-2"></i>
                    {{ $admin->phone ?? 'No phone number' }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('admin.branches.admins.edit', [$branch, $admin]) }}"
                   class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-amber-100 text-gray-500 hover:text-amber-600 flex items-center justify-center transition-colors"
                   title="Edit admin">
                    <i class="fas fa-pen text-xs"></i>
                </a>
                <form method="POST" action="{{ route('admin.branches.admins.destroy', [$branch, $admin]) }}" onsubmit="return confirm('Remove this admin?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-red-100 text-gray-500 hover:text-red-600 flex items-center justify-center transition-colors" title="Remove admin">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full card p-16 text-center">
        <i class="fas fa-user-tie text-gray-200 text-5xl mb-4 block"></i>
        <p class="text-gray-400 font-medium text-sm">No admins assigned to this branch yet.</p>
        <a href="{{ route('admin.branches.admins.create', $branch) }}"
           class="inline-flex items-center gap-2 mt-4 px-4 py-2.5 text-white rounded-xl text-sm font-semibold"
           style="background:#D4A017">
            <i class="fas fa-plus"></i> Add First Admin
        </a>
    </div>
    @endforelse
</div>

@if($admins->hasPages())
<div class="mt-5">{{ $admins->links() }}</div>
@endif
@endsection
