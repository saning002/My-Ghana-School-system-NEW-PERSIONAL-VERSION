@extends('staff-portal.layout')
@section('title','Scheme of Learning')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Scheme of Learning</h2>
</div>
<form class="flex flex-wrap gap-3 mb-6">
    <select name="program_id" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Programs</option>
        @foreach($programs as $p)
        <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="course_id" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Courses</option>
        @foreach($courses as $c)
        <option value="{{ $c->id }}" {{ request('course_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>
        @endforeach
    </select>
</form>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Week</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Topic</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Course</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Teacher</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Year</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">PDF</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($entries as $entry)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">Week {{ $entry->week_number ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-700">{{ $entry->topic ?? $entry->title ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $entry->course->name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $entry->lecturer->full_name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $entry->year ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    @if($entry->pdf_path)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($entry->pdf_path) }}" target="_blank"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 hover:bg-indigo-100">
                        <i class="fas fa-file-pdf mr-1"></i>View
                    </a>
                    @else
                    <span class="text-slate-300 text-xs">—</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No scheme of learning entries found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $entries->links() }}</div>
@endsection
