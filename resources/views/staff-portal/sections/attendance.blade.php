@extends('staff-portal.layout')
@section('title','Attendance')
@section('subtitle','Attendance records for your branch')

@section('content')
<div class="space-y-5">

<div class="card p-4">
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[140px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">Course</label>
            <select name="course_id" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
                <option value="">All Courses</option>
                @foreach($courses as $c)<option value="{{ $c->id }}" {{ request('course_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
        <div class="flex-1 min-w-[130px]">
            <label class="block text-xs font-bold uppercase tracking-wide text-slate-600 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-400">
        </div>
        <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-4 py-2.5 shadow transition-all active:scale-95">
            <i class="fas fa-search text-xs"></i> Filter
        </button>
    </form>
</div>

{{-- Desktop table (hidden on mobile) --}}
<div class="card overflow-hidden hidden lg:block">
    <div class="px-5 py-3.5 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-extrabold text-slate-800">Attendance Records ({{ $records->total() }})</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-[11px] font-bold uppercase tracking-wider text-slate-400 border-b border-slate-100">
                <th class="px-5 py-3 text-left">Student</th><th class="px-4 py-3 text-left">Course</th><th class="px-4 py-3 text-left">Date</th><th class="px-4 py-3 text-center">Status</th>
            </tr></thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($records as $r)
                <tr class="hover:bg-slate-50">
                    <td class="px-5 py-3 font-bold text-slate-800">{{ $r->student?->user?->full_name ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600 text-xs">{{ $r->course?->name ?? 'Class Register' }}</td>
                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('M d, Y') : '—' }}</td>
                    <td class="px-4 py-3 text-center"><span class="rounded-full px-2.5 py-0.5 text-[11px] font-extrabold {{ $r->status==='present'?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">{{ ucfirst($r->status) }}</span></td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center py-10 text-sm text-slate-400">No records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records->hasPages())<div class="px-5 py-4 border-t border-slate-100">{{ $records->links() }}</div>@endif
</div>

{{-- Mobile cards (hidden on desktop) --}}
<div class="lg:hidden space-y-3">
    <div class="px-1 mb-2">
        <h3 class="text-xs font-extrabold text-slate-700 uppercase tracking-wide">Attendance ({{ $records->total() }})</h3>
    </div>
    @forelse($records as $r)
    <div class="card p-4">
        <div class="flex items-start gap-3">
            <div class="h-11 w-11 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-xs font-bold shrink-0 shadow-sm">
                {{ strtoupper(substr($r->student?->user?->full_name ?? '?',0,1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="font-bold text-slate-900 text-sm leading-tight truncate">{{ $r->student?->user?->full_name ?? 'Unknown' }}</p>
                        <p class="text-[11px] text-slate-500 mt-0.5 truncate">{{ $r->course?->name ?? 'Class Register' }}</p>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-0.5 text-[10px] font-extrabold {{ $r->status==='present'?'bg-emerald-100 text-emerald-700':'bg-red-100 text-red-700' }}">
                        {{ ucfirst($r->status) }}
                    </span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-1.5">
                    <i class="fas fa-calendar-day text-slate-400 text-[10px]"></i>
                    <p class="text-xs text-slate-600 font-medium">{{ $r->date ? \Carbon\Carbon::parse($r->date)->format('l, M d, Y') : '—' }}</p>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="card p-8 text-center">
        <i class="fas fa-calendar-xmark text-3xl text-slate-200 mb-2 block"></i>
        <p class="text-sm font-semibold text-slate-400">No records found.</p>
    </div>
    @endforelse
    @if($records->hasPages())<div class="pt-2">{{ $records->links() }}</div>@endif
</div>

</div>
@endsection
