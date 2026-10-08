@extends('layouts.app')
@section('title', 'Edit Program')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-8 py-6 border-b border-gray-50">
            <h2 class="text-lg font-bold text-gray-800">{{ $program->name }}</h2>
        </div>
        <form method="POST" action="{{ route('admin.programs.update', $program) }}" class="px-8 py-6 space-y-5">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Program Name *</label>
                <input type="text" name="name" value="{{ old('name', $program->name) }}" required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Duration (Months) *</label>
                <input type="number" name="duration" value="{{ old('duration', $program->duration) }}" required min="1"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Display Order / Sequence *</label>
                <input type="number" name="sequence" value="{{ old('sequence', $program->sequence) }}" required min="0"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                <p class="text-xs text-gray-400 mt-1">Controls the order programs appear in lists. Use 1, 2, 3…</p>
            </div>
            
            <div class="p-4 bg-gray-50 rounded-xl border border-gray-200 space-y-4">
                <h3 class="text-sm font-bold text-gray-800"><i class="fas fa-clipboard-check text-primary-500 mr-2"></i>Academic Expectations (Optional)</h3>
                <p class="text-xs text-gray-500">Set specific target expectations for this class. If left empty, the global default expectations will be used.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Classworks</label>
                        <input type="number" name="expected_classworks" value="{{ old('expected_classworks', $program->expected_classworks) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="e.g. 4">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Homeworks</label>
                        <input type="number" name="expected_homeworks" value="{{ old('expected_homeworks', $program->expected_homeworks) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="e.g. 4">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Monthly Tests</label>
                        <input type="number" name="expected_tests" value="{{ old('expected_tests', $program->expected_tests) }}" min="0"
                            class="w-full px-4 py-2 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="e.g. 1">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Requirements</label>
                <textarea name="requirements" rows="3"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none">{{ old('requirements', $program->requirements) }}</textarea>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white rounded-xl text-sm font-semibold transition-colors shadow-sm">
                    <i class="fas fa-save mr-2"></i> Save Changes
                </button>
                <a href="{{ route('admin.programs.index') }}" class="px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-colors">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
