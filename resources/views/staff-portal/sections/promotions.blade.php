@extends('staff-portal.layout')
@section('title','Promotions')
@section('content')
<div class="flex items-center justify-between mb-6">
    <h2 class="text-xl font-extrabold text-slate-800">Student Promotion History</h2>
</div>
<div class="card overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Student</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">From</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">To</th>
                <th class="px-4 py-3 text-left text-xs font-bold text-slate-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50">
            @forelse($history as $h)
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 font-semibold text-slate-800">{{ $h->student->user->full_name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-600">{{ $h->fromProgram->name ?? '—' }}</td>
                <td class="px-4 py-3 text-emerald-700 font-semibold">{{ $h->toProgram->name ?? '—' }}</td>
                <td class="px-4 py-3 text-slate-500 text-xs">{{ $h->created_at->format('M d, Y') }}</td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-12 text-center text-slate-400">No promotion history found.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $history->links() }}</div>
@endsection
