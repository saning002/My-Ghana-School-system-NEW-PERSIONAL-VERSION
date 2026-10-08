@extends('layouts.app')
@section('title', 'Branches')
@section('subtitle', 'Manage Ministry Branches')

@section('content')

<style>
    .branch-card {
        background: white;
        border-radius: 14px;
        border: 1px solid rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .branch-card:hover {
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        transform: translateY(-4px);
        border-color: rgba(212, 160, 23, 0.2);
    }
    
    .branch-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #D4A017;
    }
</style>

<div class="flex items-center justify-between mb-8">
    <div>
        <h2 class="text-2xl font-bold text-gray-900">Ministry Branches</h2>
        <p class="text-sm text-gray-500 mt-1">{{ $branches->total() }} branch{{ $branches->total() !== 1 ? 'es' : '' }} in the system</p>
    </div>
    <a href="{{ route('admin.branches.create') }}"
       class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700 shadow-sm transition-all">
        <i class="fas fa-plus"></i> Add Branch
    </a>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($branches as $branch)
    <div class="branch-card p-6">
        <div class="flex items-start justify-between mb-5">
            <div>
                <p class="text-xs uppercase tracking-wider text-gray-500 mb-2 font-semibold">{{ $branch->code ? strtoupper($branch->code) : $branch->getCodeOrDerived() }}</p>
                <h3 class="text-lg font-bold text-gray-900 leading-tight">{{ $branch->name }}</h3>
                <p class="text-sm text-gray-600 mt-1.5">
                    <i class="fas fa-map-marker-alt mr-1.5" style="color: #D4A017;"></i>
                    {{ $branch->location ?: 'No location set' }}
                </p>
            </div>
            <div class="flex gap-1.5">
                <a href="{{ route('admin.branches.edit', $branch) }}"
                   class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 flex items-center justify-center transition-colors"
                   title="Edit branch">
                    <i class="fas fa-pen text-xs"></i>
                </a>
                <form method="POST" action="{{ route('admin.branches.destroy', $branch) }}" onsubmit="return confirm('Delete this branch?');" class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-9 h-9 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 flex items-center justify-center transition-colors" title="Delete branch">
                        <i class="fas fa-trash text-xs"></i>
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-2 mb-5 pb-5 border-b border-gray-100">
            <div class="rounded-lg p-4 text-center bg-blue-50 border border-blue-200/50">
                <p class="text-xl font-bold text-blue-900">{{ $branch->students_count ?? 0 }}</p>
                <p class="text-xs text-blue-700 mt-0.5 font-semibold uppercase tracking-wider">Students</p>
            </div>
            <div class="rounded-lg p-4 text-center bg-purple-50 border border-purple-200/50">
                <p class="text-xl font-bold text-purple-900">{{ $branch->admins_count ?? 0 }}</p>
                <p class="text-xs text-purple-700 mt-0.5 font-semibold uppercase tracking-wider">Admins</p>
            </div>
        </div>

        <a href="{{ route('admin.branches.admins.index', $branch) }}"
           class="w-full flex items-center justify-center gap-2 py-3 rounded-lg text-sm font-semibold transition-all bg-yellow-50 text-yellow-700 hover:bg-yellow-100">
            <i class="fas fa-users"></i> Manage Admins
        </a>
    </div>
    @empty
    <div class="col-span-full card p-16 text-center bg-gray-50">
        <div class="w-20 h-20 rounded-full bg-gray-200 text-gray-400 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-code-branch text-2xl"></i>
        </div>
        <p class="text-gray-600 font-medium text-lg mb-2">No branches created yet</p>
        <p class="text-gray-500 text-sm mb-6">Get started by creating your first ministry branch</p>
        <a href="{{ route('admin.branches.create') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-yellow-600 text-white rounded-xl text-sm font-semibold hover:bg-yellow-700">
            <i class="fas fa-plus"></i> Create First Branch
        </a>
    </div>
    @endforelse
</div>

@if($branches->hasPages())
<div class="mt-8">{{ $branches->links('pagination::tailwind') }}</div>
@endif
@endsection