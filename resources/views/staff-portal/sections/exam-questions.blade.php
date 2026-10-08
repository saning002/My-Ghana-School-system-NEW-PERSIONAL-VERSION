@extends('staff-portal.layout')
@section('title','Exam Questions')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Exam Question Documents</h2>
</div>
<form class="flex flex-wrap gap-3 mb-6">
    <select name="program_id" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Programs</option>
        @foreach($programs as $p)
        <option value="{{ $p->id }}" {{ request('program_id')==$p->id?'selected':'' }}>{{ $p->name }}</option>
        @endforeach
    </select>
    <select name="status" class="px-3 py-2 rounded-xl border border-slate-200 text-sm bg-white" onchange="this.form.submit()">
        <option value="">All Statuses</option>
        <option value="pending"  {{ request('status')==='pending'?'selected':'' }}>Pending</option>
        <option value="approved" {{ request('status')==='approved'?'selected':'' }}>Approved</option>
        <option value="flagged"  {{ request('status')==='flagged'?'selected':'' }}>Flagged</option>
    </select>
</form>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Title</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Teacher</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Course</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Uploaded</th>
                <th class="px-4 py-3 text-center text-xs font-bold text-slate-500 uppercase">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($questions as $q)
            @php
                $statusColors = ['pending'=>'bg-amber-50 text-amber-700','approved'=>'bg-emerald-50 text-emerald-700','flagged'=>'bg-red-50 text-red-700'];
                $sc = $statusColors[$q->status] ?? 'bg-slate-100 text-slate-600';
            @endphp
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $q->title }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $q->lecturer->full_name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $q->course->name ?? '—' }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 py-1 rounded-lg text-xs font-bold {{ $sc }}">{{ ucfirst($q->status) }}</span>
                </td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $q->created_at->format('M d, Y') }}</td>
                <td class="px-4 py-3 text-center">
                    @if($q->file_path)
                    <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($q->file_path) }}" target="_blank"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700 hover:bg-blue-100">
                        <i class="fas fa-download mr-1"></i>Download
                    </a>
                    @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="px-4 py-12 text-center text-slate-400">No exam question documents found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $questions->links() }}</div>
@endsection
