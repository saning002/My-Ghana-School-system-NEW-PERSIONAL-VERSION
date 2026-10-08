@extends('layouts.app')
@section('title', 'Bulk Promotion & Repeat')
@section('subtitle', 'Filter by program to promote or repeat students')

@section('content')
<div class="space-y-6">
    <div class="card border border-amber-100 bg-[#fffaf0] p-6">
        <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-amber-700">Academic Management</p>
                <h2 class="text-2xl font-semibold text-slate-900 mt-2">Program-Filtered Promotion</h2>
                <p class="text-sm text-slate-600 mt-1">Select a program to review students, promote them to the next level, or flag them to repeat.</p>
            </div>
            <div>
                <a href="{{ route('admin.students.bulk-promote') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-semibold hover:bg-slate-50 transition-colors">
                    <i class="fas fa-th-list"></i> Full Selection Mode
                </a>
            </div>
        </div>
    </div>

    {{-- Program Selector Card --}}
    <div class="card p-6 border border-slate-200 bg-white">
        <form method="GET" action="{{ route('admin.students.bulk-promotion') }}" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="program_id" class="block text-sm font-medium text-slate-700 mb-1">Select Program</label>
                <select name="program_id" id="program_id" class="w-full rounded-xl border-slate-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm">
                    <option value="">-- Choose Program --</option>
                    @foreach($programs as $program)
                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                            {{ $program->sequence }}. {{ $program->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition-colors">
                    <i class="fas fa-search"></i> Load Program Students
                </button>
            </div>
        </form>
    </div>

    @if($selectedProgram)
        <div class="card p-6 border border-slate-200 bg-white" x-data="{ selectedIds: [] }">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-100">
                <div>
                    <h3 class="text-xl font-bold text-slate-900">{{ $selectedProgram->name }}</h3>
                    <p class="text-xs text-slate-500 mt-1">
                        @if($nextProgram)
                            Next Program Level: <span class="font-semibold text-amber-700">{{ $nextProgram->name }}</span>
                        @else
                            <span class="text-emerald-700 font-semibold"><i class="fas fa-flag-checkered mr-1"></i> Final Program (Promoting graduates students)</span>
                        @endif
                    </p>
                </div>
                
                @if($students->isNotEmpty())
                    <div class="flex items-center gap-3">
                        <button type="submit" form="bulkPromoteForm" :disabled="selectedIds.length === 0" class="btn-gold inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-level-up-alt"></i> Promote Selected (<span x-text="selectedIds.length">0</span>)
                        </button>
                        <button type="submit" form="bulkRepeatForm" :disabled="selectedIds.length === 0" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-redo"></i> Repeat Selected
                        </button>
                    </div>
                @endif
            </div>

            @if($students->isEmpty())
                <div class="py-12 text-center text-slate-500">
                    <i class="fas fa-user-slash text-4xl mb-3 text-slate-300"></i>
                    <p class="text-sm font-medium">No active students in this program.</p>
                </div>
            @else
                <form id="bulkPromoteForm" method="POST" action="{{ route('admin.students.bulk-promote.submit') }}">@csrf</form>
                <form id="bulkRepeatForm" method="POST" action="{{ route('admin.students.bulk-repeat') }}">@csrf</form>

                <div class="table-wrap overflow-x-auto mt-4">
                    <table class="w-full text-left text-sm text-slate-600">
                        <thead>
                            <tr class="bg-slate-50 text-xs font-semibold uppercase tracking-wider text-slate-500 border-b border-slate-100">
                                <th class="p-4 w-12 text-center">
                                    <input type="checkbox" @change="selectedIds = $el.checked ? [{{ $students->pluck('id')->implode(',') }}] : []" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                                </th>
                                <th class="p-4">Student ID</th>
                                <th class="p-4">Full Name</th>
                                <th class="p-4">Status</th>
                                <th class="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $student)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="p-4 text-center">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" x-model.number="selectedIds" form="bulkPromoteForm" class="rounded text-amber-600 focus:ring-amber-500 w-4 h-4">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" x-model.number="selectedIds" form="bulkRepeatForm" class="hidden">
                                    </td>
                                    <td class="p-4 font-mono font-medium text-slate-800">{{ $student->student_id }}</td>
                                    <td class="p-4 font-semibold text-slate-900">{{ $student->user->full_name ?? '—' }}</td>
                                    <td class="p-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold {{ $student->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' }}">
                                            {{ ucfirst($student->status) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <form method="POST" action="{{ route('admin.students.promote', $student) }}" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold transition-colors">
                                                Promote →
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
@endsection
