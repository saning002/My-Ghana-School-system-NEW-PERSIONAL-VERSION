@extends('staff-portal.layout')
@section('title','Class Teachers')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Class Teacher Assignments</h2>
</div>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Teacher</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Program</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Course</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($assignments as $a)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $a->lecturer->full_name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $a->program->name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $a->course->name ?? 'All Courses' }}</td>
            </tr>
            @empty
            <tr><td colspan="3" class="px-4 py-12 text-center text-slate-400">No class teacher assignments found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
