@extends('staff-portal.layout')
@section('title','Exams & Scores')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Exams & Scores</h2>
</div>
<form class="flex flex-wrap gap-3 mb-6">
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
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Student</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Course</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Score</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Grade</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Period</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($scores as $score)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $score->student->user->full_name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $score->course->name ?? '—' }}</td>
                <td class="px-4 py-3 text-center font-bold text-slate-800">{{ $score->total_score ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold bg-blue-50 text-blue-700">{{ $score->grade ?? '—' }}</span>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $score->academicPeriod->name ?? '—' }}</td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-12 text-center text-slate-400">No exam scores found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $scores->links() }}</div>
@endsection
