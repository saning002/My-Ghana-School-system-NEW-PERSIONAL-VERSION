@extends('staff-portal.layout')
@section('title','Courses')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Courses</h2>
</div>
<form class="mb-6">
    <select name="program_id" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Programs</option>
        @foreach($programs as $p)
        <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
        @endforeach
    </select>
</form>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Course</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Code</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Program</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Enrolled</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($courses as $course)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $course->name }}</td>
                <td class="px-4 py-3"><span class="px-2 py-1 rounded-lg text-xs font-bold bg-yellow-50 text-yellow-800">{{ $course->code }}</span></td>
                <td class="px-4 py-3 text-slate-600">{{ $course->program->name ?? '—' }}</td>
                <td class="px-4 py-3 text-center text-slate-600">{{ $course->enrollments_count ?? 0 }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-12 text-center text-slate-400">No courses found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $courses->links() }}</div>
@endsection
